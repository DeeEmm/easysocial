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

class SocialGroupAppTasksHookNotificationComments
{
	public function execute($item)
	{
		// Get comment participants
		$model = ES::model('Comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// Include the actor of the stream item as the recipient
		// Exclude myself from the list of users.
		// Ensure that the values are unique

		// place the actor in the first place of array // #4554
		array_unshift($users, $item->actor_id);

		$users = array_diff($users, array(ES::user()->id));
		$users = array_unique($users);
		$users = array_values($users);

		// exclude the target name in the notification title. #4459
		if (isset($item->skipTargetUserNameInTitle) && $item->skipTargetUserNameInTitle && $item->target_type == 'user' && $item->target_id) {
			$index = array_search($item->target_id, $users);

			if ($index !== false) {
				unset($users[$index]);
				$users = array_values($users);
			}
		}

		// Convert the names to stream-ish
		$names  = ES::string()->namesToNotifications($users);

		$plurality = count($users) > 1 ? '_PLURAL' : '_SINGULAR';

		// By default content is always empty;
		$content = '';

		// Only show the content when there is only 1 item
		if (count($users) == 1 && !empty($item->content)) {
			$content = ES::string()->processEmoWithTruncate($item->content);
		}

		$item->content = $content;

		// Load the milestone
		$milestone = ES::table('Milestone');
		$state = $milestone->load($item->uid);

		if (!$state) {
			return;
		}

		if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
			$title = JText::sprintf('APP_GROUP_TASKS_USER_REPLIED_COMMENT_ON_MILESTONE', ES::user($item->actor_id)->getName(), ES::user($milestone->owner_id)->getName());


			if ($milestone->owner_id == $item->target_id) {
				$title = JText::sprintf('APP_GROUP_TASKS_USER_REPLIED_COMMENT_ON_YOUR_MILESTONE', ES::user($item->actor_id)->getName());
			}

			$item->title = $title;
			return;
		}

		// We need to generate the notification message differently for the author of the item and the recipients of the item.
		if ($milestone->owner_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf('APP_GROUP_TASKS_USER_COMMENTED_ON_YOUR_MILESTONE', $names);
			return $item;
		}

		if ($milestone->owner_id == $item->actor_id && count($users) == 1) {
			$item->title = JText::sprintf('APP_GROUP_TASKS_USER_COMMENTED_ON_THEIR_MILESTONE', $names);
			return $item;
		}

		// This is for 3rd party viewers
		$item->title = JText::sprintf('APP_GROUP_TASKS_USER_COMMENTED_ON_USERS_MILESTONE', $names, ES::user($milestone->owner_id)->getName());

		return $item;
	}

}
