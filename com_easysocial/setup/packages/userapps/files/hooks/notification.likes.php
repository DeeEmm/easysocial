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

class SocialUserAppFilesHookNotificationLikes extends SocialAppHooks
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

		// When user likes on a story
		if ($item->context_type == 'files.user.create') {

			$stream = ES::table('Stream');
			$stream->load($item->uid);

			$actor = ES::user($stream->actor_id);

			// We need to determine if the user is the owner
			if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_USER_FILES_USER_LIKES_YOUR_FILE', $users), $names);
				return;
			}

			// We do not need to pluralize here since we know there's always only 1 recipient
			if ($item->actor_id == $stream->actor_id && count($users) == 1) {
				$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_FILES_USER_LIKES_FILE', $actor->id), $actor->getName());
				return;
			}

			// For other users, we just post a generic message
			$item->title = JText::sprintf($this->getPlurality('APP_USER_FILES_USER_LIKES_USERS_FILE', $users), $names, $actor->getName());

			return;
		}


		return;
	}
}
