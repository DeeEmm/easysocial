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

ES::import('admin:/includes/apps/apps');

class SocialUserAppBadgesHookNotificationLikes extends SocialAppHooks
{
	public function execute($item)
	{
		$badge = ES::table('Badge');
		$badge->load($item->uid);

		list($element, $group, $verb, $owner) = explode('.', $item->context_type);

		// Get the permalink of the achievement item which is the stream item
		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('context_type' => $element, 'verb' => $verb, 'actor_type' => $group, 'actor_id' => $owner));

		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = $this->getNames($users);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		// Set the picture of the badge as the notification image
		$item->image = $badge->getAvatar();

		// We need to generate the notification message differently for the author of the item and the recipients of the item.
		if ($owner == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf($this->getPlurality('APP_USER_BADGES_USER_LIKES_YOUR_ACHIEVEMENT', $users), $names, $badge->get('title'));
			return $item;
		}

		if ($owner == $item->actor_id && count($users) == 1) {
			$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_BADGES_OWNER_LIKE_ACHIEVEMENT', $owner), $names, $badge->get('title'));

			return $item;
		}

		$item->title = JText::sprintf($this->getPlurality('APP_USER_BADGES_USER_LIKES_USERS_ACHIEVEMENT', $users), $names, ES::user($stream->actor_id)->getName(), $badge->get('title'));

		return $item;
	}

}
