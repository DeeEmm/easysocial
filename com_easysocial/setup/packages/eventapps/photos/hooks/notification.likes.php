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

class SocialEventAppPhotosHookNotificationLikes extends SocialAppHooks
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

		if ($item->context_type == 'albums.event.create') {

			$album = ES::table('Album');
			$album->load($item->uid);

			$item->image = $album->getCover();

			// We need to determine if the user is the owner
			if ($album->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_YOUR_ALBUMS', $users), $names, $album->get('title'));

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_USERS_ALBUMS', $users), $names, ES::user($album->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.event.updateCover') {

			// Get the photo object
			$photo = ES::table('Photo');
			$photo->load($item->uid);

			// Set the photo image
			$item->image = $photo->getSource();
			$item->content = '';

			$event = ES::event($photo->uid);

			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$langString = $this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PROFILE_COVER', $users);

				if (count($users) == 1) {
					$item->title = JText::sprintf($langString, $names, $event->getName());
				} else {
					$item->title = JText::sprintf($langString, $names);
				}

				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_COVER', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		if ($item->context_type == 'photos.event.uploadAvatar') {

			$photo = ES::table('Photo');
			$photo->load($item->uid);

			$item->image = $photo->getSource();
			$item->content = '';

			$event = ES::event($photo->uid);

			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$langString = $this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PROFILE_PHOTO', $users);

				if (count($users) == 1) {
					$item->title = JText::sprintf($langString, $names, $event->getName());
				} else {
					$item->title = JText::sprintf($langString, $names);
				}

				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_USERS_PROFILE_PHOTO', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		// If user uploads multiple photos on the stream
		if ($item->context_type == 'stream.event.upload') {
			$photo = ES::table('Photo');
			$photo->load($item->context_ids);

			$item->content = '';

			$event = ES::event($photo->uid);
			$item->image = $photo->getSource();

			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$langString = $this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PHOTO_SHARED_ON_THE_STREAM', $users);

				if (count($users) == 1) {
					$item->title = JText::sprintf($langString, $names, $event->getName());
				} else {
					$item->title = JText::sprintf($langString, $names, $event->getName());
				}
				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO_SHARED_ON_THE_STREAM', $users), $names, ES::user($photo->user_id)->getName(), $event->getName());

			return;
		}

		// When user likes on a single photo item
		if ($item->context_type == 'photos.event.upload' || $item->context_type == 'photos.event.add') {

			$photo = ES::table('Photo');
			$photo->load($item->uid);

			$item->image = $photo->getSource();
			$item->content = '';

			if ($photo->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_YOUR_PHOTO', $users), $names);

				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_PHOTOS_NOTIFICATIONS_LIKES_USERS_PHOTO', $users), $names, ES::user($photo->user_id)->getName());

			return;
		}

		return;
	}
}
