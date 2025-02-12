<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppDiscussionsHookNotificationLikes extends SocialAppHooks
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

		if ($item->context_ids) {
			$discussion = ES::table('Discussion');
			$discussion->load($item->context_ids);

			$item->content = ESJString::substr(strip_tags($discussion->content), 0, 30) . JText::_('COM_EASYSOCIAL_ELLIPSES');
		}

		// When someone likes on the photo that you have uploaded in a page
		if ($item->context_type == 'discussions.page.create') {

			$discussion = ES::table('Discussion');
			$discussion->load($item->uid);

			$page = ES::page($discussion->uid);
			$item->content = $discussion->title;

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($discussion->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_PAGE_DISCUSSIONS_USER_LIKES_DISCUSSION', $users), $names, $page->getName());
				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_PAGE_DISCUSSIONS_USER_LIKES_USERS_DISCUSSION', $users), $names, $page->getName());

			return;
		}

		return;
	}
}
