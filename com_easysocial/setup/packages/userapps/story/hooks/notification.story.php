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

class SocialUserAppStoryHookNotificationStory
{
	/**
	 * Processes notifications when user posts on another profiles timeline
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function execute(SocialTableNotification &$item)
	{
		// Get the actor
		$actor = ES::user($item->actor_id);

		// Ensure that html codes are stripped
		$item->content = strip_tags($item->content);

		// Legacy fix for anything prior to 1.2
		if (empty($item->content)) {
			$table = ES::table('StreamItem');
			$table->load($item->uid);

			$stream = ES::table('Stream');
			$stream->load($table->uid);

			$item->content = $stream->content;
		}

		$item->content = ES::string()->processEmoWithTruncate($item->content);
		$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_POSTED_ON_YOUR_TIMELINE', $actor->getName());

		return;
	}

}
