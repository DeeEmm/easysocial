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

class SocialUserAppK2HookNotificationLikes extends SocialAppHooks
{
	public function execute($item)
	{
		// If the skipExcludeUser is true, we don't unset myself from the list
		$excludeCurrentViewer = (isset($item->skipExcludeUser) && $item->skipExcludeUser) ? false : true;

		$users = $this->getReactionUsers($item->uid, $item->context_type, $item->actor_id, $excludeCurrentViewer);
		$names = $this->getNames($users);
		$item->reaction = $this->getReactions($item->uid, $item->context_type);

		// Assign first users from likers for avatar
		$item->userOverride = ES::user($users[0]);

		list($element, $group, $verb) = explode('.', $item->context_type);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $item->uid));

		if (!$state) {
			return;
		}

		$owner = ES::user($streamItem->actor_id);

		if ($item->target_type === SOCIAL_TYPE_USER && $item->target_id == $owner->id) {
			$item->title = JText::sprintf($this->getPlurality('APP_USER_ARTICLE_USER_LIKES_YOUR_ITEM', $users), $names);

			return $item;
		}

		if ($item->actor_id == $owner->id && count($users) == 1) {
			$item->title = JText::sprintf($this->getGenderForLanguage('APP_USER_ARTICLE_OWNER_LIKES_ITEM', $owner->id), $names);

			return $item;
		}

		$item->title = JText::sprintf($this->getPlurality('APP_USER_ARTICLE_USER_LIKES_USER_ITEM', $users), $names, $owner->getName());

		return $item;
	}
}
