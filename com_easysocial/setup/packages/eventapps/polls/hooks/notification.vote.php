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

class SocialEventAppPollsHookNotificationVote
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

		$poll = ES::table('Polls');
		$poll->load(array('uid' => $item->uid));

		// Get poll participants
		$model = ES::model('Polls');
		$users = $model->getVoterIds($poll->id);

		// Include the actor of the stream item as the recipient
		$users = array_merge($users, array($item->actor_id));

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

		// By default content is always empty;
		$content = '';

		// Only show the content when there is only 1 item
		if (count($users) == 1 && !empty($item->content)) {
			$content = ES::string()->processEmoWithTruncate($item->content);
		}

		$item->content = $content;

		if ($item->context_type == 'events') {
			$event = ES::event($poll->cluster_id);

			// Convert the names to stream-ish
			$names = ES::string()->namesToNotifications($users);

			// We need to generate the notification message differently for the author of the item and the recipients of the item.
			if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {

				$langString = ES::string()->computeNoun('APP_EVENT_POLLS_USERS_VOTED_ON_YOUR_POLL', count($users));
				$item->title = JText::sprintf($langString, $names, $poll->title, $event->title);

				return $item;
			}

			return;
		}

		return $item;
	}
}
