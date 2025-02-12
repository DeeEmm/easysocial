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

class SocialGroupAppGroupsHookNotificationGroup
{
	public function execute(&$item)
	{
		if ($item->cmd == 'group.requested') {
			$user = ES::user($item->actor_id);
			$group = ES::group($item->uid);
			$item->title = JText::sprintf('APP_USER_GROUPS_NOTIFICATIONS_USER_ASKED_TO_JOIN_GROUP', $user->getName(), $group->getName());
		}

		if ($item->cmd == 'group.invited') {
			$actor = ES::user($item->actor_id);

			$group = ES::group($item->uid);

			$item->title = JText::sprintf('APP_GROUPS_NOTIFICATION_INVITED_TITLE', $actor->getName(), $group->getName());

			$item->image = $group->getAvatar();

			return;
		}

		// For rejection, we know that there's always only 1 target
		if ($item->cmd == 'groups.promoted') {

			// Get the group
			$group  = ES::group($item->uid);

			$item->title = JText::sprintf('APP_GROUP_GROUPS_YOU_HAVE_BEEN_PROMOTED_AS_THE_GROUP_ADMIN', $group->getName());
			$item->image = $group->getAvatar();
			return;
		}

		// For rejection, we know that there's always only 1 target
		if ($item->cmd == 'groups.user.rejected') {

			// Get the group
			$group  = ES::group($item->uid);

			$item->title    = JText::sprintf('APP_GROUP_GROUPS_YOUR_APPLICATION_HAS_BEEN_REJECTED', $group->getName());

			return;
		}

		// For user removal, we know that there's always only 1 target
		if ($item->cmd == 'groups.user.removed') {

			// Get the group
			$group  = ES::group($item->uid);

			$item->title    = JText::sprintf('APP_GROUP_GROUPS_YOU_HAVE_BEEN_REMOVED_FROM_GROUP', $group->getName());

			return;
		}

		if ($item->cmd == 'group.joined') {
			$actor = ES::user($item->actor_id);

			$group = ES::group($item->uid);

			$item->title = JText::sprintf('COM_EASYSOCIAL_GROUPS_NOTIFICATION_JOIN_GROUP', $actor->getName(), $group->getName());

			$item->image = $group->getAvatar();

			return;
		}

		if ($item->cmd == 'group.leave') {
			$actor = ES::user($item->actor_id);

			$group = ES::group($item->uid);

			$item->title = JText::sprintf('COM_EASYSOCIAL_GROUPS_NOTIFICATION_LEAVE_GROUP', $actor->getName(), $group->getName());

			$item->image = $group->getAvatar();

			return;
		}

		if ($item->cmd == 'group.approved') {
			$actor = ES::user($item->actor_id);
			$group = ES::group($item->uid);

			$item->title = JText::sprintf('APP_USER_GROUPS_NOTIFICATIONS_USER_APPROVED_TO_JOIN_GROUP', $actor->getName(), $group->getName());
			$item->image = $group->getAvatar();

			return;
		}
	}

}
