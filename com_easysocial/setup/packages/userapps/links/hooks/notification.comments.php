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

class SocialUserAppLinksHookNotificationComments
{
	private function getImage(SocialTableNotification &$item)
	{
		// Get the links that are posted for this stream
		$model = ES::model('Stream');
		$links = $model->getAssets($item->uid, SOCIAL_TYPE_LINKS);

		if (!isset($links[0])) {
			return;
		}

		// Initialize default values
		$link = $links[0];
		$actor = ES::user($item->actor_id);
		$meta = ES::registry($link->data);

		$item->content = $meta->get('link');

		// If there's an image, use it
		if ($meta->get('image')) {
			return $meta->get('image');
		}

		return false;
	}

	/**
	 * Processes comment notifications
	 *
	 * @since   1.2
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function execute(SocialTableNotification &$item)
	{
		// Get the owner of the stream item since we need to notify the person
		$stream = ES::table('Stream');
		$stream->load($item->uid);

		$model = ES::model('Stream');
		$links = $model->getAssets($item->uid, SOCIAL_TYPE_LINKS);

		$image = '';
		$title = '';

		if ($links) {
			$link = ES::makeObject($links[0]->data);

			// Convert to registry object
			$assets = ES::registry($link);

			// Load the link object
			$linkTbl = ES::table('Link');
			$linkTbl->loadByLink($assets->get('link'));

			// Retrieve the link image
			$image = $linkTbl->getImage($assets);

			// Get the title of the link
			$title = isset($link->title) ? $link->title : '';
		}

		// Get comment participants
		$model = ES::model('Comments');
		$users = $model->getParticipants($item->uid, $item->context_type);

		// Include the actor of the stream item as the recipient
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
		if (count($users) == 1) {

			// Legacy fix for prior to 1.2 as there is no content stored.
			if (!empty($item->content)) {
				$content = ES::string()->processEmoWithTruncate($item->content);
			}
		}

		// Set the content to the stream
		$item->content = $content;

		// Convert the names to stream-ish
		$names = ES::string()->namesToNotifications($users);

		// Retrieve the image
		$item->image = $image;

		if ($item->cmd == 'comments.replied' && $item->target_type == SOCIAL_TYPE_USER) {
			$title = JText::sprintf('APP_USER_LINKS_USER_REPLIED_COMMENT_ON_LINK', ES::user($item->actor_id)->getName(), ES::user($stream->actor_id)->getName());

			if ($stream->actor_id == $item->target_id) {
				$title = JText::sprintf('APP_USER_LINKS_USER_REPLIED_COMMENT_ON_YOUR_LINK', ES::user($item->actor_id)->getName());
			}

			$item->title = $title;
			return;
		}

		// We need to generate the notification message differently for the author of the item and the recipients of the item.
		if ($stream->actor_id == $item->target_id && $item->target_type == SOCIAL_TYPE_USER) {

			$langString = ES::string()->computeNoun('APP_USER_LINKS_USER_POSTED_COMMENT_ON_YOUR_LINK', count($users));
			$item->title = JText::sprintf($langString, $names, $title);

			return $item;
		}

		// This is for 3rd party viewers
		$langString = ES::string()->computeNoun('APP_USER_LINKS_USER_POSTED_COMMENT_ON_USERS_LINK', count($users));
		$item->title = JText::sprintf($langString, $names, ES::user($stream->actor_id)->getName());

		return $item;
	}
}
