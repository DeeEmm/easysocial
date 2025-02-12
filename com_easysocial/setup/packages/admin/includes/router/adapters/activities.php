<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

/**
 * Component's router for activities view.
 *
 * @since   1.0
 * @author  Mark Lee <mark@stackideas.com>
 */
class SocialRouterActivities extends SocialRouterAdapter
{
	/**
	 * Construct's the actvities url
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function build(&$menu, &$query)
	{
		$segments = array();

		// If there is no active menu for friends, we need to add the view.
		if ($menu && $menu->query['view'] != 'activities') {
			$segments[] = $this->translate($query['view']);
		}

		if (!$menu) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);


		// Filters
		$type = isset($query['type']) ? $query['type'] : '';

		if ($type) {
			$segments[] = $this->translate('activities_type_' . $query['type']);
			unset($query['type']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since   1.0
	 * @access  public
	 * @param   array   An array of url segments
	 * @return  array   The query string data
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		$vars['view'] = 'activities';

		// URL: http://site.com/menu/activities
		if ($total == 1 && $segments[0] == $this->translate('activities')) {
			return $vars;
		}

		// URL: http://site.com/menu/activities/hidden
		if ($total == 2 && $segments[1] == $this->translate('activities_type_hidden')) {
			$vars['type'] = 'hidden';
			return $vars;
		}

		// URL: http://site.com/menu/activities/hiddenapp
		if ($total == 2 && $segments[1] == $this->translate('activities_type_hiddenapp')) {
			$vars['type'] = 'hiddenapp';
			return $vars;
		}

		// URL: http://site.com/menu/activities/hiddenactor
		if ($total == 2 && $segments[1] == $this->translate('activities_type_hiddenactor')) {
			$vars['type'] = 'hiddenactor';
			return $vars;
		}

		// URL: http://site.com/menu/activities/friends
		if ($total == 2 && $segments[1] == $this->translate('activities_type_friends')) {
			$vars['type'] = 'friends';
			return $vars;
		}

		// URL: http://site.com/menu/activities/followers
		if ($total == 2 && $segments[1] == $this->translate('activities_type_followers')) {
			$vars['type'] = 'followers';
			return $vars;
		}

		// URL: http://site.com/menu/activities/photos
		if ($total == 2 && $segments[1] == $this->translate('activities_type_photos')) {
			$vars['type'] = 'photos';
			return $vars;
		}

		// URL: http://site.com/menu/activities/shares
		if ($total == 2 && $segments[1] == $this->translate('activities_type_shares')) {
			$vars['type'] = 'shares';
			return $vars;
		}

		// URL: http://site.com/menu/activities/updates
		if ($total == 2 && $segments[1] == $this->translate('activities_type_story')) {
			$vars['type'] = 'story';
			return $vars;
		}

		// URL: http://site.com/menu/activities/users
		if ($total == 2 && $segments[1] == $this->translate('activities_type_users')) {
			$vars['type'] = 'users';
			return $vars;
		}

		// URL: http://site.com/menu/activities/groups
		if ($total == 2 && $segments[1] == $this->translate('activities_type_groups')) {
			$vars['type'] = 'groups';
			return $vars;
		}

		// URL: http://site.com/menu/activities/links
		if ($total == 2 && $segments[1] == $this->translate('activities_type_links')) {
			$vars['type'] = 'links';
			return $vars;
		}

		// URL: http://site.com/menu/activities/profiles
		if ($total == 2 && $segments[1] == $this->translate('activities_type_profiles')) {
			$vars['type'] = 'profiles';
			return $vars;
		}

		// URL: http://site.com/menu/activities/events
		if ($total == 2 && $segments[1] == $this->translate('activities_type_events')) {
			$vars['type'] = 'events';
			return $vars;
		}

		// URL: http://site.com/menu/activities/badges
		if ($total == 2 && $segments[1] == $this->translate('activities_type_badges')) {
			$vars['type'] = 'badges';
			return $vars;
		}

		// URL: http://site.com/menu/activities/apps
		if ($total == 2 && $segments[1] == $this->translate('activities_type_apps')) {
			$vars['type'] = 'apps';
			return $vars;
		}

		// URL: http://site.com/menu/activities/tasks
		if ($total == 2 && $segments[1] == $this->translate('activities_type_tasks')) {
			$vars['type'] = 'tasks';
			return $vars;
		}

		return $vars;
	}
}
