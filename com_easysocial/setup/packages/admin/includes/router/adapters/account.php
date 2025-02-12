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
 * Component's router for profile view.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialRouterAccount extends SocialRouterAdapter
{
	/**
	 * Constructs the profile urls
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
		if ($menu && $menu->query['view'] != 'account') {
			$segments[] = $this->translate($query['view']);
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[] = $this->translate($query['view']);
		}
		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[] = $this->translate('account_layout_' . $query['layout']);
			unset($query['layout']);
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

		$vars['view'] = 'account';

		// URL: http://site.com/menu/account/confirmReset
		if ($total == 2 && $segments[1] == $this->translate('account_layout_confirmreset')) {
			$vars['layout'] = 'confirmReset';

			return $vars;
		}

		// URL: http://site.com/menu/account/confirmReset
		if ($total == 2 && $segments[1] == $this->translate('account_layout_completereset')) {
			$vars['layout'] = 'completeReset';

			return $vars;
		}

		// URL: http://site.com/menu/account/forgetpassword
		if ($total == 2 && $segments[1] == $this->translate('account_layout_forgetpassword')) {
			$vars['layout'] = 'forgetPassword';

			return $vars;
		}

		// URL: http://site.com/menu/account/forgetusername
		if ($total == 2 && $segments[1] == $this->translate('account_layout_forgetusername')) {
			$vars['layout'] = 'forgetUsername';

			return $vars;
		}

		// URL: http://site.com/menu/account/requirePasswordReset
		if ($total == 2 && $segments[1] == $this->translate('account_layout_requirePasswordReset')) {
			$vars['layout'] = 'requirePasswordReset';

			return $vars;
		}

		return $vars;
	}
}
