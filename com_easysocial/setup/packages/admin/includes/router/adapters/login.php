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

/**
 * Component's router for login view.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterLogin extends SocialRouterAdapter
{
	/**
	 * Constructs the login urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments 	= array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'login') {
			$segments[]	= $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[]	= $this->translate($query['view']);
		}

		unset($query['view']);

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

		if ($total == 1 && ($segments[0] == $this->translate('login') || $segments[0] == 'login')) {
			$vars[ 'view' ]	= 'login';
			return $vars;
		}

		// For unknown views / layouts, display an error
		$vars['view'] = 'login';
		$vars['layout'] = 'error';

		return $vars;
	}
}
