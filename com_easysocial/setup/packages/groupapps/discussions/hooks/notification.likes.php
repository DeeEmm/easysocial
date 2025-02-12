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

class SocialGroupAppDiscussionsHookNotificationLikes extends SocialAppHooks
{
	public function execute(SocialTableNotification &$item)
	{
		// Currently like notification only supports discussions.group.create
		$allowed = array('discussions.group.create');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		$discussion = ES::table('Discussion');
		$exists = $discussion->load($item->uid);

		// Ensure that the discussion still exists on the site.
		if (!$exists) {
			return;
		}

		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = $this->getNames($users);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		$group = ES::group($discussion->uid);

		// Update the preview
		$item->content = $discussion->title;

		// When someone likes on the photo that you have uploaded in a group
		if ($item->context_type == 'discussions.group.create') {

			// View of the discussion creator
			if ($discussion->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_DISCUSSIONS_USER_LIKES_YOUR_DISCUSSION', $users), $names, $group->getName());

				return $item;
			}

			// View for every one else
			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_DISCUSSIONS_USER_LIKES_USERS_DISCUSSION', $users), $discussion->getAuthor()->getName(), $group->getName());
			return;
		}

		return;
	}

}
