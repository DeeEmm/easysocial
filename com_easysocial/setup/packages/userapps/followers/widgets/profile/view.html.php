<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class FollowersWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Display user photos on the side bar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		// Get application params
		$appParams = $this->getParams();

		// Get the user params
		$params = $this->getUserParams($user->id);

		if ($appParams->get('widget_followers', true)) {
			echo $this->getFollowers($user, $params);
		}

		if ($appParams->get('widget_following', true)) {
			echo $this->getFollowing($user, $params);
		}
	}

	/**
	 * Display a list of followers for the user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFollowers($user, &$params)
	{
		$appParams = $this->app->getParams();

		if (!$params->get('show_profile_followers', $appParams->get('show_profile_followers', true))) {
			return;
		}

		// Determines if the viewer can view user's followers
		if (!$user->isViewer() && !$this->my->canView($user, 'followers.view')) {
			return;
		}

		$limit = (int) $params->get('limit', $appParams->get('follower_widget_profile_total', 20));
		$options = array('limit' => $limit);

		$model = ES::model('Followers');
		$users = $model->getFollowers($user->id, $options);

		if (!$users) {
			return;
		}

		$theme = ES::themes();
		$theme->set('activeUser', $user);
		$theme->set('users', $users);
		$theme->set('limit', $limit);

		return $theme->output('themes:/apps/user/followers/widgets/profile/followers');
	}

	/**
	 * Display a list of users this user is following
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFollowing($user, &$params)
	{
		$appParams = $this->app->getParams();

		if (!$params->get('show_profile_following', $appParams->get('show_profile_following', true))) {
			return;
		}

		// Determines if the viewer can view user's followers
		if (!$user->isViewer() && !$this->my->canView($user, 'followers.view')) {
			return;
		}

		$limit = $params->get('limit', $appParams->get('following_widget_profile_total', 20));
		$options = array('limit' => $limit);

		$model = ES::model('Followers');
		$users = $model->getFollowing($user->id, $options);

		if (!$users) {
			return;
		}

		$theme = ES::themes();

		$theme->set('activeUser', $user);
		$theme->set('users', $users);

		return $theme->output('themes:/apps/user/followers/widgets/profile/following');
	}


}
