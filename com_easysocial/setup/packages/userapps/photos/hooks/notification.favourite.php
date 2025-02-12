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

class SocialUserAppPhotosHookNotificationFavourite
{
	/**
	 * Process likes notificaions
	 *
	 * @since	1.2.0
	 * @access	public
	 */
	public function execute(&$item)
	{
		if ($item->context_type == 'albums.user.favourite') {

			$model = ES::model('Albums');
			$users = $model->getFavouriteParticipants($item->uid);

			$users = array_unique($users);
			$users = array_values($users);

			// Exclude myself from the list of users.
			$index = array_search(ES::user()->id , $users);

			$album = ES::table('Album');
			$album->load($item->context_ids);

			$item->image = $album->getCover();

			// TODO: Get participants from favourite tables.

			// Convert the names to stream-ish
			$names = ES::string()->namesToNotifications($users);

			if ($item->target_id == $album->user_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_FAVOURITED_ON_YOUR_PHOTO_ALBUM', $names);

				return;
			}

			if ($item->actor_id == $album->user_id && count($users) == 1) {

				// We do not need to pluralize here since we know there's always only 1 recipient
				$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_FAVOURITED_ON_USERS_PHOTO_ALBUM' . ES::user($item->actor_id)->getGenderLang(), ES::user($item->actor_id)->getName());

				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf('APP_USER_PHOTOS_NOTIFICATIONS_FAVOURITED_ON_USERS_PHOTO_ALBUM', $names, ES::user($album->user_id)->getName());

			return;
		}

		return;
	}
}
