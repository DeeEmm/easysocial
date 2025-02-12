<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelMaintenance extends EasySocialModel
{
	private static $scripts = array();
	private static $versions = array();

	function __construct()
	{
		parent::__construct('maintenance');
	}

	public function initStates()
	{
		$ordering = $this->getUserStateFromRequest('ordering', 'version');
		$direction = $this->getUserStateFromRequest('direction', 'asc');
		$version = $this->getUserStateFromRequest('version', 'all');
		$search = $this->getUserStateFromRequest('search', '');

		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
		$this->setState('version', $version);
		$this->setState('search', $search);

		parent::initStates();
	}

	private function initScripts()
	{
		if (empty(self::$scripts)) {
			$lib = ES::maintenance();

			$scripts = $lib->getScriptFiles('all');

			foreach ($scripts as $script) {
				$item = new EasySocialModelMaintenanceScriptItem;

				if ($item->load($script)) {
					self::$scripts[$item->key] = $item;

					self::$versions[] = $item->version;
				}
			}

			self::$versions = array_unique(self::$versions);
		}

		return true;
	}

	/**
	 * Cache all scripts into self::$scripts first.
	 *
	 * @since  1.2
	 * @access public
	 */
	private function getScripts()
	{
		$this->initScripts();

		return self::$scripts;
	}

	public function getVersions()
	{
		$this->initScripts();

		return self::$versions;
	}

	public function getItems()
	{
		$scripts = $this->getScripts();

		$total = 0;

		$results = array();

		// Allowed filter
		// version
		// search
		foreach ($scripts as $script) {
			$version = $this->getState('version');

			if (!empty($version) && $version !== 'all' && $script->version != $version) {
				continue;
			}

			$search = $this->getState('search');

			if (!empty($search) && ESJString::strpos($script->title, $search === false)) {
				continue;
			}

			$results[] = $script;

			$total++;
		}

		$this->total = $total;
		$this->setState('total', $total);

		// Ordering
		usort($results, array($this, 'sortItems'));

		// var_dump($this->getState('ordering'), $this->getState('direction')); exit;

		$limit  = (int) $this->getState('limit');

		if ($limit > 0)
		{
			$this->setState('limit', $limit);

			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = (int) ($limit > 0 ? (floor($limitstart / $limit) * $limit ) : 0 );

			$this->setState('limitstart', $limitstart);

			$results = array_slice($results, $limitstart, $limit);
		}

		return $results;
	}

	private function sortItems($a, $b)
	{
		$ordering = $this->getState('ordering');
		$direction = $this->getState('direction');

		if (empty($ordering) || !isset($a->$ordering) || !isset($b->$ordering) || $a->$ordering == $b->$ordering) {
			return 0;
		}

		$marker = $direction === 'desc' ? -1 : 1;

		$result = $a->$ordering < $b->$ordering ? -$marker : $marker;

		return $result;
	}

	public function getItemByKeys($keys)
	{
		$scripts = $this->getScripts();

		$results = array();

		foreach ($keys as $key) {
			if (isset($scripts[$key])) {
				$results[] = $scripts[$key];
			}
		}

		return $results;
	}

	public function getItemByKey($key)
	{
		// If we are getting by a single key, then we see if cache is loaded
		// If cache is not loaded, we don't initiate it because it is unnecessary for cases of ajax loading 1 single script
		if (!empty(self::$scripts)) {
			$scripts = $this->getItemByKeys(array($key));

			if (count($scripts) < 1) {
				return false;
			}

			return $scripts[0];
		}

		$file = SOCIAL_ADMIN_UPDATES . '/' . $key;

		if (!JFile::exists($file)) {
			return false;
		}

		$script = new EasySocialModelMaintenanceScriptItem($file);

		return $script;
	}


	/**
	 * Get total number of items need to be fixed for the privacy access
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getMediaPrivacyCounts($merge = true)
	{
		$db = ES::db();

		// photos
		$query = "select count(1) as `cnt`, 'photos' as `type`";
		$query .= " FROM `#__social_photos` where `type` = 'user' and `chk_access` = 0";

		// albums
		$query .= " UNION ALL ";
		$query .= "select count(1) as `cnt`, 'albums' as `type`";
		$query .= " FROM `#__social_albums` where `type` = 'user' and `chk_access` = 0";
		$query .= " and `core` IN (0, 3)"; // we only process user albums

		//audios
		$query .= " UNION ALL ";
		$query .= "select count(1) as `cnt`, 'audios' as `type`";
		$query .= " FROM `#__social_audios` where `type` = 'user' and `chk_access` = 0";

		//videos
		$query .= " UNION ALL ";
		$query .= "select count(1) as `cnt`, 'videos' as `type`";
		$query .= " FROM `#__social_videos` where `type` = 'user' and `chk_access` = 0";

		$db->setQuery($query);
		$results = $db->loadObjectList();
		if (!$results) {
			return 0;
		}

		$data = array();
		foreach ($results as $item) {
			$data[$item->type] = $item->cnt;
		}

		if ($merge) {
			return array_sum($data);
		}

		return $data;
	}


	/**
	 * Process privacy access migration.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function processPrivacyMigration($type)
	{
		$db = ES::db();

		$privacies = $this->getMediaPrivacyTypes();

		if (isset($privacies[$type])) {
			$data = $privacies[$type];

			$rules = explode('.', $data->privacyRule);
			$ruleElement = $rules[0];
			$ruleAction = $rules[1];
			$tableName = $data->tablename;

			// get the privacy id based on the rule
			$pquery = "select `id`, `value` from `#__social_privacy`";
			$pquery .= " where `type` = " . $db->Quote($ruleElement);
			$pquery .= " and `rule` = " . $db->Quote($ruleAction);

			$db->setQuery($pquery);
			$defaultPrivacy = $db->loadObject();

			if (!isset($defaultPrivacy->id)) {
				return false;
			}

			$privacyId = $defaultPrivacy->id;
			$defaultValue = $defaultPrivacy->value;

			$subQueryAccess = "IFNULL((SELECT IFNULL(b.`value`, pru.`value`) from `#__social_privacy_map` as pru";
			$subQueryAccess .= "	where pru.`privacy_id` = " . $db->Quote($privacyId) . " and pru.`utype` = 'user' and pru.`uid` = a.`user_id`";
			$subQueryAccess .= " UNION ALL ";
			$subQueryAccess .= "SELECT IFNULL(b.`value`, prp.`value`) from `#__social_privacy_map` as prp ";
			$subQueryAccess .= "	inner join `#__social_profiles_maps` as pm on prp.`uid` = pm.`profile_id` and prp.`utype` = 'profiles'";
			$subQueryAccess .= "	where prp.`privacy_id` = " . $db->Quote($privacyId) . " and pm.`user_id` = a.`user_id`";
			$subQueryAccess .= " limit 1), b.`value`)";

			$subQueryCustom = "SELECT concat(',', group_concat(c.`user_id` SEPARATOR ','), ',') from `#__social_privacy_customize` as c where c.`uid` = b.`id` and c.`utype` = 'item' group by c.`uid`";

			$query = "update " . $db->nameQuote($tableName) . " as a";
			$query .= " 	left join `#__social_privacy_items` as b on a.`id` = b.`uid` and b.`type` = " . $db->Quote($type);
			$query .= " SET a.`access` = IFNULL(" . $subQueryAccess . ", " . $db->Quote($defaultValue). ")";
			$query .= ", a.`field_access` = b.`field_access`";
			$query .= ", a.`custom_access` = (" . $subQueryCustom . ")";
			$query .= ", a.`chk_access` = 1";
			$query .= " where a.`type` = " . $db->Quote('user');
			$query .= " and a.`chk_access` = 0";

			if ($type == 'albums') {
				$query .= " and a.`core` IN (0, 3)";
			}

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

	/**
	 * Method to generate require privacy types
	 * used for migration
	 *
	 * @since	3.1
	 * @access	private
	 */
	private function getMediaPrivacyTypes()
	{
		$data = array();

		// lets build the data 1st
		$obj = new stdClass();
		$obj->tablename = '#__social_photos';
		$obj->privacyRule = 'photos.view';
		$data['photos'] = $obj;

		$obj = new stdClass();
		$obj->tablename = '#__social_albums';
		$obj->privacyRule = 'albums.view';
		$data['albums'] = $obj;

		$obj = new stdClass();
		$obj->tablename = '#__social_audios';
		$obj->privacyRule = 'audios.view';
		$data['audios'] = $obj;

		$obj = new stdClass();
		$obj->tablename = '#__social_videos';
		$obj->privacyRule = 'videos.view';
		$data['videos'] = $obj;

		return $data;
	}

}

class EasySocialModelMaintenanceScriptItem
{
	public $file;

	public $key;
	public $filename;
	public $version;
	public $classname;
	public $title;
	public $description;

	CONST PREFIX = 'SocialMaintenanceScript';
	CONST BASE = SOCIAL_ADMIN_UPDATES;

	public function __construct($file = null)
	{
		if (!empty($file)) {
			$this->load($file);
		}
	}

	public function load($file)
	{
		$file = ES::normalizeSeparator($file);
		$this->file = $file;

		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);

		$this->key = str_ireplace($this->normalizeSeparator(self::BASE) . '/', '', $file);

		list($this->version, $this->filename) = explode('/', $this->key);

		$classname = self::PREFIX . str_ireplace('.php', '', $this->filename);

		if (!class_exists($classname)) {
			return false;
		}

		$this->classname = $classname;

		// PHP 5.2 compatibility
		$vars = get_class_vars($classname);

		$this->title = $vars['title'];

		$this->description = $vars['description'];

		return true;
	}

	/**
	 * Normalize directory separator
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function normalizeSeparator($path)
	{
		$path = str_ireplace(array( '\\' ,'/' ) , '/' , $path);

		return $path;
	}

	public function toString()
	{
		return $this->file;
	}

	public function __toString()
	{
		return $this->toString();
	}
}
