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

class SocialGroupAppFilesHookNotificationLikes extends SocialAppHooks
{
	public function execute(&$item)
	{
		$allowed = array('files.group.uploaded');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = $this->getNames($users);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		// When someone likes on the photo that you have uploaded in a group
		if ($item->context_type == 'files.group.uploaded') {
			$file = ES::table("File");
			$file->load($item->uid);

			$group = ES::group($file->uid);

			if ($file->hasPreview()) {
				$item->image = $file->getPreviewURI();
			}

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($file->user_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
				$item->title = JText::sprintf($this->getPlurality('APP_GROUP_FILES_USER_LIKES_YOUR_FILE', $users), $names, $group->getName());
				return;
			}

			// This is for 3rd party viewers
			$item->title = JText::sprintf($this->getPlurality('APP_GROUP_FILES_USER_LIKES_USERS_FILE', $users), $names, ES::user($file->user_id)->getName(), $group->getName());
			return;
		}

		return;
	}

}
