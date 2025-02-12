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

class SocialUserAppPhotosHookNotificationComments
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

		// Determine the user avatar to be used
		if (isset($users[0])) {
			$item->userOverride = ES::user($users[0]);
		}

		// Format the content here
		$content = '';
		if (count($users) == 1 && !empty($item->content)) {

			$content = $item->content;
			$content = str_replace(array("<br />","<br>","<br/>"), "", $content);

			$content = ES::string()->processEmoWithTruncate($content);
			$content = str_replace("\n", "", $content);

			$content = ES::string()->processEmoWithTruncate($content);

			// Fallback method
			// Load the comment object since we have the context_ids
			if (!$content) {
				$comment = ES::table('Comments');
				$comment->load($item->context_ids);

				$content = ES::string()->processEmoWithTruncate($comment->comment);
			}
		}

		// When user likes on an album or a group of photos from an album on the stream
		if ($item->context_type == 'albums.user.create') {

			$album  = ES::table('Album');
			$album->load($item->uid);

			$item->content = $content;
			$item->image = $album->getCover();

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_PHOTO_ALBUM', ES::user($item->actor_id)->getName(),  $album->get('title'), ES::user($album->user_id)->getName());


				if ($album->user_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_PHOTO_ALBUM', ES::user($item->actor_id)->getName(), $album->get('title'));
				}

				$item->title = $title;
				return;
			}

			// We need to determine if the user is the owner
			if ($album->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$string = ES::string()->computeNoun('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PHOTO_ALBUM', count($users));
				$item->title = JText::sprintf($string, $names, $album->get('title'));
				return;
			}

			if ($item->actor_id == $album->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PHOTO_ALBUM' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName(), $album->get('title'));

				return;
			}

			// For other users, we just post a generic message
			$string = ES::string()->computeNoun('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PHOTO_ALBUM', count($users));
			$item->title = JText::sprintf($string, $names, ES::user($album->user_id)->getName(), $album->get('title'));

			return;
		}

		// If user uploads multiple photos on the stream
		if ($item->context_type == 'stream.user.upload') {

			//Get the stream item object
			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $item->uid));

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($streamItem->context_id);

			$item->content = $content;

			// We could also set an image preview
			$item->image = $photo->getSource();

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_PHOTO_SHARED_ON_THE_STREAM', ES::user($item->actor_id)->getName(), ES::user($photo->user_id)->getName());


				if ($photo->user_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_PHOTO_SHARED_ON_THE_STREAM', ES::user($item->actor_id)->getName());
				}

				$item->title = $title;
				return;
			}

			// Because we know that this is coming from a stream, we can display a nicer message
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$string = ES::string()->computeNoun('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_YOUR_PHOTO_SHARED_ON_THE_STREAM', count($users));
				$item->title = JText::sprintf($string, $names);

				return;
			}

			if ($item->actor_id == $photo->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_USERS_PHOTO_SHARED_ON_THE_STREAM' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName());

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_USERS_PHOTO_SHARED_ON_THE_STREAM', $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.user.updateCover') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();

			$item->content = $content;

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_PROFILE_COVER', ES::user($item->actor_id)->getName(), ES::user($photo->user_id)->getName());


				if ($photo->user_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_PROFILE_COVER', ES::user($item->actor_id)->getName());
				}

				$item->title = $title;
				return;
			}

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PROFILE_COVER', $names);

				return;
			}

			if ($item->actor_id == $photo->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PROFILE_COVER' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName());

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PROFILE_COVER', $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.user.uploadAvatar') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();

			$item->content = $content;

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_PROFILE_AVATAR', ES::user($item->actor_id)->getName(), ES::user($photo->user_id)->getName());


				if ($photo->user_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_PROFILE_AVATAR', ES::user($item->actor_id)->getName());
				}

				$item->title = $title;
				return;
			}

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PROFILE_PHOTO', $names);

				return;
			}

			if ($item->actor_id == $photo->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PROFILE_PHOTO' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName());

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PROFILE_PHOTO', $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.user.upload' || $item->context_type == 'photos.user.add') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();

			// Set the comment message
			$item->content = $content;

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_PHOTO', ES::user($item->actor_id)->getName(), ES::user($photo->user_id)->getName());


				if ($photo->user_id == $item->target_id) {
					$title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_REPLIED_COMMENT_ON_YOUR_PHOTO', ES::user($item->actor_id)->getName());
				}

				$item->title = $title;
				return;
			}

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_YOUR_PHOTO', $names);

				return;
			}

			if ($item->actor_id == $photo->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PHOTO' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName());

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_COMMENTED_ON_USERS_PHOTO', $names, ES::user($photo->user_id)->getName());

			return;
		}

		return;
	}

}
