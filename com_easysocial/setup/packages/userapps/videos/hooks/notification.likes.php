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

class SocialUserAppVideosHookNotificationLikes extends SocialAppHooks
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

		// When user likes on a single video item
		if ($item->context_type == 'videos.user.create' || $item->context_type == 'videos.user.featured') {

			// Get the video object
			$table = ES::table('Video');
			$table->load($item->uid);

			// Load up the video
			$video = ES::video($table->uid, $table->type, $table);

			// Set the video image
			$item->image = $video->getThumbnail();

			// We need to determine if the user is the owner
			if ($video->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_VIDEOS_NOTIFICATIONS_LIKES_YOUR_VIDEO', $users), $names, $video->title);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $video->user_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_VIDEOS_NOTIFICATIONS_LIKES_USERS_VIDEO', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			if ($item->cmd == 'likes.involved') {
				$item->title = JText::sprintf($this->getPlurality('COM_EASYSOCIAL_LIKES_INVOLVED_VIDEO_SYSTEM_TITLE', $users), $names);
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_VIDEOS_NOTIFICATIONS_LIKES_USERS_VIDEO', $users), $names, ES::user($video->user_id)->getName());

			return;
		}

		return;
	}
}
