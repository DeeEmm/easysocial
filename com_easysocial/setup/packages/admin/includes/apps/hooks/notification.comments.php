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

class SocialAppHookNotificationComments
{
	/**
	 * Process comments notifications
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function execute(&$item)
	{
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
		$index = array_search(ES::user()->id , $users);

		if ($index !== false) {
			unset($users[$index]);
			$users = array_values($users);
		}

		// Convert the names to stream-ish
		$names = ES::string()->namesToNotifications($users);

		$content = '';

		// Only show the content when there is only 1 user
		if (count($users) == 1 && !empty($item->content)) {
			$content = $item->content;
			$content = str_replace(array("<br />","<br>","<br/>"), "", $content);

			$content = ES::string()->processEmoWithTruncate($content);
			$content = str_replace("\n", "", $content);

			// Fallback method
			// Load the comment object since we have the context_ids
			if (!$content) {
				$comment = ES::table('Comments');
				$comment->load($item->context_ids);

				$content = ES::string()->processEmoWithTruncate($comment->comment);
			}
		}

		switch ($item->cmd) {
			case 'comments.like':
				$item->title = JText::sprintf('COM_EASYSOCIAL_COMMENTS_LIKES_SYSTEM_TITLE', $names);
				break;
			case 'comments.tagged':
				$item->content = $content;
				$item->title = JText::sprintf('COM_EASYSOCIAL_COMMENTS_TAGGED_SYSTEM_TITLE', $names);
				break;
			case 'comments.item':
			case 'comments.involved':
			default:
				$item->content = $content;

				if ($item->title) {
					$item->title = JText::sprintf($item->title, $names);
				} else {
					$item->title = JText::sprintf('COM_EASYSOCIAL_COMMENTS_ITEM_SYSTEM_TITLE', $names);
				}
				break;
		}

		return;
	}
}
