<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PagesWidgetsPages extends SocialAppsWidgets
{
	/**
	 * Display user photos on the side bar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function sidebarBottom($pageId, SocialPage $page)
	{
		// Get the page
		$page = ES::page($pageId);

		$params = $this->app->getParams();

		// Determines if we should display the online page followers
		if ($params->get('show_online')) {
			echo $this->getOnlineUsers($page);
		}

		// Determines if we should display friends in this page
		if ($params->get('show_friends') && $this->config->get('friends.enabled')) {
			echo $this->getFriends($page);
		}
	}

	/**
	 * Displays a list of friends in the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFriends(SocialPage $page)
	{
		$theme = ES::themes();

		// Get the current logged in user.
		$my = ES::user();

		$options = array();
		$options['userId'] = $my->id;
		$options['randomize'] = true;
		$options['limit'] = 5;
		$options['published'] = true;

		// Get a list of friends in this page based on the current viewer.
		$model = ES::model('Pages');
		$friends = $model->getFriendsInPage($page->id, $options);
		$total = $model->getTotalFriendsInPage($page->id, $options);

		if (!$friends) {
			return;
		}

		$theme->set('total', $total);
		$theme->set('friends', $friends);

		return $theme->output('themes:/apps/page/pages/widgets/friends');
	}

	/**
	 * Displays a list of online page followers
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getOnlineUsers(SocialPage $page)
	{
		$model = ES::model('Pages');
		$users = $model->getOnlineFollowers($page->id);
		$total = count($users);

		$theme = ES::themes();
		$theme->set('total', $total);
		$theme->set('users', $users);

		return $theme->output('themes:/apps/page/pages/widgets/online');
	}
}
