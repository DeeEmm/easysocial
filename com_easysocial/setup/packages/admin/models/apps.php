<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelApps extends EasySocialModel
{
	private $data = null;
	protected $pagination = null;

	protected $limitstart = null;
	protected $limit = null;

	public function __construct($config = array())
	{
		parent::__construct('apps', $config);
	}

	/**
	 * Loads the css for apps on the site
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function loadAppCss($options = array())
	{
		static $loaded = false;

		if (!$loaded) {
			$apps = $this->getApps($options);

			// We need to load the app's own css file.
			if ($apps) {
				foreach ($apps as $app) {
					$app->loadCss();
				}
			}

			$loaded = true;
		}
	}

	/**
	 * Removes app from the `#__social_apps_map` table
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The application id
	 * @return
	 */
	public function removeUserApp($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_apps_map');
		$sql->where('app_id', $id);

		$db->setQuery($sql);
		$state = $db->Query();

		return $state;
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function initStates()
	{
		$state = $this->getUserStateFromRequest('state', 'all');
		$filter = $this->getUserStateFromRequest('filter', 'all');
		$group = $this->getUserStateFromRequest('group', 'all');

		$this->setState('group', $group);
		$this->setState('filter', $filter);
		$this->setState('state', $state);

		parent::initStates();
	}

	/**
	 * Deletes existing views for specific app id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 		The application id.
	 * @return	boolean		True if success false otherwise.
	 */
	public function deleteExistingViews($appId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_apps_views');
		$sql->where('app_id', $appId);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Deletes discovered items
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function deleteDiscovered()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_apps');
		$sql->where('state', SOCIAL_APP_STATE_DISCOVERED);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Discover new applications on the site
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function discover()
	{
		// Default paths
		$folders = array(
				SOCIAL_APPS . '/user',
				SOCIAL_APPS . '/group',
				SOCIAL_APPS . '/page',
				SOCIAL_APPS . '/event',
				SOCIAL_FIELDS . '/user',
				SOCIAL_FIELDS . '/group',
				SOCIAL_FIELDS . '/event',
				SOCIAL_FIELDS . '/page',
				SOCIAL_FIELDS . '/marketplace'
			);

		$total = 0;

		// Go through each of the folders and look for any app folders.
		foreach ($folders as $folder) {

			if (!JFolder::exists($folder)) {
				continue;
			}

			$items = JFolder::folders($folder, '.', false, true);

			foreach ($items as $item) {

				// Load the installer and pass in the folder
				$installer = ES::installer();
				$installer->load($item);

				$state = $installer->discover();

				if ($state) {
					$total += 1;
				}
			}
		}

		return $total;
	}

	/**
	 * Determines if the app has been installed in the system
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The app's id.
	 * @param	int		The user's id.
	 * @return	bool	Result
	 */
	public function isAppInstalled($element, $group, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps');
		$sql->column('COUNT(1)', 'count');
		$sql->where('element', $element);
		$sql->where('group', $group);
		$sql->where('type', $type);

		$db->setQuery($sql);

		$installed = (bool) $db->loadResult();

		return $installed;
	}

	/**
	 * Determines if the app has been installed by the provided user.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The app's id.
	 * @param	int		The user's id.
	 * @return	bool	Result
	 */
	public function isInstalled($appId, $userId = null)
	{
		if (empty($userId)) {
			$userId = ES::user()->id;
		}

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps_map');
		$sql->where('app_id', $appId);
		$sql->where('uid', $userId);

		$db->setQuery($sql->getTotalSql());
		$installed = (bool) $db->loadResult();

		return $installed;
	}

	/**
	 * Retrieve a list of applications that is installed on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItemsWithState($options = [])
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps');

		// Determine if we should only fetch apps that are widgets
		$widget = ES::normalize($options, 'widget', false);

		if ($widget) {
			$sql->where('widget', SOCIAL_STATE_PUBLISHED);
		}

		// Depending on type of apps.
		$filter = $this->normalize($options, 'filter', 'all');

		if ($filter && $filter !== 'all') {
			$sql->where('type', $filter);
		}

		// Filter by group
		$group = $this->getState('group');

		if ($group && $group !== 'all') {
			$sql->where('group', $group);
		}

		// Search filter
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Depending on group of apps.
		$group = ES::normalize($options, 'group', '');

		if ($group) {
			$sql->where('group', $group);
		}

		// Discover apps
		$discover = ES::normalize($options, 'discover', '');

		if ($discover) {
			$sql->where('state', SOCIAL_APP_STATE_DISCOVERED);
		} else {
			// State Filters
			$state = $this->getState('state');

			if ($state !== '' && $state !== 'all' && $state !== 'outdated') {
				$sql->where('state', $state);
			}

			$sql->where('(');
			$sql->where('state', SOCIAL_STATE_PUBLISHED, '=', 'OR');
			$sql->where('state', SOCIAL_STATE_UNPUBLISHED, '=', 'OR');
			$sql->where(')');

			$sql->where('state', SOCIAL_APP_STATE_DISCOVERED, '!=');
		}

		// Check for ordering
		$ordering = ES::normalize($options, 'ordering', '');
		$direction = ES::normalize($options, 'direction', '');

		if ($ordering) {
			$sql->order($ordering, $direction);
		}

		$limit = $this->getState('limit', 0);

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getSql(), true);

			// Get the list of users
			$result = parent::getData($sql->getSql());
		} else {

			// Set the total
			$this->setTotal($sql->getTotalSql());

			// Get the result using parent's helper
			$result = $this->getData($sql);
		}

		if (!$result) {
			return $result;
		}

		$apps = [];

		foreach ($result as $row) {
			$appTable = ES::app($row);

			$apps[] = $appTable;
		}

		return $apps;
	}


	/**
	 * Retrieve a list of applications that is installed on the site.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of configuration.
	 * @return	Array	An array of application object.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps');

		// Determine if we should only fetch apps that are widgets
		$widget = ES::normalize($options, 'widget', false);

		if ($widget) {
			$sql->where('widget', SOCIAL_STATE_PUBLISHED);
		}

		// Depending on group of apps.
		$group = ES::normalize($options, 'group', '');

		if ($group) {
			$sql->where('group', $group);
		}

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$apps = array();

		foreach ($result as $row) {
			$appTable = ES::app($row);

			$apps[] = $appTable;
		}

		return $apps;
	}

	/**
	 * Retrieve a list of SocialTableAppViews for an app.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	The app's id.
	 * @return
	 */
	public function getViews($appId)
	{
		// TODO: Change this to where case.
		$cache = ES::dbcache('appview');
		$result = $cache->loadObjectList(array('app_id' => $appId));
		$views = $cache->bindTable($result);

		return $views;
	}

	public function getElement($type, $element, $lookup)
	{
		$path = SOCIAL_MEDIA . DS . constant('SOCIAL_APPS_'. strtoupper($type)) . DS . $element . DS . $element . '.xml';
		$data = JText::_('Unknown');
		$xml = ES::get('Parser')->read($path);

		if (isset($xml->{$lookup})) {
			$data = $xml->{$lookup};
		}
		return $data;
	}

	/**
	 * Get's a list of folder and determines if the folder is writable.
	 *
	 * @since	1.0
	 * @access	public
	 * @return	Array	An array of stdClass objects.
	 *
	 */
	public function getDirectoryPermissions()
	{
		$jConfig = ES::jconfig();

		// Get a list of folders.
		$folders = array(
						$jConfig->getValue('tmp_path'),
						SOCIAL_MEDIA,
						SOCIAL_APPS . '/fields',
						SOCIAL_APPS . '/user'
					);

		$directories = array();

		foreach ($folders as $folder) {
			$obj = new stdClass();
			$obj->path = $folder;
			$obj->writable = is_writable($folder);

			$directories[] = $obj;
		}

		return $directories;
	}

	/**
	 * This is a temporary method until @1.3 allows the group the ability to add new apps
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getGroupApps($groupId, $respectView = false, $categoryId = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps', 'a');
		$sql->column('a.*');

		$sql->where('a.group', SOCIAL_TYPE_GROUP);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.type', SOCIAL_APPS_TYPE_APPS);
		$sql->where('a.system', SOCIAL_STATE_PUBLISHED, '!=');

		// exclude the app do not show into the navigation bar
		$exclusionApps = array('qrcode', 'marketplaces_submission');

		$sql->where('a.element', $exclusionApps, 'not in');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$apps = array();

		foreach ($result as $row) {
			$app = ES::app($row);

			$hasListing = $app->appListing('groups', $groupId, SOCIAL_TYPE_GROUP);

			$group = ES::group($groupId);
			$categoryId = $categoryId ? $categoryId : $group->getCategory()->id;

			$canAccess = $app->hasAccess($categoryId);

			$hasView = true;
			if ($respectView) {
				$hasView = $app->hasView('groups');
			}

			if ($hasListing && $canAccess && $hasView) {
				// 3rd party apps might have their language strings
				$app->loadLanguage();

				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * This is a temporary method until @future allows the page the ability to add new apps
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getPageApps($pageId, $respectView = false, $categoryId = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps', 'a');
		$sql->column('a.*');

		$sql->where('a.group', SOCIAL_TYPE_PAGE);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.type', SOCIAL_APPS_TYPE_APPS);
		$sql->where('a.system', SOCIAL_STATE_PUBLISHED, '!=');

		// exclude the app do not show into the navigation bar
		$exclusionApps = array('qrcode', 'marketplaces_submission');

		$sql->where('a.element', $exclusionApps, 'not in');

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$apps = array();

		foreach ($result as $row) {
			$app = ES::app($row);

			$hasListing = $app->appListing('pages', $pageId, SOCIAL_TYPE_PAGE);

			$page = ES::page($pageId);
			$categoryId = $categoryId ? $categoryId : $page->getCategory()->id;

			$canAccess = $app->hasAccess($categoryId);

			$hasView = true;
			if ($respectView) {
				$hasView = $app->hasView('pages');
			}

			if ($hasListing && $canAccess && $hasView) {
				// 3rd party apps might have their language strings
				$app->loadLanguage();

				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * This is a temporary method until @future allows the event the ability to add new apps
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getEventApps($eventId, $respectView = false, $categoryId = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps');

		$sql->where('group', SOCIAL_TYPE_EVENT);
		$sql->where('state', SOCIAL_STATE_PUBLISHED);
		$sql->where('type', SOCIAL_APPS_TYPE_APPS);
		$sql->where('system', SOCIAL_STATE_PUBLISHED, '!=');

		// exclude the app do not show into the navigation bar
		$exclusionApps = array('qrcode');

		$sql->where('element', $exclusionApps, 'not in');

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$apps = array();

		foreach ($result as $row) {
			$app = ES::app($row);

			$hasListing = $app->appListing('events', $eventId, SOCIAL_TYPE_GROUP);

			$event = ES::event($eventId);
			$categoryId = $categoryId ? $categoryId : $event->getCategory()->id;

			$canAccess = $app->hasAccess($categoryId);

			$hasView = true;
			if ($respectView) {
				$hasView = $app->hasView('events');
			}

			if ($hasListing && $canAccess && $hasView) {
				// 3rd party apps might have their language strings
				$app->loadLanguage();

				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Returns a list of field type applications that are installed and published.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getApps($options = array(), $debug = false)
	{
		static $cache = array();

		$db = ES::db();
		$sql = $db->sql();

		// Serialize the key so that we can cache them
		ksort($options);
		$idx = serialize($options);

		if (!isset($cache[$idx])) {

			$sql->select('#__social_apps', 'a');
			$sql->column('a.*');

			// If uid / key is passed in, we need to only fetch apps that are related to the uid / key.
			$uid = $this->normalize($options, 'uid');
			$key = $this->normalize($options, 'key');

			if (!is_null($uid) && !is_null($key)) {
				$sql->join('#__social_apps_map', 'b');
				$sql->on('b.app_id', 'a.id');
				$sql->on('b.uid', $uid);
				$sql->on('b.type', $key);

				$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
			}

			// Test if 'view' is provided. If view is provided, we only want to fetch apps for these views.
			$view = $this->normalize($options, 'view');

			if (!is_null($view)) {
				$sql->innerjoin('#__social_apps_views', 'c');
				$sql->on('c.app_id', 'a.id');

				// Need to get the dashboard view as well.
				if ($view == 'profile') {
					$sql->on('(');
					$sql->on('c.view', $view);
					$sql->on('c.view', 'dashboard', '=', 'OR');
					$sql->on(')');
				} else {
					$sql->on('c.view', $view);
				}
			}

			// If state filter is provided, we need to filter the state.
			$state = $this->normalize($options, 'state');

			if (!is_null($state)) {
				$sql->where('a.state', $state);
			}

			// If type filter is provided, we need to filter the type.
			$type = $this->normalize($options, 'type');

			if (!is_null($type)) {
				$sql->where('a.type', $type);
			}

			// If group filter is provided, we need to filter apps by group.
			$group = $this->normalize($options, 'group');

			if (!is_null($group)) {
				$sql->where('a.group', $group);
			}

			// Detect if we should only pull apps that are installable
			$installable = $this->normalize($options, 'installable');

			if (!is_null($installable)) {

				// this flag used only in my apps page and use conjunction with 'installable'. #1657
				$includeDefault = $this->normalize($options, 'includedefault');

				$sql->where('(', '', '', 'AND');
				$sql->where('a.installable', $installable , '=' , 'AND');

				if (!$includeDefault) {
					$sql->where('a.default', SOCIAL_STATE_PUBLISHED, '!=', 'AND');
				}

				$sql->where(')');
			}

			// Check for widgets
			$widgets = $this->normalize($options, 'widget');

			if ($widgets) {
				$sql->where('a.widget', $widgets);
			}

			// Check for core app
			$core = $this->normalize($options, 'core');

			// If core is provided, we want to load core apps
			if (!is_null($core)) {
				$sql->where('a.core', $core);
			}

			// What is this?
			if (!is_null($uid) && !is_null($key) && $group != 'group') {
				$sql->where( '(' , '' , '' , 'AND' );
				$sql->where( 'a.default' , SOCIAL_STATE_PUBLISHED , '=' , 'OR' );
				$sql->where( 'b.id' , null , 'IS NOT' , 'OR' );

				if ($widgets) {
					$sql->where('a.system', true , '=' , 'OR');
				}

				// If there is a list of inclusion given, we need to include these apps as well
				$inclusion = $this->normalize($options, 'inclusion', null);

				if (!is_null($inclusion) && $inclusion) {
					$sql->where('a.id', $inclusion, 'IN', 'OR');
				}

				$sql->where( ')' );
			}

			// this is to get the default apps which are published on the site.
			if (!$uid && !$key && is_null($installable) && (is_null($type) || $type == SOCIAL_APPS_TYPE_APPS)) {
				$sql->where('(', '', '', 'OR');
				$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
				$sql->where('a.default', SOCIAL_STATE_PUBLISHED);
				$sql->where(')');
			}

			// Sorting and ordering options
			$sort = $this->normalize($options, 'sort');

			if (!is_null($sort)) {
				$order = $this->normalize($options, 'order', 'asc');
				$sql->order($sort, $order);
			}

			// cache the total

			// Set the total query.
			$this->setTotal($sql->getTotalSql());

			// echo $sql;
			// echo '<br/><br/>';
			// exit;

			// Get data
			$result = $this->getData($sql, false);

			if (!$result) {
				$cache[$idx] = array();
				return $cache[$idx];
			}

			$apps = array();

			foreach ($result as $row) {
				$app = ES::app($row);

				if (($group == SOCIAL_TYPE_USER || $key == SOCIAL_TYPE_USER) && $uid && !$app->hasAccess(ES::user($uid)->profile_id)) {
					continue;
				}

				// // Check if the apps should really have such view
				// if (!$app->appListing($view)) {
				// 	continue;
				// }

				// 3rd party apps might have their language strings
				$app->loadLanguage();

				$apps[]	= $app;
			}

			$cache[$idx] = $apps;
		}

		return $cache[$idx];
	}


	/**
	 * Returns a list of user type applications that are installed and published.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserApps($userId, $view = '', $options = array())
	{
		$db = ES::db();

		// to support default / core apps when retrieving users apps.
		$includeDefault = isset($options['includeDefault']) ? $options['includeDefault'] : false;

		$viewQuery = array();
		$viewQuery[] = 'INNER JOIN `#__social_apps_views` AS `c`';
		$viewQuery[] = 'ON c.`app_id` = a.`id`';
		$viewQuery[] = 'AND (';
		$viewQuery[] = 'c.`view` = ' . $db->Quote('profile');
		$viewQuery[] = 'OR';
		$viewQuery[] = 'c.`view` = ' . $db->Quote('dashboard');
		$viewQuery[] = ')';
		$viewQuery = implode(' ', $viewQuery);


		$query = array();

		// user installed apps
		$query[] = 'SELECT a.* FROM `#__social_apps` AS a';
		$query[] = 'INNER JOIN `#__social_apps_map` AS b';
		$query[] = 'ON b.`app_id` = a.`id`';

		if ($userId) {
			$query[] = 'AND b.`uid`=' . $db->Quote($userId);
		}

		$query[] = 'AND b.`type`=' . $db->Quote(SOCIAL_APPS_GROUP_USER);

		// Test if 'view' is provided. If view is provided, we only want to fetch apps for these views.
		if ($view) {
			$query[] = $viewQuery;
		}

		$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = 'AND a.`type`=' . $db->Quote(SOCIAL_APPS_TYPE_APPS);
		$query[] = 'AND a.`group`=' . $db->Quote(SOCIAL_APPS_GROUP_USER);

		if ($includeDefault) {
			// union here
			$query[] = 'UNION ALL';

			// site defaults apps
			$query[] = 'SELECT a.* FROM `#__social_apps` AS a';

			// Test if 'view' is provided. If view is provided, we only want to fetch apps for these views.
			if ($view) {
				$query[] = $viewQuery;
			}

			$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED);
			$query[] = 'AND a.`type`=' . $db->Quote(SOCIAL_APPS_TYPE_APPS);
			$query[] = 'AND a.`group`=' . $db->Quote(SOCIAL_APPS_GROUP_USER);
			$query[] = 'AND (a.`core` = 1 OR a.`default` = 1)';
		}

		$query = implode(' ', $query);

		// echo $query;exit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		$user = ES::user($userId);

		// manual grouping.
		// since this query do not have any limit constraint, we will
		// do the manual grouping instead of group by to boost the sql performance.
		$apps = array();
		foreach ($result as $row) {

			if (!isset($apps[$row->id])) {
				$app = ES::app($row);

				if ($app->appListing('profile', $user->id) && $app->hasAccess($user->profile_id)) {
					$apps[$row->id] = $app;
				}
			}
		}

		// Set the total
		// since this query do not have any limit constraint, we will
		// just use count to avoid another query to count the result.
		$this->setTotalCount(count($apps));

		return $apps;
	}



	/**
	 * Retrieve a list of core apps from the site.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getDefaultApps($config = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps', 'a');
		$sql->column('a.*');

		$sql->where('(');
		$sql->where('a.core', '1');
		$sql->where('a.default', '1', '=', 'or');
		$sql->where(')');
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		// If caller wants only specific type of apps.
		if (isset($config['type'])) {
			$sql->where('a.type', $config['type']);
		}

		$db->setQuery($sql);

		$fields	= $db->loadObjectList();

		return $fields;
	}

	/**
	 * Returns a list of tending apps from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTrendingApps($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps', 'a');
		$sql->column('a.*');

		$sql->leftjoin('#__social_apps_map', 'b');
		$sql->on('a.id', 'b.app_id');

		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		if (isset($options['type'])) {
			$sql->where('a.type', $options['type']);
		}

		if (isset($options['timefrom'])) {
			$sql->where('b.created', ES::date($options['timefrom'])->toSql(), '>=');
		}

		if (isset($options['timeto'])) {
			$sql->where('b.created', ES::date($options['timeto'])->toSql(), '<=');
		}


		// If group filter is provided, we need to filter apps by group.
		$group = isset($options['group']) ? $options['group'] : null;

		if (!is_null($group)) {
			$sql->where( 'a.group', $group );
		}

		// Determines if caller wants to only display the installable apps
		$installable = isset($options['installable']) ? $options['isntallable'] : '';

		if ($installable) {
			$sql->where('(', '', '', 'AND');
			$sql->where('a.installable', $installable, '=', 'AND');
			$sql->where('a.default', SOCIAL_STATE_PUBLISHED, '!=', 'AND');
			$sql->where(')');

			$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		}

		$sql->group('a.id');
		$sql->order('b.app_id', 'desc', 'count');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$apps = array();

		foreach ($result as $row) {
			$app = ES::app($row);
			$apps[] = $app;
		}

		return $apps;
	}

	public function assignProfileUsersApps($profileId, $appId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$now = ES::date()->toSql();

		$query = "insert into `#__social_apps_map` (`uid`, `type`, `app_id`, `created`) select a.user_id, 'user', " . $db->Quote($appId) . ", " . $db->Quote($now);
		$query .= " from `#__social_profiles_maps` as a";
		$query .= " where not exists (select b.`uid` from `#__social_apps_map` as b where b.`uid` = a.`user_id` and b.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and b.`app_id` = " . $db->Quote($appId) . ")";
		$query .= " and a.`profile_id` = " . $db->Quote($profileId);

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();
		return $state;
	}

	/**
	 * Allows caller to update the access for an app.
	 * It uses FIFO method
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function updateAccess(SocialTableApp $app, $values = array())
	{
		$db = ES::db();

		$access = ES::table('AppsAccess');
		$access->load(array('app_id' => $app->id));
		$access->app_id = $app->id;
		$access->value = json_encode($values);

		return $access->store();
	}

	/**
	 * Allows caller to update the access for an app.
	 * It uses FIFO method
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getWidgetApps($group, $options = array())
	{
		static $_cache = array();

		$uid = $this->normalize($options, 'uid');
		$key = $this->normalize($options, 'key');

		$idx = $group;

		if (!is_null($uid) && !is_null($key)) {
			$idx .= '-' . $uid . '-' . $key;
		}

		if (!isset($_cache[$idx])) {
			$db = ES::db();

			$query = "SELECT `a`.*";
			$query .= " FROM `#__social_apps` AS a";

			// If uid / key is passed in, we need to only fetch apps that are related to the uid / key.
			if (!is_null($uid) && !is_null($key)) {
				$query .= " LEFT JOIN  `#__social_apps_map` as b";
				$query .= " ON b.`app_id` = a.`id`";

				$query .= " AND b.`uid` = " . $db->Quote($uid);
				$query .= " AND b.`type` = " . $db->Quote($key);
			}

			$query .= " WHERE a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
			$query .= " AND a.`type` = " . $db->Quote(SOCIAL_APPS_TYPE_APPS);
			$query .= " AND a.`group` = " . $db->Quote($group);
			$query .= " AND a.`widget` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

			// load the system / default apps
			if (!is_null($uid) && !is_null($key) && $group != 'group') {
				$query .= " AND (`a`.`default` = 1";
				$query .= "			OR `b`.`id` IS NOT NULL";
				$query .= "			OR `a`.`system` = 1)";
			}

			$query .= " ORDER BY a.`ordering` asc";

			// echo $query;
			// echo '<br><br>';

			$db->setQuery($query);
			$results = $db->loadObjectList();

			$apps = array();

			foreach ($results as $row) {
				$app = ES::app($row);

				if (($group == SOCIAL_TYPE_USER || $key == SOCIAL_TYPE_USER) && $uid && !$app->hasAccess(ES::user($uid)->profile_id)) {
					continue;
				}

				// 3rd party apps might have their language strings
				$app->loadLanguage();

				$apps[]	= $app;
			}

			$_cache[$idx] = $apps;
		}

		return $_cache[$idx];

	}

	/**
	 * Determines if the app enable or not
	 *
	 * @since	3.0.3
	 * @access	public
	 */
	public function isAppEnabled($element, $group = array(SOCIAL_APPS_GROUP_USER), $type = 'apps')
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.`id`, a.`element`, a.`group`, a.`state` FROM ' . $db->qn('#__social_apps') . ' AS a';
		$query[] = 'WHERE a.' . $db->qn('type') . ' = ' . $db->Quote($type);
		$query[] = 'AND a.' . $db->qn('element') . ' = ' . $db->Quote($element);
		$query[] = 'AND a.' . $db->qn('group') . ' IN (' . implode(',', $db->Quote($group)) . ')';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Rebuild ordering for apps
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function rebuildOrdering($group = false)
	{
		$db = ES::db();

		$groups = array('user', 'group', 'page', 'event');

		if ($group && in_array($group, $groups)) {
			$groups = array($group);
		}

		foreach ($groups as $group) {
			$querySet1 = "SET @ordering_interval = 1";
			$querySet2 = "SET @new_ordering = 0";

			$query = "UPDATE `#__social_apps` SET";
			$query .= " `ordering` = (@new_ordering := @new_ordering + @ordering_interval)";
			$query .= " WHERE `group` = " . $db->Quote($group);
			$query .= " AND `type` = " . $db->Quote('apps');
			$query .= " ORDER BY `ordering` ASC";

			// execute ordering_interval variable initiation.
			$db->setQuery($querySet1);
			$db->query();

			// execute new_ordering variable initiation.
			$db->setQuery($querySet2);
			$db->query();

			// now perform the update
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

	/**
	 * Update apps ordering
	 *
	 * @since   3.2
	 * @access  public
	 */
	public function updateAppsOrdering($id, $order)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "update `#__social_apps` set ordering = " . $db->Quote($order);
		$query .= " where id = " . $db->Quote($id);

		$sql->raw($query);

		$db->setQuery($sql);
		$state = $db->query();

		return $state;
	}
}
