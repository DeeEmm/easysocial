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

ES::import('admin:/tables/table');

class SocialTableLikes extends SocialTable
{
	public $id = null;
	public $reaction = null;
	public $type = null;
	public $uid = null;
	public $created_by = null;
	public $created = null;
	public $stream_id = null;
	public $uri = null;
	public $react_as = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__social_likes', 'id', $db);
	}

	/**
	 * Override parent's store implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = ES::registry($this->params);
		}

		$this->params = $this->params->toString();

		$isNew = false;

		if (empty($this->id)) {
			$isNew = true;
		}

		// Get dispatcher library
		$dispatcher = ES::dispatcher();
		$group = SOCIAL_APPS_GROUP_USER;

		$like = new stdClass();

		foreach (get_object_vars($this) as $key => $value) {
			$like->{$key} = $value;
		}

		$like->element = $this->type;
		$like->group = $group;
		$like->verb = '';

		if (strpos($this->type, '.') !== false) {
			$tmp = explode('.', $this->type);
			$group = $tmp['1'];

			$like->element = $tmp[0];
			$like->group = $tmp[1];
			$like->verb = isset($tmp[2]) ? $tmp[2] : '';
		}

		$args = array(&$like, &$isNew);

		// @trigger: onBeforeLikeSave
		$dispatcher->trigger($group, 'onBeforeLikeSave', $args);

		$state = parent::store();

		if (!$state) {
			return $state;
		}

		$like->id = $this->id;

		// @trigger: onAfterLikeSave
		$dispatcher->trigger($group, 'onAfterLikeSave', $args);

		return $state;
	}

	/**
	 * Overrides parent's delete implementation
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete();

		if ($state) {
			$group = SOCIAL_APPS_GROUP_USER;

			if (strpos($this->type, '.') !== false) {
				$tmp = explode('.', $this->type);
				$group = $tmp['1'];
			}

			$dispatcher = ES::dispatcher();
			$args = array(&$this);

			$dispatcher->trigger($group, 'onAfterLikeDelete', $args);
		}

		return $state;
	}

	/**
	 * Retrieve params from the table
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getParams()
	{
		$params = ES::registry($this->params);

		return $params;
	}

	public function getReaction()
	{
		static $reaction = array();

		if (!isset($reaction[$this->reaction])) {
			$lib = ES::reaction($this->reaction);
			$reaction[$this->reaction] = $lib;
		}

		return $reaction[$this->reaction];
	}
}
