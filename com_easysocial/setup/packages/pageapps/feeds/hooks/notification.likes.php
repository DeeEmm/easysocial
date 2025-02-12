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

class SocialPageAppFeedsHookNotificationLikes extends SocialAppHooks
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

		// Load the stream object
		$stream = ES::table('Stream');
		$stream->load($item->context_ids);

		// Load the page
		$page = ES::page($stream->cluster_id);

		// We need to determine if the user is the owner
		if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf($this->getPlurality('APP_PAGE_FEEDS_NOTIFICATIONS_USER_LIKES_FEED', $users), $names, $page->getName());
			return;
		}

		// If the owner likes her/his own
		if ($item->actor_id == $stream->actor_id && count($users) == 1) {
			$item->title = JText::sprintf('APP_PAGE_FEEDS_NOTIFICATIONS_USER_LIKES_USERS_FEED', $page->getName());
			return;
		}

		$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_FEEDS_NOTIFICATIONS_USER_LIKES_USERS_FEED', $users), $names, $page->getName());

		return;
	}
}
