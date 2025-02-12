<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialUserAppStoryHookNotificationTagged
{
	/**
	 * Processes likes notifications
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function execute(SocialTableNotification &$item)
	{
		// Get the actor
		$actor = ES::user($item->actor_id);

		if ($item->cmd == 'comments.tagged') {

			// Get the comment object
			$comment = ES::table('Comments');
			$comment->load($item->context_ids);

			// Ensure that html codes are stripped e.g. user mention and smiley link
			// So it will not show the clickable link on the system notification popbox
			$item->content = strip_tags($item->content);

			// Set the content of the notification
			$item->content = ES::string()->processEmoWithTruncate($item->content);

			$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_MENTIONED_YOU_IN_COMMENT', $actor->getName());

			return;
		}

		if ($item->cmd == 'stream.tagged') {

			// We need to reload the content to ensure that we get the raw data
			$table = ES::table('StreamItem');
			$table->load($item->uid);

			$stream = ES::table('Stream');
			$stream->load($table->uid);

			// Get the content from the stream table.
			$item->content = ES::string()->processEmoWithTruncate($stream->content);

			// Determine if the actor is a male or female or unknown (shemale?)
			$genderValue = $actor->getFieldData('GENDER');

			// By default we use male.
			$gender = 'MALE';

			if ($genderValue == 2) {
				$gender = 'FEMALE';
			}

			// If the item has a location, we need to display the title a little different.
			// User said he was with you at xxx
			if ($stream->location_id) {

				$location = ES::table('Location');
				$location->load($stream->location_id);

				// We need to format the address
				$address = ESJString::substr($location->address, 0, 15);

				// Determine if the location has any params
				if (!empty($location->params)) {

					$city = $location->getCity();

					if ($city) {
						$address = $city;
					}
				}

				$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_TAGGED_' . $gender . '_WITH_YOU_AT_LOCATION', $actor->getName(), $address);

				return;
			}

			$item->title = JText::sprintf('APP_USER_STORY_NOTIFICATIONS_USER_' . $gender . '_TAGGED_WITH_YOU', $actor->getName());
		}
	}

}
