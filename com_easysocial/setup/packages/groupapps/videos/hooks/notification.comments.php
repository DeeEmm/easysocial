<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGroupAppVideosHookNotificationComments
{
	/**
	 * Process comment notifications
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function execute(&$item)
	{
		// Get the list of participants for comments
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

		if ($item->context_type == 'videos.group.create') {

			// Load the video table
			$table = ES::table('Video');
			$table->load($item->uid);

			$video = ES::video($table->uid, $table->type, $table);

			// Set the video image
			$item->image = $video->getThumbnail();

			if (count($users) == 1) {
				$item->content = ES::string()->processEmoWithTruncate($item->content);
			}

			$string = ES::string()->computeNoun('APP_USER_VIDEOS_NOTIFICATIONS_COMMENTED_ON_YOUR_VIDEO', count($users));

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_VIDEOS_NOTIFICATIONS_REPLIED_COMMENT_ON_VIDEO', ES::user($item->actor_id)->getName(), $video->getTitle(), ES::user($video->user_id)->getName());


				if ($video->user_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_VIDEOS_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_VIDEO', ES::user($item->actor_id)->getName(), $video->getTitle());
				}

				$item->title = $title;
				return;
			}

			// We need to determine if the user is the owner
			if ($video->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($string, $names, $video->getTitle());

				return;
			}

			if ($item->actor_id == $video->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_VIDEOS_NOTIFICATIONS_COMMENTED_ON_USERS_VIDEO' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName(), $video->getTitle());

				return;
			}

			// For other users, we just post a generic message
			$string = ES::string()->computeNoun('APP_USER_VIDEOS_NOTIFICATIONS_COMMENTED_ON_USERS_VIDEO', count($users));
			$item->title = JText::sprintf($string, $names, ES::user($video->user_id)->getName(), $video->getTitle());

			return;
		}

		return;
	}

}
