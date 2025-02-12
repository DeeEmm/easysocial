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

class FriendsWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Displays user friends on mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($user)
	{
		if (!$this->config->get('friends.enabled')) {
			return;
		}

		// Get the user params
		$params = $this->getUserParams($user->id);

		// Hard limit to 10 friends only
		$limit = 10;

		echo $this->getFriends($user, $params, $limit);
	}

	/**
	 * Display user friends on sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		if (!$this->config->get('friends.enabled')) {
			return;
		}

		// Get the user params
		$params = $this->getUserParams($user->id);

		echo $this->getFriends($user, $params);

		echo $this->getMutualFriends($user, $params);
	}

	/**
	 * Retrieves friends for profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFriends($user, &$params, $limit = null)
	{
		$appParams = $this->app->getParams();

		if (!$params->get('profile_show_friends', $appParams->get('profile_show_friends', true))) {
			return;
		}

		// Determines if the viewer can view user's followers
		if (!$user->isViewer() && !$this->my->canView($user, 'friends.view')) {
			return;
		}

		// Load a list of the user's friends.
		$model = ES::model('Friends');

		if (!$limit) {
			$limit = $params->get('profile_show_limit', $appParams->get('profile_show_limit', 10));
		}

		$options = array('limit' => $limit, 'idonly' => true);

		$ids = $model->getFriends($user->id, $options);

		// If there's nothing here, skip this altogether
		$friends = array();
		$total = 0;

		if ($ids) {
			$friends = ES::user($ids);
		}

		$theme = ES::themes();
		$theme->set('friends', $friends);
		$theme->set('activeUser', $user);

		return $theme->output('themes:/apps/user/friends/widgets/profile/friends');
	}

	/**
	 * Displays a list of mutual friends for the user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMutualFriends($user, &$params)
	{
		$appParams = $this->app->getParams();

		if (!$params->get('profile_show_mutual', $appParams->get('profile_show_mutual', true))) {
			return;
		}

		$my = ES::user();

		// If viewer is viewing his own profile, don't show mutual friends
		if ($my->id == $user->id) {
			return;
		}

		if ($my->id != $user->id) {
			$privacy = $my->getPrivacy();

			if (!$privacy->validate('friends.view', $user->id)) {
				return;
			}
		}

		$model = ES::model('Friends');
		$limit = $params->get('profile_show_limit', $appParams->get('profile_show_limit', 10));

		$friends = $model->getMutualFriends($my->id, $user->id, $limit);

		$theme = ES::themes();
		$theme->set('friends', $friends);
		$theme->set('user', $user);

		return $theme->output('themes:/apps/user/friends/widgets/profile/mutual');
	}
}
