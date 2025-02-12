<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGroupAppAudiosHookNotificationUpdates
{
	/**
	 * Processes likes notifications
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function execute(&$item)
	{
		// Get the actor that is tagging the target
		$actor = ES::user($item->actor_id);
		$group = ES::group($item->uid);

		// Set the notification title
		$item->title = JText::sprintf('APP_GROUP_AUDIO_USER_UPLOADED_NEW_AUDIO', $actor->getName(), $group->getName());

		// Try to get the audio
		$audio = ES::audio($item->uid, $item->type, $item->context_ids);

		$item->image = $audio->getAlbumArt();
		return;
	}

}
