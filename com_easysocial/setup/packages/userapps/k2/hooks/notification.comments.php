<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialUserAppK2HookNotificationComments
{
	public function execute($item)
	{
		$model = ES::model('comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// place the actor in the first place of array // #4554
		array_unshift($users, $item->actor_id);

		$users = array_diff($users, array(ES::user()->id));
		$users = array_unique($users);
		$users = array_values($users);

		$names = ES::string()->namesToNotifications($users);

		$plurality = count($users) > 1 ? '_PLURAL' : '_SINGULAR';

		$content = '';

		if (count($users) == 1 && !empty($item->content)) {
			$content = ES::string()->processEmoWithTruncate($item->content);
		}

		$item->content = $content;

		list($element, $group, $verb) = explode('.', $item->context_type);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $item->uid));

		if (!$state) {
			return;
		}

		$owner = $streamItem->actor_id;

		if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
			$title = JText::sprintf('APP_USER_APPS_USER_REPLIED_COMMENT_ON_ITEM', ES::user($item->actor_id)->getName(), ES::user($owner)->getName());

			if ($owner == $item->target_id) {
				$title = JText::sprintf('APP_USER_APPS_USER_REPLIED_COMMENT_ON_YOUR_ITEM', ES::user($item->actor_id)->getName());
			}

			$item->title = $title;
			return;
		}

		if ($item->target_type === SOCIAL_TYPE_USER && $item->target_id == $owner) {
			$item->title = JText::sprintf('APP_USER_ARTICLE_USER_COMMENTED_ON_YOUR_ITEM' . $plurality, $names);

			return $item;
		}

		if ($item->actor_id == $owner && count($users) == 1) {
			$item->title = JText::sprintf('APP_USER_ARTICLE_OWNER_COMMENTED_ON_ITEM' . ES::user($owner)->getGenderLang(), $names);

			return $item;
		}

		$item->title = JText::sprintf('APP_USER_ARTICLE_USER_COMMENTED_ON_USER_ITEM' . $plurality, $names, ES::user($owner)->getName());

		return $item;
	}
}
