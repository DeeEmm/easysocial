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

ES::import('admin:/includes/model');

class EasySocialModelRegions extends EasySocialModel
{
	private $_tbl = '#__social_regions';

	// Currently we don't include city first
	private $types = array(SOCIAL_REGION_TYPE_COUNTRY, SOCIAL_REGION_TYPE_STATE);

	public function __construct($config = array())
	{
		parent::__construct('regions' , $config);
	}

	public function initStates()
	{
		// Direction, search, limit, limitstart, ordering is handled by parent::initStates();
		parent::initStates();

		$currentLayout = JFactory::getApplication()->input->getString('layout');

		if (empty($currentLayout)) {
			$currentLayout = 'country';
		}

		$previousLayout = $this->getUserState('layout');

		if ($currentLayout !== $previousLayout) {
			$this->setUserState('search', '');
		}

		$this->setUserState('layout', $currentLayout);

		// Init other parameters
		$state = $this->getUserStateFromRequest('state', 'all');

		$this->setState('state', $state);
	}

	public function getItems($options = [])
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select($this->_tbl);

		if (isset($options['type'])) {
			$sql->where('type', $options['type']);
		}

		if (isset($options['parent_uid'])) {
			$sql->where('parent_uid', $options['parent_uid']);
		}

		if (isset($options['parent_type'])) {
			$sql->where('parent_type', $options['parent_type']);
		}

		$state = $this->getState('state');

		if ($state !== 'all') {
			$sql->where('state', $state);
		}

		$search = $this->getState('search');

		if (!empty($search)) {
			$sql->where('name', '%' . $search . '%', 'LIKE');
		}

		$sql->order($this->getState('ordering'), $this->getState('direction'));

		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		return $this->bindTable('Region', $result);
	}

	public function initDB($key = null, $hard = false)
	{
		foreach ($this->types as $type) {
			if (empty($key) || $key === $type) {
				if ($hard) {
					$this->clearDB($type);
				}

				$func = 'init' . strtoupper($type);
				$this->$func();
			}
		}
	}

	public function clearDB($key = null)
	{
		$db = ES::db();

		$sql = $db->sql();

		if (empty($key)) {
			$sql->raw("TRUNCATE TABLE `$this->_tbl`");
		} else {
			$sql->delete($this->_tbl);
			$sql->where('type', $key);
		}

		$db->setQuery($sql);
		$db->query();
	}

	public function initCountry()
	{
		$file = SOCIAL_ADMIN_DEFAULTS . '/regions/country.json';

		$data = ES::makeObject($file);

		$db = ES::db();

		$values = array();

		$counter = 1;

		foreach ($data as $row) {
			$uid = $db->quote($row->uid);
			$name = $db->quote($row->name);
			$code = $db->quote($row->code);
			$type = $db->quote(SOCIAL_REGION_TYPE_COUNTRY);

			$values[] = "($uid, $name, $code, $type, $counter)";

			if ($counter % 100 === 0) {
				$string = implode(', ', $values);
				$table = $this->_tbl;

				$query = "INSERT INTO `$table` (`uid`, `name`, `code`, `type`, `ordering`) VALUES $string";

				$sql = $db->sql();

				$sql->raw($query);

				$db->setQuery($sql);
				$db->query();

				$values = array();
			}

			$counter++;
		}

		// Insert remaining ones
		$string = implode(', ', $values);
		$table = $this->_tbl;

		$query = "INSERT INTO `$table` (`uid`, `name`, `code`, `type`, `ordering`) VALUES $string";

		$sql = $db->sql();

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

	public function initState()
	{
		$file = SOCIAL_ADMIN_DEFAULTS . '/regions/state.json';

		$data = ES::makeObject($file);

		$db = ES::db();

		$values = array();

		$counter = 1;

		$ordering = 1;

		$prevpuid = 0;

		foreach ($data as $row) {

			$parentUid = (int) $row->parent_uid;

			if ($prevpuid !== $parentUid) {
				$prevpuid = $parentUid;
				$ordering = 1;
			} else {
				$ordering++;
			}

			$uid = $db->quote($row->uid);
			$name = $db->quote($row->name);
			$code = $db->quote($row->code);
			$type = $db->quote(SOCIAL_REGION_TYPE_STATE);
			$puid = $db->quote($row->parent_uid);
			$ptype = $db->quote(SOCIAL_REGION_TYPE_COUNTRY);

			$values[] = "($uid, $name, $code, $type, $puid, $ptype, $ordering)";

			if ($counter % 100 === 0) {
				$string = implode(', ', $values);
				$table = $this->_tbl;

				$query = "INSERT INTO `$table` (`uid`, `name`, `code`, `type`, `parent_uid`, `parent_type`, `ordering`) VALUES $string";

				$sql = $db->sql();

				$sql->raw($query);

				$db->setQuery($sql);
				$db->query();

				$values = array();
			}

			$counter++;
		}

		// Insert the remaining ones
		$string = implode(', ', $values);
		$table = $this->_tbl;

		$query = "INSERT INTO `$table` (`uid`, `name`, `code`, `type`, `parent_uid`, `parent_type`, `ordering`) VALUES $string";

		$sql = $db->sql();

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	public function initCity()
	{
		$file = SOCIAL_ADMIN_DEFAULTS . '/regions/cities.csv';

		$data = ES::parseCSV($file);

		$db = ES::db();

		$json = ES::json();

		// $columns = array("CityId","CountryID","RegionID","City","Latitude","Longitude","TimeZone","DmaId","Code");

		$values = array();

		$counter = 1;

		foreach ($data as $row) {
			if (!empty($row['Code'])) {
				$uid = $db->quote($row['CityId']);
				$type = $db->quote(SOCIAL_REGION_TYPE_CITY);
				$name = $db->quote($row['City']);
				$code = $db->quote($row['Code']);
				$puid = $row['RegionID'];
				$ptype = SOCIAL_REGION_TYPE_STATE;
				$state = $db->quote(SOCIAL_STATE_PUBLISHED);
				$params = $db->quote($json->encode($row));

				$values[] = "($uid, $type, $name, $code, $puid, $ptype, $state, $params)";
			}

			if ($counter % 100 === 0) {
				$string = implode(', ', $values);
				$table = $this->_tbl;

				$query = "INSERT INTO `$table` (`uid`, `type`, `name`, `code`, `parent_uid`, `parent_type`, `state`, `params`) VALUES $string";

				$sql = $db->sql();

				$sql->raw($query);

				$db->setQuery($sql);
				$db->query();

				$values = array();
			}

			$counter++;
		}

		// Insert the remaining ones
		$string = implode(', ', $values);
		$table = $this->_tbl;

		$query = "INSERT INTO `$table` (`uid`, `type`, `name`, `code`, `parent_uid`, `parent_type`, `state`, `params`) VALUES $string";

		$sql = $db->sql();

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	public function getRegions($options = array())
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select($this->_tbl);

		if (isset($options['type'])) {
			$sql->where('type', $options['type']);
		}

		if (isset($options['state'])) {
			$sql->where('state', $options['state']);
		}

		if (isset($options['parent_uid'])) {
			$sql->where('parent_uid', $options['parent_uid']);
		}

		if (isset($options['parent_type'])) {
			$sql->where('parent_type', $options['parent_type']);
		}

		// Forcefully order by type first
		$sql->order('type');

		if (isset($options['ordering'])) {
			$sql->order($options['ordering'], isset($options['direction']) ? $options['direction'] : 'asc');
		} else {
			// If no ordering then we always obey by ordering
			$sql->order('ordering');
		}

		$db->setQuery($sql);

		$codeAsIndex = isset($options['codeAsIndex']) ? $options['codeAsIndex'] : false;

		$data = $db->loadObjectList();
		$regions = $this->bindTable('Region', $data);

		// return an array with country code as array index.
		if ($codeAsIndex && $regions) {
			$tmp = [];
			foreach ($regions as $row) {
				$tmp[$row->code] = $row;
			}
			$regions = $tmp;
		}

		return $regions;
	}

	public function export($key = null)
	{
		$db = ES::db();

		$sql = $db->sql();

		foreach ($this->types as $type) {
			if (empty($key) || $key === $type) {
				$sql = $db->sql();

				$sql->select($this->_tbl);
				$sql->where('type', $type);
				$sql->order('name');
				$sql->order('uid');

				$db->setQuery($sql);

				$result = $db->loadObjectList();

				$data = array();

				foreach ($result as $row) {
					$d = array(
						'uid' => $row->uid,
						'name' => $row->name,
						'code' => $row->code,
						'ordering' => $row->ordering
					);

					if (!empty($row->parent_uid)) {
						$d['parent_uid'] = $row->parent_uid;
					}

					$data[] = $d;
				}

				$string = json_encode($data);

				$path = SOCIAL_TMP . '/' . $type . '.json';

				JFile::write($path, $string);
			}
		}
	}
}
