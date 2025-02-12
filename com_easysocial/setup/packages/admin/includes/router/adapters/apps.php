<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterApps extends SocialRouterAdapter
{
	/**
	 * Construct's the app's url
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu, &$query)
	{
		$skipView = false;
		$segments = array();
		$clusterTypes = array('group', 'event', 'page', 'user');
		$ignoreLayout = false;
		$isCluster = false;

		$xViews = array('group' => 'groups', 'page' => 'pages', 'event' => 'events', 'user' => 'user');

		// http://site.com/index.php?option=com_easysocial&view=apps&layout=canvas&uid=14:susan-group&type=group&id=30:discussions&customView=create&Itemid=121

		// http://site.com/index.php?option=com_easysocial&view=apps&layout=canvas&id=10:feeds&cid=1&uid=600:susan&type=user&Itemid=153

		if (isset($query['uid']) && isset($query['type']) && in_array($query['type'], $clusterTypes)) {

			$addExtraSegments = true;
			// we need to determine if we need to add below segments or not
			if (isset($query['Itemid'])) {
				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);

				if ($xMenu) {
					$xquery = $xMenu->query;

					$utype = $query['type'];
					$xView = $xViews[$utype];

					if ($xquery['view'] == $xView && isset($xquery['layout']) && $xquery['layout'] == 'item' && isset($xquery['id'])) {
						$xId = (int) $xquery['id'];
						$tId = (int) $query['uid'];
						if ($xId == $tId) {
							$addExtraSegments = false;
						}
					}

					// To prevent this case: pages/pages/album (menuitem/view/album)
					if ($xquery['view'] == $xView && !isset($xquery['layout'])) {
						$addExtraSegments = false;
						$segments[] = ESR::normalizePermalink($query['uid']);
					}

					// If the event menu is provided, then we remove the event id from the url.
					// Currently, we only do this for event.
					if ($xquery['view'] == 'events' && $query['view'] == 'apps' && $xView == 'events') {
						$addExtraSegments = false;
					}
				}
			}

			$type = $query['type'];
			if ($addExtraSegments) {
				// we need to change Itemid to respect the culster type.
				$utype = $query['type'];
				$xView = $xViews[$utype];

				$xMenu = JFactory::getApplication()->getMenu('site')->getItem($query['Itemid']);
				if ($xMenu) {
					$xquery = $xMenu->query;

					if ($xquery['view'] != $xView) {
						$query['Itemid'] = ESR::getItemId($xView, 'item', (int) $query['uid']);
						$addExtraView = true;

						if ($type != 'user') {
							$segments[] = $this->translate($xView);
						}
					}
				}

				$segments[] = ESR::normalizePermalink($query['uid']);
			}

			unset($query['uid']);
			unset($query['type']);

			$isCluster = true;
			$ignoreLayout = true;
		}

		if ($menu && $menu->query['view'] == 'apps') {
			$skipView = true;

		} else if ($isCluster) {
			$skipView = true;

		} else {
			$skipView = true;
			$segments[] = $this->translate($query['view']);
		}

		unset($query['view']);

		// From here if element is set, then we point it to child app router and build the segments from the child app router
		// If not then we proceed with the usual
		// We default group to SOCIAL_APPS_GROUP_USER
		if (!empty($query['element'])) {
			$group = empty($query['group']) ? SOCIAL_APPS_GROUP_USER : $query['group'];
			$element = $query['element'];

			unset($query['group']);
			unset($query['element']);

			$router = $this->getAppRouter($group, $element);

			// If unable to get the app router, then skip this and continue the normal build
			if ($router !== false && is_callable(array($router, 'build'))) {
				// If router is available, then we append group and element into the segments
				$segments += array($this->translate($this->name . '_' . $group), $this->translate($this->name . '_' . $group . '_' . $element));

				// Append the returned array into the original segment
				$segments += $router->build($menu, $query);

				return $segments;
			}
		}

		// Get the layout
		$layout = $this->normalize($query, 'layout');
		if (!is_null($layout) && !$ignoreLayout) {
			$segments[]	= $this->translate('apps_layout_' . $layout);
		}
		unset($query['layout']);

		// Get the app id
		$appId = $this->normalize($query, 'id');

		if (!is_null($appId)) {
			$segments[]	= ESR::normalizePermalink($appId);
			unset($query['id']);
		}

		// Determines if filter is set
		$filter = $this->normalize($query, 'filter');

		if (!is_null($filter)) {
			$segments[]	= $this->translate('apps_filter_' . $filter);
			unset($query['filter']);
		}

		// Translate uid and type for apps view
		$uid = $this->normalize($query, 'uid');
		$type = $this->normalize($query, 'type');

		if(!is_null($uid) && !is_null($type)) {
			$segments[]	= $type;
			$segments[]	= $uid;

			unset($query['uid']);
			unset($query['type']);
		}

		//determines if customView is set
		$customView = isset($query['customView']) ? $query['customView'] : null;
		if (!is_null($customView) && $appId) {
			// $segments[] = $this->translate('apps_customview_' . $customView);
			// unset($query['customView']);
			$this->buildAppsRoute($appId, $menu, $query, $segments);
		}


		// Determines if filter is set
		$sort = isset($query['sort']) ? $query['sort'] : null;
		if (!is_null($sort)) {
			$segments[]	= $this->translate('apps_sort_' . $sort);
			unset($query['sort']);
		}

		// Determines if userid is set
		$userId = isset($query['userid']) ? $query['userid'] : null;
		if (!is_null($userId)) {
			$segments[]	= ESR::normalizePermalink($query['userid']);
			unset($query['userid']);
		}

		$cid = isset($query['cid']) ? $query['cid'] : null;
		if (!is_null($cid)) {
			$segments[]	= ESR::normalizePermalink($query['cid']);
			unset($query['cid']);
		}

		// Get the item id if the layout is canvas
		if ($layout == 'canvas' && $uid && $type) {

			$customView = 'events';

			if ($type == 'group') {
				$customView = 'groups';
			} else if ($type == 'page') {
				$customView = 'pages';
			}

			// Ensure that the view already isn't added
			if (!$skipView) {
				array_unshift($segments, $this->translate('apps'));
			}

			// Try to get the item id
			$query['Itemid'] = ESR::getItemId($customView, 'item', (int) $uid);
		}

		return $segments;
	}


	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	An array of url segments
	 * @return	array 	The query string data
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		// var_dump('apps~', $segments);
		// echo '<br />';

		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu
		$activeMenu = $menu->getActive();

		// For albums on group pages, we need to parse it differently as it was composed differently with a menu id on the site
		// The activemenu MUST have the appropriate query data
		if ($activeMenu && isset($activeMenu->query['view']) && isset($activeMenu->query['layout']) && isset($activeMenu->query['id'])) {

			// Since there is parts of the group in the menu parameters, we can safely assume that the user is viewing a group item page.
			if (($activeMenu->query['view'] == 'groups' || $activeMenu->query['view'] == 'events' || $activeMenu->query['view'] == 'pages')
				&& $activeMenu->query['layout'] == 'item' && $activeMenu->query['id']) {
				$uid = $activeMenu->query['id'];

				if ($total > 1) {
					// we need to re-arrange the segments to simulate the groups albums.

					$addItemLayout = true;
					if (($segments[1] == $this->translate( 'albums_layout_form') || $segments[1] == 'form') ||
						($segments[1] == $this->translate( 'albums_layout_all') || $segments[1] == 'all')) {
						$addItemLayout = false;
					}

					$firstSegment = array_shift($segments);
					if ($addItemLayout) {
						// array_unshift($segments, 'item'); // we need this layout 'item'
					}

					$clusterType = 'group';
					if ($activeMenu->query['view'] == 'events') {
						$clusterType = 'event';
					} else if ($activeMenu->query['view'] == 'pages') {
						$clusterType = 'page';
					}

					// now we add back the first element;
					array_unshift($segments, $firstSegment, $clusterType, $uid);
				}
			}
		}

		// reset the total count.
		$total = count($segments);

		// var_dump('apps', $segments);exit;


		// URL: http://site.com/menu/apps
		if ($total == 1 && $segments[0] == $this->translate('apps')) {
			$vars['view']	= 'apps';

			return $vars;
		}

		// Check if should go to child router or not
		// URL: http://site.com/menu/apps/group/element
		if ($total >= 3) {

			// Check for possible group here
			if ($segments[1] === $this->translate($this->name . '_' . SOCIAL_APPS_GROUP_USER)) {
				$group = $segments[1];
				$element = $segments[2];

				$router = $this->getAppRouter($group, $element);

				if ($router !== false && is_callable(array($router, 'parse'))) {
					// Only need to pass the remaining segments
					$rebuild = array();

					if ($total > 3) {
						$rebuild = array_slice($segments, 3);
					}

					$childVars = $router->parse($rebuild);

					// It is possible that childVars return false because child router can verify if the url is valid or not
					if ($childVars === false) {
						return array();
					}

					$vars = array_merge($vars, $childVars);

					return $vars;
				}
			}

			// If no group matched, then we proceed to normal parsing below
		}

		// URL: http://site.com/menu/apps/mine
		if ($total == 2 && $segments[0] == $this->translate('apps') && $segments[1] == $this->translate('apps_filter_mine')) {
			$vars['view'] = 'apps';
			$vars['filter']	= 'mine';

			return $vars;
		}

		// URL: http://site.com/menu/apps/trending
		$sortItems	= array($this->translate('apps_sort_alphabetical') , $this->translate('apps_sort_trending') , $this->translate('apps_sort_recent'));

		if ($total == 2 && in_array($segments[1] , $sortItems)) {
			$vars['view'] = 'apps';

			if ($segments[1] == $this->translate('apps_sort_alphabetical')) {
				$sort = 'alphabetical';
			}

			if ($segments[1] == $this->translate('apps_sort_trending')) {
				$sort = 'trending';
			}

			if ($segments[1] == $this->translate('apps_sort_recent')) {
				$sort = 'recent';
			}

			$vars['sort'] = $sort;

			return $vars;
		}

		// URL: http://site.com/menu/apps/ID-app-alias
		if ($total == 2) {
			$vars['view'] = 'apps';
			$vars['layout']	= 'canvas';
			$vars['id'] = $segments[2];

			return $vars;
		}

		$clusterTypes = array('group', 'event', 'page');
		$clusterViews = array('group' => 'groups', 'event' => 'events', 'page' => 'pages');

		if ($total > 2) {
			if (in_array($segments[1], $clusterTypes) && isset($segments[3])) {

				$appId = $this->getIdFromPermalink($segments[3] , SOCIAL_TYPE_APPS);

				$vars['view'] = 'apps';
				$vars['layout'] = 'canvas';
				$vars['id'] = $appId;
				$vars['type'] = $segments[1];
				$vars['uid'] = $this->getUserId($segments[2]);

				$this->parseAppsRoute($appId, $segments, $vars);

				return $vars;
			}

			// this seem like the url is not complete. lets go back to the original page.
			if (in_array($segments[1], $clusterTypes) && isset($segments[2])) {

				$clusterType = $segments[1];
				$vars['view'] = $clusterViews[$clusterType];
				$vars['layout'] = 'item';
				$vars['id'] = $segments[2];

				return $vars;
			}

		}


		// URL: http://site.com/menu/apps/ID-app-alias/ID-user-alias
		if ($total >= 3 && $segments[1] == $this->translate('apps_layout_canvas')) {
			$vars['view'] = 'apps';
			$vars['layout'] = 'canvas';
			$vars['id'] = $segments[2];

			if (isset($segments[3]) && $segments[3]) {
				$vars['userid'] = $this->getUserId($segments[3]);
			}

			return $vars;
		}

		// // URL: http://site.com/menu/apps/user/ID-user-alias/ID-app-alias
		if ($total == 4 && $segments[1] == 'user') {
			$vars['view'] = 'apps';
			$vars['layout'] = 'canvas';
			$vars['id'] = $this->getIdFromPermalink($segments[3] , SOCIAL_TYPE_APPS);
			$vars['type'] = $segments[1];
			$vars['uid'] = $this->getUserId($segments[2]);

			return $vars;
		}

		return $vars;
	}

	/**
	 * Retrieve the ordering
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function getClusterType($translated)
	{
		if ($translated == $this->translate('apps_group')) {
			return 'group';
		}

		if ($translated == $this->translate('apps_page')) {
			return 'page';
		}

		if ($translated == $this->translate('apps_event')) {
			return 'event';
		}

		// Default to return user
		return 'user';
	}

	private function buildAppsRoute($appId, &$menu, &$query, &$segments)
	{
		$id = (int) $appId;
		$app = ES::table('App');
		$app->load($id);

		$element = $app->element;
		$group = $app->group;

		// We first check if this app has it own router php or not. If yes, we use it.
		//      e.g. /media/com_easysocial/apps/group/discussions/router.php
		// If no, we will use our own app router.
		//      e.g. /administrator/components/com_easysocial/includes/router/adapters/apps/news.php
		// If both router files not exists, we will skip the processing.

		$file = SOCIAL_APPS . '/' . $group . '/' . $element . '/router.php';
		if (!JFile::exists($file)) {
			$file = SOCIAL_LIB . '/router/adapters/apps/' . $element . '.php';
			if (!JFile::exists($file)) {
				return false;
			}
		}

		$classname = 'SocialRouterApp' . ucfirst($element);
		if (!class_exists($classname)) {
			require_once($file);
		}

		$appRouter = new $classname($element);
		$segments = $appRouter->build($menu, $query, $segments);
	}


	private function parseAppsRoute($appId, &$segments, &$vars)
	{
		$id = (int) $appId;
		$app = ES::table('App');
		$app->load($id);

		$element = $app->element;
		$group = $app->group;

		// We first check if this app has it own router php or not. If yes, we use it.
		//      e.g. /media/com_easysocial/apps/group/discussions/router.php
		// If no, we will use our own app router.
		//      e.g. /administrator/components/com_easysocial/includes/router/adapters/apps/news.php
		// If both router files not exists, we will skip the processing.

		$file = SOCIAL_APPS . '/' . $group . '/' . $element . '/router.php';
		if (!JFile::exists($file)) {
			$file = SOCIAL_LIB . '/router/adapters/apps/' . $element . '.php';
			if (!JFile::exists($file)) {
				return false;
			}
		}

		$classname = 'SocialRouterApp' . ucfirst($element);
		if (!class_exists($classname)) {
			require_once($file);
		}

		$appRouter = new $classname($element);
		$vars = $appRouter->parse($segments, $vars);
	}


	private function getAppRouter($group, $element)
	{
		static $adapters = array();

		if(empty($adapters[$group][$element]))
		{
			$file = SOCIAL_APPS . '/' . $group . '/' . $element . '/router.php';

			if(!JFile::exists($file))
			{
				return false;
			}

			$classname = 'SocialRouterApps' . ucfirst($group) . ucfirst($element);

			if(!class_exists($classname))
			{
				require_once($file);
			}

			require_once($file);

			if(!class_exists($classname))
			{
				return false;
			}

			$class = new $classname($this->name);

			// Init a few properties
			$class->group = $group;
			$class->element = $element;

			$adapters[$group][$element] = $class;
		}

		return $adapters[$group][$element];
	}
}

abstract class SocialRouterAppsAdapter extends SocialRouterApps
{
	// This is a function for child router to use
	// Rather than constructing a the translation string from APPS_GROUP_ELEMENT_TASK, child router only need to pass in TASK
	public function subtranslate($task)
	{
		$prefix = $this->name . '_' . $this->group . '_' . $this->element;

		$string = $prefix . '_' . $task;

		return $this->translate($string);
	}
}
