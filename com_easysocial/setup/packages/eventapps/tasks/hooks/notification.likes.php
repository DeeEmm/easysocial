<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialEventAppTasksHookNotificationLikes extends SocialAppHooks
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

		list($element, $group, $verb) = explode('.', $item->context_type);

		if ($verb == 'createMilestone') {

			$milestone = ES::table('Milestone');
			$milestone->load($item->uid);

			$item->content = $milestone->title;

			if ($milestone->owner_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_TASKS_USER_LIKES_YOUR_MILESTONE', $users), $names);
				$item->content = $milestone->get('title');
				return;
			}

			// This is for 3rd party viewers
			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_TASKS_USER_LIKES_USERS_MILESTONE', $users), $names, ES::user($milestone->owner_id)->getName());
			$item->content = $milestone->get('title');
		}

		if ($verb == 'createTask') {
			$task = ES::table('Task');
			$task->load($item->uid);

			$item->content = $task->title;

			if ($task->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_TASKS_USER_LIKES_YOUR_TASK', $users), $names);

				return;
			}

			$item->title = JText::sprintf($this->getPlurality('APP_EVENT_TASKS_USER_LIKES_USERS_TASK', $users), $names, ES::user($task->user_id)->getName());
		}

		return;
	}

}
