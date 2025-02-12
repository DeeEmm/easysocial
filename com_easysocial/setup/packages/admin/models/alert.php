<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelAlert extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('alert' , $config);
	}

	public function initStates()
	{
		$published	= $this->getUserStateFromRequest('published', 'all');
		$ordering 	= $this->getUserStateFromRequest('ordering', 'published');
		$direction	= $this->getUserStateFromRequest('direction', 'DESC');

		$this->setState('published', $published);
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);

		parent::initStates();
	}

	/**
	 * Given a path to the file, install the points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install($file)
	{
		// Import platform's file library.
		jimport('joomla.filesystem.file');

		// Convert the contents to an object
		$alerts 	= ES::makeObject($file);
		$result 	= array();

		if ($alerts)
		{
			foreach ($alerts as $alert)
			{
				$table		= ES::table('Alert');
				$exists		= $table->load(array('element' => $alert->element , 'rule' => $alert->rule));

				if (!$exists)
				{
					$table->element	= $alert->element;
					$table->rule	= $alert->rule;
					$table->created	= ES::date()->toSql();

					if (!isset($alert->value))
					{
						$table->email	= true;
						$table->system	= true;
					}
					else
					{
						$table->email	= $alert->value->email;
						$table->system	= $alert->value->system;
					}
				}

				$table->app			= isset($alert->app) ? $alert->app : false;
				$table->field		= isset($alert->field) ? $alert->field : false;
				$table->group		= isset($alert->group) ? $alert->group : false;
				$table->extension	= isset($alert->extension) ? $alert->extension : false;

				$table->core	= isset($alert->core) ? $alert->core : 0;
				$table->app		= isset($alert->app) ? $alert->app : 0;
				$table->field	= isset($alert->field) ? $alert->field : 0;

				$result[] = $table->store();
			}
		}

		return $result;
	}

	/**
	 * Scans through the given path and see if there are any *.points file.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The path type. E.g: components , plugins, apps , modules
	 * @return
	 */
	public function scan($path)
	{
		jimport('joomla.filesystem.folder');

		$files 	= array();

		if ($path == 'admin' || $path == 'components')
		{
			$directory	= JPATH_ROOT . '/administrator/components';
		}

		if ($path == 'site')
		{
			$directory	= JPATH_ROOT . '/components';
		}

		if ($path == 'apps')
		{
			$directory 	= SOCIAL_APPS;
		}

		if ($path == 'fields')
		{
			$directory 	= SOCIAL_FIELDS;
		}

		if ($path == 'plugins')
		{
			$directory 	= JPATH_ROOT . '/plugins';
		}

		if ($path == 'modules')
		{
			$directory	 = JPATH_ROOT . '/modules';
		}

		$files 		= JFolder::files($directory , '.alert$' , true , true);

		return $files;
	}

	/**
	 * Retrieve a list of alert rules from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_alert');

		$published = $this->getState('published', 'all');

		if ($published !== 'all') {
			$sql->where('published', $published);
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction	 = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering , $direction);
		}

		$search = $this->getState('search');

		if ($search) {
			$words = explode(' ', $search);
			foreach ($words as $word) {
				$sql->where('(', '', '', 'AND');
				$sql->where('element', '%' . $word . '%', 'LIKE');
				$sql->where('rule', '%' . $word . '%', 'LIKE', 'OR');
				$sql->where(')');
			}
		}

		$limit = $this->getState('limit', 0);

		if ($limit > 0) {
			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart 	= $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart 	= ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$alerts 	= array();

		foreach($result as $row)
		{
			$alert 	= ES::table('Alert');
			$alert->bind($row);

			$alerts[]	= $alert;
		}

		return $alerts;
	}

	public function getRules($element)
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_alert');
		$sql->where('element', $element);
		$sql->where('published', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$alerts = array();

		foreach($result as $row)
		{
			$table = ES::table('alert');
			$table->bind($row);

			$alerts[] = $table;
		}

		return $alerts;
	}

	public function getCoreRules()
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_alert');
		$sql->where('core', 1);

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$alerts = array();

		foreach($result as $row)
		{
			$table = ES::table('alert');
			$table->bind($row);

			$alerts[] = $table;
		}

		return $alerts;
	}

	public function getElements()
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_alert');
		$sql->column('element', 'element', 'distinct');
		$sql->column('core');
		$sql->order('core', 'desc');
		$sql->order('element');

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	public function getUsers($element, $rule)
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_alert_map', 'a');
		$sql->column('a.user_id');
		$sql->column('a.email');
		$sql->column('a.system');

		$sql->leftjoin('#__social_alert', 'b');
		$sql->on('b.id', 'a.alert_id');

		$sql->where('b.element', $element);
		$sql->where('b.rule', $rule);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getCoreUserSettings($uid, $options = array())
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$config = ES::config();

		// need to exclude core features thats are disabled.
		$excludeElements = array();
		$excludeElementRules = array();

		// badges
		if (! $config->get('badges.enabled')) {
			$excludeElements[] = 'badges';
		}

		//followers
		if (! $config->get('followers.enabled')) {
			$excludeElementRules[] = 'profile.followed';
		}

		// groups
		if (! $config->get('groups.enabled')) {
			$excludeElements[] = 'groups';
		}

		//events
		if (! $config->get('events.enabled')) {
			$excludeElements[] = 'events';
		}

		//reports
		if (! $config->get('reports.enabled')) {
			$excludeElements[] = 'reports';
		}

		//photos / albums
		if (! $config->get('photos.enabled')) {
			$excludeElements[] = 'photos';
			$excludeElements[] = 'albums';
		}

		// notifications.broadcast.popup
		if (! $config->get('notifications.broadcast.popup')) {
			$excludeElements[] = 'broadcast';
		}

		// video.enabled
		if (! $config->get('video.enabled')) {
			$excludeElements[] = 'videos';
		}

		$sql->select('#__social_alert', 'a')
			->column('a.id')
			->column('a.element')
			->column('a.rule')
			->column('a.core')
			->column('a.extension')
			->column('a.created')
			->column('a.app')
			->column('a.group')
			->column('a.field')
			->column('a.email')
			->column('a.system')
			->column('b.email', 'user_email')
			->column('b.system', 'user_system')
			->column('a.email_published')
			->column('a.system_published')
			->leftjoin('#__social_alert_map', 'b')
			->on('a.id', 'b.alert_id')
			->on('b.user_id', $uid)
			->where('a.app', 0)
			->where('a.field', 0)
			->where('a.published', 1);

		if ($excludeElements) {
			$sql->where('a.element', $excludeElements, 'NOT IN');
		}

		if ($excludeElementRules) {
			foreach($excludeElementRules as $exER) {
				$segments = explode('.', $exER);
				$sql->where('(');
				$sql->where('a.element', $segments[0], '!=');
				$sql->where('a.rule', $segments[1], '!=');
				$sql->where(')');
			}
		}

		$sql->order('a.element');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		foreach($result as &$row)
		{
			$this->mergeUserSettings($row);
		}

		return $result;
	}

	public function getAppsUserSettings($uid, $options = array())
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_alert', 'a')
			->column('a.id')
			->column('a.element')
			->column('a.rule')
			->column('a.core')
			->column('a.extension')
			->column('a.created')
			->column('a.app')
			->column('a.field')
			->column('a.group')
			->column('a.email')
			->column('a.system')
			->column('b.email', 'user_email')
			->column('b.system', 'user_system')
			->column('a.email_published')
			->column('a.system_published')
			->leftjoin('#__social_alert_map', 'b')
			->on('a.id', 'b.alert_id')
			->on('b.user_id', $uid)
			->leftjoin('#__social_apps', 'c')
			->on('c.element', 'a.element')
			->on('c.element', 'a.element')
			->leftjoin('#__social_apps_map', 'd')
			->on('d.app_id', 'c.id')
			->where('a.published', 1)
			->where('c.type', SOCIAL_APPS_TYPE_APPS)
			->where('c.group', SOCIAL_APPS_GROUP_USER)
			->where('d.uid', $uid)
			->where('a.app', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		foreach($result as &$row)
		{
			$this->mergeUserSettings($row);
		}

		return $result;
	}

	public function getFieldUserSettings($uid, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_alert', 'a')
			->column('a.id')
			->column('a.element')
			->column('a.rule')
			->column('a.core')
			->column('a.extension')
			->column('a.created')
			->column('a.group')
			->column('a.app')
			->column('a.field')
			->column('a.email')
			->column('a.system')
			->column('b.email', 'user_email')
			->column('b.system', 'user_system')
			->column('a.email_published')
			->column('a.system_published')
			->leftjoin('#__social_alert_map', 'b')
			->on('a.id', 'b.alert_id')
			->on('b.user_id', $uid)
			->leftjoin('#__social_apps', 'c')
			->on('a.element', 'c.element')
			->leftjoin('#__social_profiles_maps', 'd')
			->on('d.user_id', $uid)
			->leftjoin('#__social_workflows_maps', 'wm')
			->on('wm.uid', 'd.profile_id')
			->on('wm.type', 'user')
			->leftjoin('#__social_fields_steps', 'e')
			->on('e.workflow_id', 'wm.workflow_id')
			->on('e.type', SOCIAL_TYPE_PROFILES)
			->where('a.published', 1)
			->where('c.type', SOCIAL_APPS_TYPE_FIELDS)
			->where('c.group', SOCIAL_APPS_GROUP_USER)
			->where('a.field', SOCIAL_STATE_PUBLISHED)
			->group('a.element')
			->group('a.rule');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		foreach($result as &$row)
		{
			$this->mergeUserSettings($row);
		}

		return $result;
	}

	private function mergeUserSettings(&$row)
	{
		if (!is_null($row->user_email) && $row->email >= 0)
		{
			$row->email = $row->user_email;
		}

		unset($row->user_email);

		if (!is_null($row->user_system) && $row->system >= 0)
		{
			$row->system = $row->user_system;
		}

		unset($row->user_system);

		return $row;
	}

	public function getNotificationSetting($userId, $element = '')
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_alert', 'a');
		$sql->column('a.id');
		$sql->column('a.element');
		$sql->column('a.rule');
		$sql->column('a.core');
		$sql->column('a.extension');
		$sql->column('a.app');
		$sql->column('b.email');
		$sql->column('b.system');
		$sql->column('b.user_id');
		$sql->leftjoin('#__social_alert_map', 'b');
		$sql->on('a.id', 'b.alert_id');
		$sql->where('b.user_id', $userId);

		if (!empty($element))
		{
			$sql->where('a.element', $element);
		}

		$sql->order('a.core', 'desc');
		$sql->order('a.element');

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$alerts = array();
		$groups = array();
		if (count($result) > 0)
		{
			foreach($result as $item)
			{
				$title = array();
				$title[] = $item->app > 0 ? 'APP_NOTIFICATION' : 'COM_EASYSOCIAL_PROFILE_NOTIFICATION';
				$title[] = 'SETTINGS';
				$title[] = $item->element;
				$title[] = str_replace('.', '_', $item->rule);

				$item->title = JText::_(strtoupper(implode('_', $title)));

				if (empty($alerts[$item->id]))
				{
					$alert = ES::table('alert');
					$alert->load($item->id);
					$alerts[$item->id] = $alert;
				}

				$app = $alerts[$item->id]->getApp();
				$title = $app ? $app->title : ucfirst($item->element);

				$groups[$item->element]['alert'] = $alerts[$item->id];
				$groups[$item->element]['title'] = $title;
				$groups[$item->element]['rules'][] = $item;
			}
		}

		return $groups;
	}

	/**
	 * Method used to reset all the user's saved setting for a particular alert rules
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function resetUserSettings($ruleIds, $type, $value)
	{
		$db = ES::db();

		$ids = ES::makeArray($ruleIds);

		$query = "update `#__social_alert_map` set " . $db->nameQuote($type) . " = " . $db->Quote($value);
		$query .= " where `alert_id` IN (" . implode(',', $ids) . ")";

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
