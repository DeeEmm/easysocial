<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');
ES::import('admin:/includes/indexer/indexer');

class SocialTableUsers extends SocialTable implements ISocialIndexerTable
{
	public $user_id = null;
	public $alias = '';
	public $state = null;
	public $params = null;
	public $connections	= null;
	public $type = 'joomla';
	public $permalink = '';
	public $auth = '';
	public $completed_fields = 0;
	public $reminder_sent = 0;
	public $require_reset = 0;
	public $block_period = '';
	public $block_date = null;
	public $social_params = '';
	public $verified = false;
	public $affiliation_id = null;
	public $robots = null;
	
	public function __construct($db)
	{
		parent::__construct('#__social_users' , 'user_id' , $db);
	}

	/**
	 * Loads a record by a given user id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadByUser($id)
	{
		$db = ES::db();
		
		$query = array();
		$query[] = 'SELECT * FROM ' . $db->nameQuote($this->_tbl);
		$query[] = 'WHERE ' . $db->nameQuote('user_id') . '=' . $db->Quote($id);

		$query = implode(' ', $query);

		$db->setQuery($query);
		$data = $db->loadObject();

		if (!$data) {
			return false;
		}

		return parent::bind($data);
	}

	/**
	 * Override parent's behavior of store because there's no auto increment on the primary key.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		// Update values.
		$db = ES::db();

		$obj = new stdClass();

		$properties = get_object_vars($this);

		// we need to clear up some extra keys that might get added from
		// some third party system plugins.
		unset($properties['privacy']);

		foreach ($properties as $key => $value) {
			if (stripos($key , '_') !== 0) {
				$obj->$key = $value;

				// For Joomla 4, it does not convert boolean value into 1 / 0
				if (is_bool($value)) {
					$obj->$key = $value ? 1 : 0;
				}
			}
		}

		// Ensure that there's a record.
		$exists = $this->exists($this->user_id);

		if ($exists) {

			if (ES::isJoomlaSefEnabled()) {
				$this->updateAliasSEFCache();
			}

			$state = $db->updateObject($this->_tbl , $obj , 'user_id');

			if (!$state) {
				$this->setError($db->getError());
			}

			return $state;
		}

		if (!$obj->params) {
			$obj->params = '';
		}

		if (!$obj->connections) {
			$obj->connections = 0;
		}

		if (!$obj->affiliation_id) {
			$obj->affiliation_id = 0;
		}

		$state = $db->insertObject($this->_tbl , $obj);

		if (!$state) {
			$this->setError($db->getError());

			return $state;
		}

		return $state;
	}

	/**
	 * Determines if a particular record exists.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function exists($id)
	{
		$db = ES::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote($this->_tbl);
		$query[] = 'WHERE ' . $db->nameQuote('user_id') . '=' . $db->Quote($id);

		$query = implode(' ' , $query);

		$db->setQuery($query);

		// If the record does not exist yet, create it.
		$exists = $db->loadResult() ? true : false;

		return $exists;
	}

	/**
	 * Initializes the user's record in this table. Since new user's are not created automatically, we need
	 * to map their default values here.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function init($id)
	{
		$db = ES::db();

		$exists = $this->exists($id);
		if (!$exists) {
			// @TODO: Store any custom default values here.
			$obj = new stdClass();
			$obj->user_id = $id;

			// If user is created on the site but doesn't have a record, we should treat it as published.
			$obj->state = SOCIAL_STATE_PUBLISHED;

			$db->insertObject('#__social_users' , $obj);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function syncIndex()
	{
		// do nothing. this function is to satisfy the implementation of indexer interface. the actual indexing located at /includes/user.php
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteIndex()
	{
		$indexer = ES::get('Indexer');
		$indexer->delete($this->user_id, SOCIAL_INDEXER_TYPE_USERS);
	}

	/**
	 * Method to update the cached sef alias when there
	 * is changes on the alias column
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateAliasSEFCache()
	{
		$app = JFactory::getApplication();
		$controller = $app->input->get('controller', '', 'cmd');
		// we do not want to process this on registration. #4157
		if ($controller == 'registration') {
			return true;
		}

		$old = ES::table('Users');
		$old->load($this->user_id);

		$oldAlias = $old->permalink ? $old->permalink : $old->alias;
		$newAlias = $this->permalink ? $this->permalink : $this->alias;

		if ($oldAlias && $newAlias && $oldAlias != $newAlias) {
			ESR::updateSEFCache($this, $oldAlias, $newAlias);
		}
	}

	/**
	 * Method to delete the cached sef alias when item being removed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		$alias = $this->permalink ? $this->permalink : $this->alias;
		$alias = $this->user_id . ':' . $alias;

		$state = ESR::deleteSEFCache($this, $alias);

		return $state;
	}

}
