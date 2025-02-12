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

class SocialUserAppLinksHookNotificationLikes extends SocialAppHooks
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
		$stream->load($item->uid);

		// Get the link assets
		$assets = $stream->getAssets(SOCIAL_TYPE_LINKS);

		if (!empty($assets)) {

			$asset = $assets[0];
			$link = ES::makeObject($asset->data);

			if ($link) {

				// Convert to registry object
				$assets = ES::registry($link);

				// Load the link object
				$linkTbl = ES::table('Link');
				$linkTbl->loadByLink($assets->get('link'));

				// Retrieve the link image
				$image = $linkTbl->getImage($assets);

				if ($link->link) {
					$item->content = $link->link;
				}

				if ($image) {
					$item->image = $image;
				}
			}
		}

		// We need to determine if the user is the owner
		if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf($this->getPlurality('APP_USER_LINKS_NOTIFICATIONS_USER_LIKES_YOUR_LINK_UPDATE', $users), $names);

			return;
		}

		// For other users, we just post a generic message
		$item->title = JText::sprintf($this->getPlurality('APP_USER_LINKS_NOTIFICATIONS_USER_LIKES_USERS_LINK_UPDATE', $users), $names, ES::user($stream->actor_id)->getName());

		return;
	}
}
