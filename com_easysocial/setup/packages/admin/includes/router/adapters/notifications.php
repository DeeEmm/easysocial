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

/**
 * Component's router for notifications view.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterNotifications extends SocialRouterAdapter
{
	/**
	 * Constructs the notification urls
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'notifications') {
			$segments[] = $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[] = $this->translate('notifications');
		}
		unset($query['view']);


		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[] = $this->translate('notifications_layout_' . $layout);
			unset($query['layout']);
		}

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[]	= $id;
			unset($query['id']);
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
		$total = count($segments);
		$vars = array();

		// URL: http://site.com/menu/notifications
		if ($total == 1 && ($segments[0] == $this->translate('notifications') || $segments[0] == 'notifications')) {
			$vars['view'] = 'notifications';

			return $vars;
		}

		// URL: http://site.com/menu/notifications/route/ID
		if ($total == 3 && $segments[1] == $this->translate('notifications_layout_route')) {
			$vars['view'] = 'notifications';
			$vars['layout'] = 'route';
			$vars['id'] = $segments[2];

			return $vars;
		}

		return $vars;
	}
}
