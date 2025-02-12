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

class SocialGroupAppPhotosHookNotificationLikes extends SocialAppHooks
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

		// When user likes on an album or a group of photos from an album on the stream
		if ($item->context_type == 'albums.group.create') {
			$album = ES::table('Album');
			$album->load($item->uid);

			$item->image = $album->getCover();

			// We need to determine if the user is the owner
			if ($album->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_YOUR_ALBUMS', $users), $names, $album->get('title'));

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_USERS_ALBUMS', $users), $names, ES::user($album->user_id)->getName());
			return;
		}

		$photo = ES::table('Photo');
		$photo->load($item->uid);

		// Set the photo image
		$item->image = $photo->getSource();
		$item->content = '';

		if ($item->context_type == 'photos.group.updateCover') {
			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PROFILE_COVER', $users), $names);
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_COVER', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.group.uploadAvatar') {
			// We need to determine if the user is the owner
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_YOUR_GROUP_PICTURE', $users), $names);
				return;
			}

			return;
		}

		// If user uploads multiple photos on the stream
		if ($item->context_type == 'stream.group.upload') {
			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->context_ids);

			$item->content = '';
			$item->image = $photo->getSource();

			// Because we know that this is coming from a stream, we can display a nicer message
			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PHOTO_SHARED_ON_THE_STREAM', $users), $names);

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO_SHARED_ON_THE_STREAM', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		// When user likes on a single photo item
		if ($item->context_type == 'photos.group.upload' || $item->context_type == 'photos.group.add') {

			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PHOTO', $users), $names);
				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO', $users), $names, ES::user($photo->user_id)->getName());
			return;
		}


		return;
	}

}
