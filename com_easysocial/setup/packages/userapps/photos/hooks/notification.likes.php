<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialUserAppPhotosHookNotificationLikes extends SocialAppHooks
{
	public function execute(&$item)
	{
		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = ES::string()->namesToNotifications($users);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);

		$item->userOverride = ES::user($users[0]);

		// When user likes on an album or a group of photos from an album on the stream
		if ($item->context_type == 'albums.user.create') {
			$album = ES::table('Album');
			$album->load($item->uid);

			$item->image = $album->getCover();

			// We need to determine if the user is the owner
			if ($album->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_YOUR_ALBUMS', $users), $names, $album->get('title'));
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $album->user_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_ALBUMS', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_ALBUMS', $users), $names, ES::user($album->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.user.updateCover') {

			// Get the photo object
			$photo  = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();
			$item->content = '';

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PROFILE_COVER', $users), $names);

				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $photo->user_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_COVER', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_COVER', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.user.uploadAvatar') {
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();
			$item->content  = '';

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PROFILE_PHOTO', $users), $names);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $photo->user_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_PHOTO', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_PHOTO', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		// If user uploads multiple photos on the stream
		if ($item->context_type == 'stream.user.upload') {

			$photo  = ES::table('Photo');
			$photo->load($item->context_ids);

			$item->content  = '';

			// We could also set an image preview
			$item->image = $photo->getSource();

			// Because we know that this is coming from a stream, we can display a nicer message
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PHOTO_SHARED_ON_THE_STREAM', $users), $names);
				return;
			}

			if ($item->actor_id == $photo->user_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO_SHARED_ON_THE_STREAM', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO_SHARED_ON_THE_STREAM', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		// When user likes on a single photo item
		if ($item->context_type == 'photos.user.upload' || $item->context_type == 'photos.user.add') {
			$photo  = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();
			$item->content = '';

			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PHOTO', $users), $names);

				// dump($item->id, $item->title, $names);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $photo->user_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO', $item->actor_id), ES::user($item->actor_id)->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO', $users), $names, ES::user($photo->user_id)->getName());
			return;
		}

		return;
	}

}
