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

ES::import('admin:/includes/apps/apps');

class SocialGroupAppMembers extends SocialAppItem
{
	public $appListing = false;

	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if ($item->cmd == 'groups.tagged' && $item->context_type == 'tagged') {

			// Get the actor
			$actor = ES::user($item->actor_id);

			// We need to reload the content to ensure that we get the raw data
			$table = ES::table('StreamItem');
			$table->load($item->uid);

			$stream = ES::table('Stream');
			$stream->load($table->uid);

			// Get the content from the stream table.
			$item->content = $stream->content;

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

		return false;
	}

	/**
	 * Processes a saved story so that we can notify users who are tagged in the system
	 *
	 * @since   2.0.20
	 * @access  public
	 */
	public function onAfterStorySave(&$stream, $streamItem, $streamTemplate)
	{
		// If there's no "with" data, skip this.
		if (!$streamTemplate->with) {
			return;
		}

		// Determine if this is for a group
		if (!$streamTemplate->cluster_id) {
			return;
		}

		// Get list of users that are tagged in this post.
		$taggedUsers = $streamTemplate->with;

		// Get the creator of this update
		$poster = ES::user($streamTemplate->actor_id);

		// Get the content of the stream item.
		$content = $streamTemplate->content;

		// Get the group object
		$group = ES::group($streamTemplate->cluster_id);

		if (!$taggedUsers) {
			return;
		}

		foreach ($taggedUsers as $id) {

			$taggedUser = ES::user($id);

			// Set the email options
			$emailOptions = array(
				'title' => 'APP_USER_FRIENDS_EMAILS_USER_TAGGED_YOU_IN_POST_SUBJECT',
				'template' => 'apps/user/friends/post.tagged',
				'permalink' => $streamItem->getPermalink(true, true),
				'actor' => $poster->getName(),
				'actorAvatar' => $poster->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $poster->getPermalink(true, true),
				'message' => $content,
				// sid used by scheduled post.
				'sid' => $streamItem->uid
			);

			$systemOptions = array(
				'uid' => $streamItem->id,
				// sid used by scheduled post.
				'sid' => $streamItem->uid,
				'context_type' => 'tagged',
				'type' => 'groups',
				'url' => $streamItem->getPermalink(false, false, false),
				'actor_id' => $poster->id,
				'aggregate' => false,
				'context_ids' => $group->id
			);

			// We'll need to pass the scheduled date for notification processing.
			if (isset($stream->scheduled) && $stream->scheduled) {
				$emailOptions['scheduled'] = $stream->scheduled;
				$systemOptions['scheduled'] = $stream->scheduled;
			}

			// Add new notification item
			ES::notify('groups.tagged',  array($taggedUser->id), $emailOptions, $systemOptions);
		}

		return true;
	}

	/**
	 * Processes notification items
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onBeforeNotificationRedirect(&$notification)
	{
		$allowed = array('group.requested');

		if (!in_array($notification->cmd, $allowed)) {
			return;
		}

		// We want to alter the redirection URL to the apps page
		$group = ES::group($notification->uid);

		// Get the application object
		$application = $this->getApp();

		// Alter the original notification url.
		$notification->url = $group->getAppsPermalink($application->getAlias());
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if ($item->cmd != 'groups.tagged' && $item->context_type != 'tagged') {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'stream';
		$target->endpoint = 'stream.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		$item->target = $target;
	}
}
