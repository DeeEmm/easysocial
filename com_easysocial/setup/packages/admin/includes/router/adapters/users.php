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

class SocialRouterUsers extends SocialRouterAdapter
{
	/**
	 * Constructs users urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments 	= array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'users') {
			$segments[]	= $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}
		unset($query['view']);

		$filter = isset($query['filter']) ? $query['filter'] : null;
		$menuFilter = ($menu && $menu->query['view'] == 'users' && isset($menu->query['filter'])) ? $menu->query['filter'] : null;
		$addFilter = false;

		if (is_null($menuFilter)) {
			if (!is_null($filter)) {
				$addFilter = true;
			}
		} else {
			if (!is_null($filter) && $filter != $menuFilter) {
				$addFilter = true;
			}
		}

		if ($addFilter) {
			// dump($query);
			$segments[]	= $this->translate('users_filter_' . $query['filter']);
		}
		unset($query['filter']);

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[]	= $id;
			unset($query['id']);
		}

		$sort = isset($query['sort']) ? $query['sort'] : null;

		if (!is_null($sort)) {
			$segments[]	= $this->translate('users_sort_' . $query['sort']);
			unset($query['sort']);
		}

		return $segments;
	}

	/**
	 * Translates the SEF url to the appropriate url
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$total = count($segments);

		// var_dump('users', $segments);

		// URL: http://site.com/menu/users/online
		if (($total == 2 || $total == 3) && $segments[1] == $this->translate('users_filter_online')) {
			$vars['view'] = 'users';
			$vars['filter'] = 'online';

			if (isset($segments[2])) {

				if ($segments[2] == $this->translate('users_sort_alphabetical')) {
					$vars['sort'] = 'alphabetical';
				}

				if ($segments[2] == $this->translate('users_sort_lastlogin')) {
					$vars['sort'] = 'lastlogin';
				}

				if ($segments[2] == $this->translate('users_sort_latest')) {
					$vars['sort'] = 'latest';
				}
			}

			return $vars;
		}

		if (($total == 2 || $total == 3) && $segments[1] == $this->translate('users_filter_photos')) {
			$vars['view'] = 'users';
			$vars['filter'] = 'photos';

			if (isset($segments[2])) {

				if ($segments[2] == $this->translate('users_sort_alphabetical')) {
					$vars['sort'] = 'alphabetical';
				}

				if ($segments[2] == $this->translate('users_sort_lastlogin')) {
					$vars['sort'] = 'lastlogin';
				}

				if ($segments[2] == $this->translate('users_sort_latest')) {
					$vars['sort'] = 'latest';
				}

			}
			return $vars;
		}

		if (($total == 2 || $total == 3) && $segments[1] == $this->translate('users_filter_blocked')) {
			$vars['view'] = 'users';
			$vars['filter'] = 'blocked';

			if (isset($segments[2])) {

				if ($segments[2] == $this->translate('users_sort_alphabetical')) {
					$vars['sort'] = 'alphabetical';
				}

				if ($segments[2] == $this->translate('users_sort_lastlogin')) {
					$vars['sort'] = 'lastlogin';
				}

				if ($segments[2] == $this->translate('users_sort_latest')) {
					$vars['sort'] = 'latest';
				}

			}
			return $vars;
		}

		if (isset($segments[1]) && $segments[1] == $this->translate('users_filter_profiletype')) {
			$vars['view'] = 'users';
			$vars['filter'] = 'profiletype';
			$vars['id'] = $this->getIdFromPermalink($segments[2]);
			return $vars;
		}

		if (isset($segments[1]) && $segments[1] == $this->translate('users_filter_search')) {
			$vars['view'] = 'users';
			$vars['filter'] = 'search';
			$vars['id'] = $this->getIdFromPermalink($segments[2]);
			return $vars;
		}

		// URL: http://site.com/menu/users/alphabetical or http://site.com/menu/users/latest
		if ($total == 2 && ($segments[1] == $this->translate('users_sort_alphabetical') || $segments[1] == $this->translate('users_sort_latest') || $segments[1] == $this->translate('users_sort_lastlogin'))) {
			$vars['view'] = 'users';
			$vars['sort'] = 'latest';

			if ($segments[1] == $this->translate('users_sort_alphabetical')) {
				$vars['sort'] = 'alphabetical';
			}

			if ($segments[1] == $this->translate('users_sort_lastlogin')) {
				$vars['sort'] = 'lastlogin';
			}

			return $vars;
		}

		// URL: http://site.com/menu/users
		if ($total <= 3 && ($segments[0] == $this->translate('users') || $segments[1] == $this->translate('users_filter_all'))) {

			$vars['view'] = 'users';

			if (isset($segments[1])) {

				// Default to all
				$vars['filter'] = 'all';

				if ($segments[1] == $this->translate('users_filter_online')) {
					$vars['filter'] = 'online';
				}

				if ($segments[1] == $this->translate('users_filter_photos')) {
					$vars['filter'] = 'photos';
				}

				if ($segments[1] == $this->translate('users_filter_blocked')) {
					$vars['filter'] = 'blocked';
				}

				if ($segments[1] == $this->translate('users_filter_verified')) {
					$vars['filter'] = 'verified';
				}

				if ($segments[1] == $this->translate('users_filter_friends')) {
					$vars['filter'] = 'friends';
				}

				if ($segments[1] == $this->translate('users_filter_followers')) {
					$vars['filter'] = 'followers';
				}
			}

			// dump($segments);
			if (isset($segments[2])) {
				if ($segments[2] == $this->translate('users_sort_alphabetical')) {
					$vars['sort'] = 'alphabetical';
				} else if ($segments[2] == $this->translate('users_sort_lastlogin')) {
					$vars['sort'] = 'lastlogin';
				} else {
					$vars['sort'] = 'latest';
				}
			}

			return $vars;
		}

		return $vars;
	}
}
