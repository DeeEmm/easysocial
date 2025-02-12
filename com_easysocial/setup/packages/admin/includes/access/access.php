<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialAccess
{
	/**
	 * The unique id that is associated with the access rules.
	 * @var	int
	 */
	private $uid = null;

	/**
	 * The unique type that is associated with the access rules.
	 * @var	string
	 */
	private $type = null;

	/**
	 * The Registry that stores the user access.
	 * @var Array
	 */
	public $access = null;

	/**
	 * Cache the default values so that it only load once.
	 * @var string
	 */
	public $default = null;

	public function __construct($id = null, $type = SOCIAL_TYPE_USER)
	{
		$this->loadAccess($id, $type);
	}

	private function loadAccess($id = null, $type = SOCIAL_TYPE_USER)
	{
		// This is to prevent unnecessary multiple loading per user id
		static $loadedAccess = array();

		$uid = null;
		$utype = SOCIAL_TYPE_USER;

		// Perform data standardization

		// If type is profile, then we just directly use it as profile id
		if ($type === SOCIAL_TYPE_PROFILES) {
			$uid = $id;
		}

		// If type is user then we deduce the profile id from the user
		if ($type === SOCIAL_TYPE_USER) {
			// Get the user object
			$my = ES::user($id);
			$uid = $my->profile_id;
			$utype = $type;

			$type	= SOCIAL_TYPE_PROFILES;
		}

		// clusters is the profiles equivalent
		// If type is groups category, then use the id directly

		// For some reason clustercategory table getAccess method pass in clusters type
		// so we need to manually get that cluster category data then return a proper clusters type value
		if ($type === SOCIAL_TYPE_CLUSTERS) {
			$uid = $id;

			$clusterCat = ES::table('ClusterCategory');
			$clusterCat->load((int) $id);

			$utype = $clusterCat->type;
		}

		$clusterType = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);

		if (in_array($type, $clusterType)) {
			$uid = $id;
			$utype = $type;

			$type = SOCIAL_TYPE_CLUSTERS;
		}

		$this->getDefaultValues($utype);

		if (empty($loadedAccess[$type][$uid])) {

			// Load up the access based on the profile
			$model = ES::model('Access');
			$storedAccess = $model->getParams($uid, $type);

			// Merge all the group registries first.
			$registry = ES::registry($storedAccess);

			$loadedAccess[$type][$uid]	= $registry;
		}

		$this->access = $loadedAccess[$type][$uid];

		return $this;
	}

	/**
	 * Get default values from the files first.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDefaultValues($type)
	{
		static $defaults = array();

		if (empty($defaults[$type])) {

			$model = ES::model('accessrules');

			$options = array('group' => $type, 'state' => SOCIAL_STATE_PUBLISHED);
			$rules = $model->getAllRules($options);

			$registry = ES::registry();

			if (!empty($rules)) {
				foreach ($rules as $rule) {

					if (!isset($rule->default)) {
						$rule->default = true;
					}

					$registry->set($rule->name, $rule->default);
				}
			}

			$defaults[$type] = $registry;
		}

		$this->default = $defaults[$type];

		return $this->default;
	}

	/**
	 * Factory method to create a new access object.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	The unique id that is tied to the access.
	 * @param	int		The unique type that is tied to the access.
	 */
	public static function factory( $id = null, $type = SOCIAL_TYPE_USER )
	{
		$obj 	= new self( $id , $type );

		return $obj;
	}

	/**
	 * Detect if the user is allowed to perform specific actions.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function get($rule)
	{
		if (!$this->access) {
			return false;
		}

		// Get the default rule
		$default = $this->default->get($rule);

		// If rule is not found in access, then fallback to default
		// If rule is not found in default, then return null
		$value = $this->access->get($rule, $default);

		// Specific checking for file upload rule
		$fileUploadRule = array('audios.maxsize', 'files.maxsize', 'photos.uploader.maxsize', 'videos.maxsize');

		// Add trigger for listener to override the size limit
		if (in_array($rule, $fileUploadRule)) {
			ESDispatcher::trigger('onEasySocialGetUploadSizeLimit', array(ES::user()->id, &$value));
		}

		return $value;
	}

	/**
	 * Detect if the user is allowed to perform specific actions.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function allowed($rule, $default = true)
	{
		if (!$this->access) {
			return false;
		}

		// If rule is not found in access, then fallback to default
		// If rule is not found in default, then fallback to the provided default value
		return $this->access->get($rule , $this->default->get($rule, $default));
	}

	/**
	 * Determines if a rule item exceeded the usage.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function exceeded($rule , $usage , $default = true)
	{
		// If rule is not found in access, then fallback to default
		// If rule is not found in default, then fallback to the provided default value
		$limit = (int) $this->access->get($rule, $this->default->get($rule, $default));

		// If limit is 0, we know it should be unlimited
		if ($limit == 0) {
			return false;
		}

		$exceeded	= $usage >= $limit;

		return $exceeded;
	}


	public function intervalExceeded($rule , $userId)
	{
		// If rule is not found in access, then fallback to default
		// If rule is not found in default, then fallback to the provided default value
		$limit = $this->access->get( $rule , $this->default->get($rule) );

		$value = 0;
		$interval = 0;

		if (is_object($limit)) {
			$value = $limit->value;
			$interval = $limit->interval;
		} else {
			// backward compatibility
			$value = $limit;
		}

		// If limit is 0, we know it should be unlimited
		if ($value == 0) {
			return false;
		}

		// we need to get usage here.
		$model = ES::model('AccessLogs');
		$usage = $model->getUsage($rule, $userId, $interval);

		$exceeded	= $usage >= $value;

		return $exceeded;
	}

	public function log($rule, $userId, $uid, $utype) {

		$log = ES::table('AccessLogs');

		$log->rule = $rule;
		$log->user_id = $userId;
		$log->uid = $uid;
		$log->utype = $utype;
		$log->created = ES::date()->toMySQL();

		$state = $log->store();
		return $state;
	}

	public function removeLog($rule, $userId, $uid, $utype) {

		$log = ES::table('AccessLogs');
		$state = $log->load(array('rule'=>$rule, 'user_id' => $userId, 'uid' => $uid, 'utype' => $utype));

		if ($state) {
			$state = $log->delete();
		}

		return $state;
	}

	public function switchLogAuthor($rule, $userId, $uid, $utype, $newUserId) {

		$log = ES::table('AccessLogs');
		$state = $log->load(array('rule'=>$rule, 'user_id' => $userId, 'uid' => $uid, 'utype' => $utype));

		if ($state) {
			$log->user_id = $newUserId;
			$state = $log->store();
		}

		return $state;
	}



}
