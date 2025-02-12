<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialUserAppStoryHookNotificationLikes extends SocialAppHooks
{
	public function execute(&$item)
	{
		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);
		$names = $this->getNames($users);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		// When user likes on a comment item
		if ($item->context_type == 'comments.user.like') {
			$comment = ES::table('Comments');
			$comment->load($item->uid);

			// We need to determine if the user is the owner
			if ($comment->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_STORY_NOTIFICATIONS_USER_LIKES_YOUR_COMMENT_ITEM', $users), $names);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $comment->created_by && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_STORY_NOTIFICATIONS_USER_LIKES_USERS_COMMENT_ITEM', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_STORY_NOTIFICATIONS_USER_LIKES_USERS_COMMENT_ITEM', $users), $names, ES::user($comment->created_by)->getName());
			return;
		}

		// When user likes on a story
		if ($item->context_type == 'story.user.create') {
			$stream = ES::table('Stream');
			$stream->load($item->uid);

			// We need to determine if the user is the owner
			if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_STORY_NOTIFICATIONS_USER_LIKES_YOUR_STATUS_UPDATE', $users), $names);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $stream->actor_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_STORY_NOTIFICATIONS_USER_LIKES_STATUS_UPDATE', $item->actor_id), ES::user($stream->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_STORY_NOTIFICATIONS_USER_LIKES_USERS_STATUS_UPDATE', $users), $names, ES::user($stream->actor_id)->getName());

			return;
		}

		return;
	}
}
