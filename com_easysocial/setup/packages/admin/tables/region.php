<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableRegion extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $type = null;
	public $name = null;
	public $code = null;
	public $parent_uid = 0;
	public $parent_type = '';
	public $state = null;
	public $params = null;
	public $ordering = null;
	public $site_id = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_regions' , 'id' , $db);
	}

	public function getChildren($options = array())
	{
		$options = array_merge(array(
			'parent_uid' => $this->uid,
			'parent_type' => $this->type,
			'state' => SOCIAL_STATE_PUBLISHED
		), $options);

		$data = ES::model('regions')->getRegions($options);

		return $data;
	}

	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if (!$state) {
			return false;
		}

		// Delete all the children as well
		foreach ($this->getChildren() as $child) {
			$child->delete();
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		if (empty($this->ordering)) {
			$this->ordering = $this->getNextOrder('type = ' . ES::db()->quote($this->type) . ' AND parent_uid = ' . ES::db()->quote($this->parent_uid));
		}

		if (empty($this->uid)) {
			$db = ES::db();
			$sql = $db->sql();
			$sql->raw("SELECT MAX(`uid`) FROM `#__social_regions` WHERE `type` = " . $db->q($this->type));
			$db->setQuery($sql);
			$result = (int) $db->loadResult();

			$this->uid = $result + 1;
		}

		parent::store($updateNulls);
	}
}
