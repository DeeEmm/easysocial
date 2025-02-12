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

class SocialEventAppDiscussionsHookNotificationLikes extends SocialAppHooks
{
	public function execute(&$item)
	{
		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = $this->getNames($users);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		$discussion = ES::table('Discussion');
		$discussion->load($item->context_ids);

		$event = ES::event($discussion->uid);

		$isOwner = $discussion->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER;

		$item->content = $discussion->title;

		if ($item->context_type == 'discussions.event.create') {

			if ($isOwner) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_DISCUSSIONS_USER_LIKES_YOUR_DISCUSSION', $users), $names, $event->getName());
				return;
			} else {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_DISCUSSIONS_USER_LIKES_USERS_DISCUSSION', $users), $names, ES::user($discussion->created_by)->getName(), $event->getName());
				return;
			}
		}

		if ($item->context_type == 'discussions.event.reply') {

			if ($isOwner) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_DISCUSSIONS_USER_LIKES_YOUR_REPLY', $users), $names, $event->getName());
				return;
			} else {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_DISCUSSIONS_USER_LIKES_USERS_REPLY', $users), $names, ES::user($discussion->created_by)->getName(), $event->getName());
				return;
			}
		}

		if ($item->context_type == 'discussions.event.answered') {
			if ($isOwner) {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_DISCUSSIONS_USER_LIKES_YOUR_ACCEPTED_ANSWER', $users), $names, $event->getName());
				return;
			} else {
				$item->title = JText::sprintf($this->getPlurality('APP_EVENT_DISCUSSIONS_USER_LIKES_USERS_ACCEPTED_ANSWER', $users), $names, ES::user($discussion->created_by)->getName(), $event->getName());
				return;
			}
		}
	}
}
