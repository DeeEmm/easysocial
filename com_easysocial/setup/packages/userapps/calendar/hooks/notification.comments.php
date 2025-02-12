<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialUserAppCalendarHookNotificationComments
{
	public function execute($item, $calendar)
	{
		$model = ES::model('comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// Include the notification actor
		// place the actor in the first place of array // #4554
		array_unshift($users, $item->actor_id);

		// Exclude the current user
		$users = array_values(array_unique(array_diff($users, array(ES::user()->id))));

		$names = ES::string()->namesToNotifications($users);

		$plurality = count($users) > 1 ? '_PLURAL' : '_SINGULAR';

		$content = '';

		if (count($users) == 1 && !empty($item->content)) {
			$content = ES::string()->processEmoWithTruncate($item->content);
		}

		$item->content = $content;

		if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
			$title = JText::sprintf('APP_USER_CALENDAR_USER_REPLIED_COMMENT_ON_EVENT', ES::user($item->actor_id)->getName(), ES::user($calendar->user_id)->getName());

			if ($calendar->user_id == $item->target_id) {
				$title = JText::sprintf('APP_USER_CALENDAR_USER_REPLIED_COMMENT_ON_YOUR_EVENT', ES::user($item->actor_id)->getName());
			}

			$item->title = $title;
			return;
		}

		if ($calendar->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf('APP_USER_CALENDAR_USER_COMMENTED_ON_YOUR_EVENT' . $plurality, $names);

			return $item;
		}

		if ($calendar->user_id == $item->actor_id && count($users) == 1) {
			$item->title = JText::sprintf('APP_USER_CALENDAR_OWNER_COMMENTED_ON_EVENT' . ES::user($calendar->user_id)->getGenderLang(), $names);

			return $item;
		}

		$item->title = JText::sprintf('APP_USER_CALENDAR_USER_COMMENTED_ON_USER_EVENT' . $plurality, $names, ES::user($calendar->user_id)->getName());

		return $item;
	}
}
