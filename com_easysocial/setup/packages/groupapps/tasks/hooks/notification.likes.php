<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGroupAppTasksHookNotificationLikes extends SocialAppHooks
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

		$milestone = ES::table('Milestone');
		$milestone->load($item->uid);

		// Set the milestone title as the content
		$item->content = $milestone->title;

		// We need to generate the notification message differently for the author of the item and the recipients of the item.
		if ($milestone->owner_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_TASKS_USER_LIKES_YOUR_MILESTONE', $users), $names);

			return;
		}

		if ($milestone->owner_id == $item->actor_id && count($users) == 1) {
			$item->title = JText::sprintf('APP_GROUP_TASKS_USER_LIKES_THEIR_MILESTONE', $names);
			return;
		}

		$item->title = JText::sprintf($this->getPlurality('APP_GROUP_TASKS_USER_LIKES_USERS_MILESTONE', $users), $names, ES::user($milestone->owner_id)->getName());

		return $item;
	}
}
