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

class SocialPageAppPollsHookNotificationComments
{
	/**
	 * Processes comment notifications
	 *
	 * @since   3.1.5
	 * @access  public
	 */
	public function execute(SocialTableNotification &$item)
	{
		// Get the owner of the stream item since we need to notify the person
		$stream = ES::table('Stream');
		$stream->load($item->uid);

		// Get comment participants
		$model = ES::model('Comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// Include the actor of the stream item as the recipient
		// place the actor in the first place of array // #4554
		array_unshift($users, $item->actor_id);

		// Ensure that the values are unique
		$users = array_unique($users);
		$users = array_values($users);

		// Exclude myself from the list of users.
		$index = array_search(ES::user()->id, $users);

		// If the skipExcludeUser is true, we don't unset myself from the list
		if (isset($item->skipExcludeUser) && $item->skipExcludeUser) {
			$index = false;
		}

		if ($index !== false) {
			unset($users[$index]);
			$users = array_values($users);
		}

		// exclude the target name in the notification title. #4459
		if (isset($item->skipTargetUserNameInTitle) && $item->skipTargetUserNameInTitle && $item->target_type == 'user' && $item->target_id) {
			$index = array_search($item->target_id, $users);

			if ($index !== false) {
				unset($users[$index]);
				$users = array_values($users);
			}
		}

		// By default content is always empty;
		$content = '';

		// Only show the content when there is only 1 item
		if (count($users) == 1 && !empty($item->content)) {
			$content = ES::string()->processEmoWithTruncate($item->content);
		}

		$item->content = $content;

		if ($item->context_type == 'polls.page.create') {
			$poll = ES::table('Polls');
			$poll->load(array('id' => $item->uid));

			$page = ES::page($poll->cluster_id);

			// Convert the names to stream-ish
			$names = ES::string()->namesToNotifications($users);

			if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
				$title = JText::sprintf('APP_GROUP_POLLS_USERS_REPLIED_COMMENT_ON_POLL', ES::user($item->actor_id)->getName(), $poll->title, $page->getTitle(), ES::user($poll->created_by)->getName());


				if ($poll->created_by == $item->target_id) {
					$title = JText::sprintf('APP_GROUP_POLLS_USERS_REPLIED_COMMENT_ON_YOUR_POLL', ES::user($item->actor_id)->getName(), $poll->title, $page->getTitle());
				}

				$item->title = $title;
				return;
			}

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($poll->created_by == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {

				$langString = ES::string()->computeNoun('APP_PAGE_POLLS_USERS_COMMENT_ON_YOUR_POLL', count($users));
				$item->title = JText::sprintf($langString, $names, $poll->title, $page->title);

				return $item;
			}

			// This is for 3rd party viewers
			$langString = ES::string()->computeNoun('APP_PAGE_POLLS_USER_POSTED_COMMENT_ON_USERS_POLL', count($users));
			$item->title = JText::sprintf($langString, $names, $poll->title, $page->title);

			return;
		}

		return $item;
	}
}
