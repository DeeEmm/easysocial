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

class GroupsWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Display online users from the group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function sidebarBottom($groupId)
	{
		// Get the group
		$group = ES::group($groupId);
		$params = $this->app->getParams();

		// Determines if we should display the online group members
		if ($params->get('show_online')) {
			echo $this->getOnlineUsers($group);
		}

		// Determines if we should display friends in this group
		if ($params->get('show_friends') && $this->config->get('friends.enabled')) {
			echo $this->getFriends($group);
		}
	}

	/**
	 * Displays a list of friends in the group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFriends($group)
	{
		$options = array(
			'userId' => $this->my->id,
			'randomize' => true,
			'limit' => 5,
			'published' => true
		);

		// Get a list of friends in this group based on the current viewer.
		$model = ES::model('Groups');
		$friends = $model->getFriendsInGroup($group->id, $options);

		if (!$friends) {
			return;
		}

		$theme = ES::themes();
		$theme->set('friends', $friends);

		return $theme->output('themes:/apps/group/groups/widgets/friends');
	}

	/**
	 * Displays a list of online group members
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getOnlineUsers($group)
	{
		$model = ES::model('Groups');
		$users = $model->getOnlineMembers($group->id);

		$theme = ES::themes();
		$theme->set('users', $users);

		return $theme->output('themes:/apps/group/groups/widgets/online');
	}
}
