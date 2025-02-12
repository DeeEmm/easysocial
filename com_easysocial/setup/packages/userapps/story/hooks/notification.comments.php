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

class SocialUserAppStoryHookNotificationComments
{
	/**
	 * Processes likes notifications
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function execute(&$item)
	{
		 // Get comment participants
		$model = ES::model('Comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// Include the actor of the stream item as the recipient
		// place the actor in the first place of array // #4554
		array_unshift($users, $item->actor_id);

		// Ensure that the values are unique
		$users = array_unique($users);
		$users = array_values($users);

		// Exclude myself from the list of users.
		$index = array_search(ES::user()->id, $users);

		// If the skipExcludeUser is true, we don't unset myself from the list
		if (isset($item->skipExcludeUser) && $item->skipExcludeUser) {
			$index = false;
		}

		if ($index !== false) {
			unset($users[$index]);
			$users = array_values($users);
		}

		// exclude the target name in the notification title. #4459
		if (isset($item->skipTargetUserNameInTitle) && $item->skipTargetUserNameInTitle && $item->target_type == 'user' && $item->target_id) {
			$index = array_search($item->target_id, $users);

			if ($index !== false) {
				unset($users[$index]);
				$users = array_values($users);
			}
		}

		// Convert the names to stream-ish
		$names = ES::string()->namesToNotifications($users);

		// If content doesnt exists, we get the content from the comment directly.
		if (!isset($item->content) || !$item->content) {
			$comment = ES::table('Comments');
			$comment->load($item->context_ids);

			$item->content = $comment->comment;
		}

		// When user likes on an album or a group of photos from an album on the stream
		if ($item->context_type == 'story.user.create') {

			$stream  = ES::table('Stream');
			$stream->load($item->uid);

			if ($users && count($users) > 1) {
				$item->content = '';
			}

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_REPLIED_COMMENT_ON_UPDATE', ES::user($item->actor_id)->getName(), ES::user($stream->actor_id)->getName());

				if ($stream->actor_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_UPDATE', ES::user($item->actor_id)->getName());
				}

				$item->title = $title;
				return;
			}

			// We need to determine if the user is the owner
			if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$langString = ES::string()->computeNoun('APP_USER_STORY_NOTIFICATIONS_USER_POSTED_COMMENT_ON_YOUR_STATUS_UPDATE', count($users));
				$item->title = JText::sprintf($langString, $names);

				return;
			}

			if ($item->actor_id == $stream->actor_id && count($users) == 1) {

				$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_POSTED_COMMENT_ON_USERS_UPDATE' . ES::user($item->actor_id)->getGenderLang(), ES::user($stream->actor_id)->getName());

				return;
			}

			// For other users, we just post a generic message
			$langString = ES::string()->computeNoun('APP_USER_STORY_NOTIFICATIONS_USER_POSTED_COMMENT_ON_YOUR_USERS_UPDATE', count($users));
			$item->title = JText::sprintf($langString, $names, ES::user($stream->actor_id)->getName());

			return;
		}

		return;
	}

}
