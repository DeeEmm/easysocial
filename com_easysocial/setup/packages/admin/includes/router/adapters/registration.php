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

class SocialRouterRegistration extends SocialRouterAdapter
{
	/**
	 * Constructs the registration urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu , &$query)
	{
		$segments = array();

		// If there is a menu but not pointing to the profile view, we need to set a view
		$disallowedTranslations = array('oauthDialog');
		$translate = true;

		if (isset($query['layout']) && in_array($query['layout'], $disallowedTranslations)) {
			$translate  = false;
		}

		// $translate  = true;

		if ($menu && $menu->query['view'] != 'registration') {
			$segments[] = $translate ? $this->translate($query['view']) : $query['view'];
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			$segments[] = $translate ? $this->translate($query['view']) : $query['view'];
		}
		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			$segments[] = $translate ? $this->translate('registration_layout_' . $layout) : $layout;
			unset($query['layout']);
		}

		$step = isset($query['step']) ? $query['step'] : null;

		if (!is_null($step)) {
			$segments[]	= $step;
			unset($query['step']);
		}

		$id = isset($query['id']) ? $query['id'] : null;

		if (!is_null($id)) {
			$segments[]	= $id;
			unset($query['id']);
		}

		// Only translate the client when it's not a controller link
		if(!isset($query['controller'])) {
			$client = isset($query['client']) ? $query['client'] : null;

			if (!is_null($client)) {
				$segments[]	= $client;
				unset($query['client']);
			}
		}

		// if the current menu has the same profile type, remove the profile_id in the query #5317
		if ($menu && $menu->query['view'] == 'registration' && isset($menu->query['profile_id']) && isset($query['profile_id']) && $menu->query['profile_id'] == $query['profile_id']) {
			unset($query['profile_id']);
		}

		// clean up profile_id which is having 0 value. #778
		if (isset($query['profile_id']) && ($query['profile_id'] == '0' || $query['profile_id'] == 'browse')) {
			unset($query['profile_id']);
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

		// URL: http://site.com/menu/registration
		if ($total == 1) {
			$vars['view'] = 'registration';

			return $vars;
		}

		// URL: http://site.com/menu/registration/activation
		if ($total == 2 && $segments[1] == $this->translate('registration_layout_activation')) {
			$vars['view'] = 'registration';
			$vars['layout']	= 'activation';

			return $vars;
		}

		// URL: http://site.com/menu/registration/steps/1
		if ($total == 3 && $segments[1] == $this->translate('registration_layout_steps')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'steps';
			$vars['step'] = $segments[2];

			return $vars;
		}

		// URL: http://site.com/menu/registration/selectProfile/facebook
		if ($total == 3 && $segments[1] == $this->translate('registration_layout_oauthselectprofile')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'oauthSelectProfile';
			$vars['client'] = $segments[2];

			return $vars;
		}


		// URL: http://site.com/menu/registration/oauthRequestToken/facebook
		if ($total == 3 && $segments[1] == $this->translate('registration_layout_oauthrequesttoken')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'oauthRequestToken';
			$vars['client'] = $segments[2];

			return $vars;
		}

		if ($total == 3 && $segments[1] == $this->translate('registration_layout_steps')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'steps';
			$vars['step'] = $segments[2];

			return $vars;
		}

		// URL: http://site.com/menu/registration/completed/1
		if ($total == 3 && $segments[1] == $this->translate('registration_layout_completed')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'completed';
			$vars['id'] = $segments[2];

			return $vars;
		}

		if ($total == 3 && ($segments[1] == $this->translate('registration_layout_oauthlogin') || $segments[1] == 'oauthLogin')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'oauthLogin';
			$vars['client'] = $segments[2];

			return $vars;
		}

		// URL: http://site.com/menu/registration/completed/1
		if ($total == 3 && ($segments[1] == $this->translate('registration_layout_oauthdialog') || $segments[1] == 'oauthDialog')) {
			$vars['view'] = 'registration';
			$vars['layout']	= 'oauthDialog';
			$vars['client']	= $segments[2];

			return $vars;
		}

		// URL: http://site.com/menu/registration/completed/1
		if ($total == 3 && $segments[1] == $this->translate('registration_layout_oauth')) {
			$vars['view'] = 'registration';
			$vars['layout'] = 'oauth';
			$vars['client'] = $segments[2];
			
			return $vars;
		}

		return $vars;
	}
}
