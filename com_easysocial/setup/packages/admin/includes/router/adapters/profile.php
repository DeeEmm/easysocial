<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRouterProfile extends SocialRouterAdapter
{
	/**
	 * Constructs the profile urls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function build(&$menu, &$query)
	{
		$segments = array();
		$addView = false;

		// this is so that in sef, for user links, we will not have the menu item alias.
		// dump($query);

		// If there is a menu but not pointing to the profile view, we need to set a view
		if ($menu && $menu->query['view'] != 'profile') {
			// $segments[]	= $this->translate($query['view']);
			$addView = true;
		}

		// If there's no menu, use the view provided
		if (!$menu) {
			// $segments[] = $this->translate($query['view']);
			$addView = true;
		}

		// Check if the user
		$id = isset($query['id']) ? $query['id'] : null;

		// If user id is provided, use their given alias.
		if (!is_null($id)) {
			$segments[] = ESR::normalizePermalink($query['id']);
			unset($query['id']);

			$addView = false;
		}

		if ($addView) {
			array_unshift($segments, $this->translate($query['view']));
		}

		unset($query['view']);

		$layout = isset($query['layout']) ? $query['layout'] : null;

		if (!is_null($layout)) {
			if ($layout !== 'about' && $layout !== 'timeline') {
				$segments[] = $this->translate('profile_layout_' . $query['layout']);
			}

			$defaultLayout = ES::config()->get('users.profile.display', 'timeline');

			// Special handling for timeline and about

			// Depending settings, if default is set to timeline and layout is timeline, we don't need to add this into the segments

			if ($layout === 'timeline' && $layout !== $defaultLayout) {
				$segments[] = $this->translate('profile_layout_' . $query['layout']);
			}

			if ($layout === 'about' && ($layout !== $defaultLayout || isset($query['step']))) {
				// If layout is about and there is a step provided, then about has to be added regardless of settings
				$segments[] = $this->translate('profile_layout_' . $query['layout']);

				if (isset($query['step'])) {
					$segments[] = $query['step'];
					unset($query['step']);
				}
			}

			unset($query['layout']);
		}

		// Determines if the viewer is trying to view an app from a user.
		$appId = isset($query['appId']) ? $query['appId'] : null;

		if (!is_null($appId)) {
			$segments[]	= ESR::normalizePermalink($appId);
			unset($query['appId']);
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

		// apps
		if ($total > 2 && $segments[2] == $this->translate('apps')) {

			$uid = $segments[1];

			require_once(SOCIAL_LIB . '/router/adapters/apps.php');
			$appsRouter = new SocialRouterApps('apps');

			array_shift($segments); // remove the 'profiles'
			array_shift($segments); // remove the 'id-user-alias'
			array_shift($segments); // remove the 'apps'

			array_unshift($segments, 'apps', 'user', $uid);

			$vars = $appsRouter->parse($segments);
			return $vars;
		}

		// here we need to test if this sef is belong to other views or not.
		if ($total > 2) {
			$testView = $this->getNonProfileViews($segments[2]);

			if ($testView !== false) {
				$view = array_shift($segments); // remove 'profiles';
				$userId = array_shift($segments); // remove id-user-alias;

				$nonProfileView = array_shift($segments); // remove non-profile-view;

				// now we have to add
				array_unshift($segments, $testView, 'user', $userId);

				// Parse the segments
				$router = ES::router($testView);
				$vars = $router->parse($segments);
				return $vars;
			}
		}

		// URL: http://site.com/menu/profile
		if ($total == 1) {
			$vars['view'] = 'profile';
			return $vars;
		}

		// URL: http://site.com/menu/profile/confirmReset
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_confirmreset')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'confirmReset';

			return $vars;
		}

		// URL: http://site.com/menu/profile/downloadFile
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_downloadfile')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'downloadFile';

			return $vars;
		}

		// GDPR download
		// URL: http://site.com/menu/profile/download
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_download')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'download';

			return $vars;
		}


		// URL: http://site.com/menu/profile/confirmReset
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_completereset')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'completeReset';

			return $vars;
		}

		// This rule has to be before the "id" because passing an "id" would also mean viewing the person's profile.
		//
		// URL: http://site.com/menu/profile/edit
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_edit')) {
			$vars['view']		= 'profile';
			$vars['layout']	= 'edit';

			return $vars;
		}

		// URL: http://site.com/menu/profile/user-id/edit
		if ($total == 3 && $segments[2] == $this->translate('profile_layout_edit')) {
			$vars['view'] = 'profile';
			$vars['id'] = $this->getUserId($segments[1]);
			$vars['layout'] = 'edit';

			return $vars;
		}

		// This rule has to be before the "id" because passing an "id" would also mean viewing the person's profile.
		//
		// URL: http://site.com/menu/profile/editprivacy
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_editprivacy')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'editPrivacy';

			return $vars;
		}

		// This rule has to be before the "id" because passing an "id" would also mean viewing the person's profile.
		//
		// URL: http://site.com/menu/profile/editnotifications
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_editnotifications')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'editNotifications';

			return $vars;
		}

		// URL:: /menu/profile/submitVerification
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_submitverification')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'submitVerification';

			return $vars;
		}

		// URL: http://site.com/menu/profile/swithProfile
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_switchprofile')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'switchProfile';

			return $vars;
		}

		// URL: http://site.com/menu/profile/swithProfileEdit
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_switchprofileedit')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'switchProfileEdit';

			return $vars;
		}

		// URL: http://site.com/menu/profile/username/timeline
		// URL: http://site.com/menu/profile/ID-username/timeline
		if($total == 3 && $segments[2] == $this->translate('profile_layout_timeline')) {
			$vars['view'] = 'profile';
			$vars['id'] = $this->getUserId($segments[1]);
			$vars['layout'] = 'timeline';

			return $vars;
		}

		// URL: http://site.com/menu/profile/username/about
		// URL: http://site.com/menu/profile/ID-username/about
		if ($total == 3 && $segments[2] == $this->translate('profile_layout_about')) {
			$vars['view'] = 'profile';
			$vars['id'] = $this->getUserId($segments[1]);
			$vars['layout'] = 'about';

			return $vars;
		}

		// URL: http://site.com/menu/profile/username/about/[step]
		// URL: http://site.com/menu/profile/ID-username/about/[step]
		if ($total == 4 && $segments[2] == $this->translate('profile_layout_about')) {
			$vars['view'] = 'profile';
			$vars['id'] = $this->getUserId($segments[1]);
			$vars['layout'] = 'about';
			$vars['step'] = $segments[3];

			return $vars;
		}

		// URL: http://site.com/menu/profile/forgetpassword
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_forgetpassword')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'forgetPassword';

			return $vars;
		}

		// URL: http://site.com/menu/profile/forgetusername
		if ($total == 2 && $segments[1] == $this->translate('profile_layout_forgetusername')) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'forgetUsername';

			return $vars;
		}

		// This rule has to be before the "id" because passing an "id" would also mean viewing the person's profile.
		//
		// URL: http://site.com/menu/profile/editPrivacy
		if ($total == 2 && ($segments[1] == $this->translate('profile_layout_editprivacy') || str_ireplace(':' , '-' , $segments[1]) == $this->translate('profile_layout_editprivacy'))) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'editPrivacy';

			return $vars;
		}

		// This rule has to be before the "id" because passing an "id" would also mean viewing the person's profile.
		//
		// URL: http://site.com/menu/profile/editNotifications
		if ($total == 2 && ($segments[1] == $this->translate('profile_layout_editnotifications') || str_ireplace(':' , '-' , $segments[1]) == $this->translate('profile_layout_editnotifications'))) {
			$vars['view'] = 'profile';
			$vars['layout'] = 'editNotifications';

			return $vars;
		}

		// URL: http://site.com/menu/profile/username OR http://site.com/menu/profile/ID-name
		if ($total == 2) {
			$vars['view'] = 'profile';
			$vars['id'] = $this->getUserId($segments[1]);

			return $vars;
		}

		// Viewing an app in a profile
		//
		// URL: http://site.com/menu/profile/username/ID-app
		if ($total == 3) {
			$vars['view'] = 'profile';
			$vars['id'] = $this->getUserId($segments[1]);
			$vars['appId'] = $this->getIdFromPermalink($segments[2]);

			return $vars;
		}

		return $vars;
	}

	private function getNonProfileViews($translated)
	{
		$view = false;

		if ($translated == $this->translate('albums') || $translated == 'albums') {
			$view = 'albums';

		} else if ($translated == $this->translate('groups') || $translated == 'groups') {
			$view = 'groups';

		} else if ($translated == $this->translate('events') || $translated == 'events') {
			$view = 'events';

		} else if ($translated == $this->translate('pages') || $translated == 'pages') {
			$view = 'pages';

		} else if ($translated == $this->translate('videos') || $translated == 'videos') {
			$view = 'videos';

		} else if ($translated == $this->translate('friends') || $translated == 'friends') {
			$view = 'friends';

		} else if ($translated == $this->translate('followers') || $translated == 'followers') {
			$view = 'followers';

		} else if ($translated == $this->translate('badges') || $translated == 'badges') {
			$view = 'badges';

		} else if ($translated == $this->translate('points') || $translated == 'points') {
			$view = 'points';

		} else if ($translated == $this->translate('audios') || $translated == 'audios') {
			$view = 'audios';
		} else if ($translated == $this->translate('polls') || $translated == 'polls') {
			$view = 'polls';
		}

		return $view;
	}

}
