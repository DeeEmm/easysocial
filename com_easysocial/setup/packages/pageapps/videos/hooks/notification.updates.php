<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppVideosHookNotificationUpdates
{
	/**
	 * Processes likes notifications
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function execute(&$item)
	{
		// Get the actor that is tagging the target
		$actor = ES::user($item->actor_id);
		$page = ES::page($item->uid);

		// Set the notification title
		$item->title = JText::sprintf('APP_PAGE_VIDEOS_USER_UPLOADED_NEW_VIDEO', $actor->getName(), $page->getName());

		// For Page, this will be a different actor
		if ($page->isAdmin($actor->id)) {
			$item->setActorAlias($page);
			$item->title = JText::sprintf('APP_PAGE_VIDEOS_PAGE_UPLOADED_NEW_VIDEO', $page->getName());
		}

		// Try to get the video
		$video = ES::video($item->uid, $item->type, $item->context_ids);

		$item->image = $video->getThumbnail();
		return;
	}

}
