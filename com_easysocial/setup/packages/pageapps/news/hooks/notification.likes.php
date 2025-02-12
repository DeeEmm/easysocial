<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppNewsHookNotificationLikes extends SocialAppHooks
{
	public function execute(&$item)
	{
		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = $this->getNames($users);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		// When someone likes on the news that you have created in a page
		if ($item->context_type == 'news.page.create') {

			// We do not want to display any content if the person likes a page announcement
			$item->content = '';

			// Get the news object
			$news = ES::table('ClusterNews');
			$news->load($item->uid);

			// Get the page from the stream
			$page = ES::page($news->cluster_id);

			// Set the content
			if ($page) {
				$item->image = $page->getAvatar();
			}

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($news->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_PAGE_NEWS_USER_LIKES_ANNOUNCEMENT', $users), $names, $page->getName());
				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_PAGE_NEWS_USER_LIKES_USER_ANNOUNCEMENT', $users), $names, ES::user($news->created_by)->getName(), $page->getName());
			return;
		}

		return;
	}

}
