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

class SocialUserAppBadgesHookNotificationComments
{
	/**
	 * Execute notification for comments
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function execute($item)
	{
		// Get the badge
		$badge = ES::table('Badge');
		$badge->load($item->uid);

		// Break the namespace
		list($element, $group, $verb, $owner) = explode('.', $item->context_type);

		// Get the permalink of the achievement item which is the stream item
		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('context_type' => $element, 'verb' => $verb, 'actor_type' => $group, 'actor_id' => $owner));

		// Get comment participants
		$model = ES::model('Comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// place the actor in the first place of array // #4554
		array_unshift($users, $item->actor_id);

		$users = array_values(array_unique(array_diff($users, array(ES::user()->id))));

		// Convert the names to stream-ish
		$names = ES::string()->namesToNotifications($users);

		// Get the badge image
		$item->image = $badge->getAvatar();

		$plurality = count($users) > 1 ? '_PLURAL' : '_SINGULAR';

		if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
			$title = JText::sprintf('APP_USER_BADGES_USER_REPLIED_COMMENT_ON_ACHIEVEMENT', ES::user($item->actor_id)->getName(), $badge->get('title'), ES::user($owner)->getName());

			if ($owner == $item->target_id) {
				$title = JText::sprintf('APP_USER_BADGES_USER_REPLIED_COMMENT_ON_YOUR_ACHIEVEMENT', ES::user($item->actor_id)->getName(), $badge->get('title'));
			}

			$item->title = $title;
			return;
		}

		// We need to generate the notification message differently for the author of the item and the recipients of the item.
		if ($owner == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {
			$item->title = JText::sprintf('APP_USER_BADGES_USER_COMMENTED_ON_YOUR_ACHIEVEMENT' . $plurality, $names, $badge->get('title'));

			return $item;
		}

		if ($owner == $item->actor_id && count($users) == 1) {
			$item->title = JText::sprintf('APP_USER_BADGES_OWNER_COMMENTED_ON_ACHIEVEMENT' . ES::user($owner)->getGenderLang(), $names, $badge->get('title'));

			return $item;
		}

		// This is for 3rd party viewers
		$item->title = JText::sprintf('APP_USER_BADGES_USER_COMMENTED_ON_USERS_ACHIEVEMENT' . $plurality, $names, ES::user($owner)->getName(), $badge->get('title'));

		return $item;
	}

}
