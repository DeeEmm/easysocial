<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterDashboard extends SocialRouterAdapter
{
	/**
	 * Constructs dashboard urls
	 *
	 * @since	1.0
	 * @access	public
	 * @param	JMenu 	The active menu object.
	 * @param	array 	An array of query strings
	 * @return	array 	The url structure
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is no active menu for profile, we need to add the view, otherwise we
		// would not be able to determine the correct profile to display.
		if ($menu && $menu->query['view'] != 'dashboard') {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

		// Translate the app id
		$appId 	= isset($query['appId']) ? $query['appId'] : null;

		// If app id is provided, get the app alias.
		if (!is_null($appId)) {
			$segments[]	= $this->translate('dashboard_filter_app');
			$segments[]	= $appId;
			unset($query['appId']);
		}

		// Stream filters on normal html requests
		$filter = isset($query['type']) ? $query['type'] : '';

		if ($filter) {
			if ($filter == 'all' || $filter == 'me') {
				unset($query['type']);
			} else {
				$segments[]	= $this->translate('dashboard_' . $filter);
				unset($query['type']);
			}
		}

		// Stream filters on feed pages. We cannot use "type" because feed would break
		$filter = isset($query['filter']) ? $query['filter'] : '';

		if ($filter) {
			if ($filter == 'all' || $filter == 'me') {
				unset($query['filter']);
			} else {
				$segments[] = $this->translate('dashboard_' . $filter);
				unset($query['filter']);
			}
		}

		// There could be a possibility that this is filter by groups
		$groupId = isset($query['groupId']) ? $query['groupId'] : '';

		if ($groupId) {
			$segments[]	= $groupId;
			unset($query['groupId']);
		}

		// There could be a possibility that this is filter by events
		$eventId = isset($query['eventId']) ? $query['eventId'] : '';

		if ($eventId) {
			$segments[]	= $eventId;
			unset($query['eventId']);
		}

		// There could be a possibility that this is filter by pages
		$pageId = isset($query['pageId']) ? $query['pageId'] : '';

		if ($pageId) {
			$segments[]	= $pageId;
			unset($query['pageId']);
		}


		// Translate list id in the query string
		$listId = isset($query['listId']) ? $query['listId'] : '';

		if ($listId) {
			$segments[] = $listId;
			unset($query['listId']);
		}

		if (isset($query['layout'])) {
			$segments[]	= $this->translate('dashboard_layout_' . $query['layout']);

			unset($query['layout']);
		}

		if (isset($query['tag'])) {
			$segments[]	= $query['tag'];

			unset($query['tag']);
		}

		if (isset($query['filterid'])) {
			$segments[]	= $query['filterid'];
			unset($query['filterid']);
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

		$doc = JFactory::getDocument();

		if ($total == 1 && ($segments[0] == $this->translate('dashboard') || $segments[0] == 'dashboard')) {
			$vars['view'] = 'dashboard';

			return $vars;
		}

		// /dashboard/following
		if ($total == 2 && ($segments[0] == $this->translate('dashboard') || $segments[0] == 'dashboard') && $segments[1] == $this->translate('dashboard_following')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'following';

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// /dashboard/bookmarks
		if ($total == 2 && ($segments[0] == $this->translate('dashboard') || $segments[0] == 'dashboard') && $segments[1] == $this->translate('dashboard_bookmarks')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'bookmarks';

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// /dashboard/bookmarks
		if ($total == 2 && ($segments[0] == $this->translate('dashboard') || $segments[0] == 'dashboard') && $segments[1] == $this->translate('dashboard_sticky')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'sticky';

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// /dashboard/group/ID-xxxx
		if ($total == 3 && $segments[1] == $this->translate('dashboard_group')) {

			$vars['view'] = 'dashboard';
			$vars['type'] = 'group';
			$vars['groupId'] = $this->getIdFromPermalink($segments[2]);

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// /dashboard/group/ID-xxxx
		if ($total == 3 && $segments[1] == $this->translate('dashboard_event')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'event';
			$vars['eventId'] = $this->getIdFromPermalink($segments[2]);

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// /dashboard/following
		if ($total == 2 && $segments[1] == $this->translate('dashboard_everyone')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'everyone';

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// /dashboard/ID-app
		if (($total == 2 || $total == 3) && $segments[1] == $this->translate('dashboard_filterform')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = $segments[1];

			if ($total == 3) {
				$vars['filterid'] = $this->getIdFromPermalink($segments[2]);
			}

			return $vars;
		}

		// /dashboard/app/ID-app
		// we need to test if this is 'app' filter link or not.
		if ($total == 3 && ($segments[0] == $this->translate('dashboard') || $segments[0] == 'dashboard') && $segments[1] == $this->translate('dashboard_filter_app')) {
			$vars['view'] = 'dashboard';
			$vars['appId'] = $segments[2];

			return $vars;
		}


		if ($total == 3 && $segments[1] == $this->translate('dashboard_layout_hashtag')) {
			$vars['view'] = 'dashboard';
			$vars['tag'] = $segments[2];

			return $vars;
		}

		// /list/ID-list
		if ($total == 3 && $segments[1] == $this->translate('dashboard_list')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'list';
			$vars['listId'] = $segments[2];

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		if($total == 3 && $segments[1] == $this->translate('dashboard_appfilter'))
		{
			$vars['view'] = 'dashboard';
			$vars['type'] = 'appFilter';
			$vars['filterid'] = $segments[2];

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		if ($total == 3 && $segments[1] == $this->translate('dashboard_filter')) {
			$vars['view'] = 'dashboard';
			$vars['type'] = 'filter';
			$vars['filterid'] = $this->getIdFromPermalink($segments[2]);

			if ($doc->getType() != 'html') {
				$vars['filter'] = $vars['type'];
				unset($vars['type']);
			}

			return $vars;
		}

		// If there's no other suitable segments, return an error
		$vars['view'] = 'dashboard';
		$vars['layout'] = 'error';

		return $vars;
	}
}
