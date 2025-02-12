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

ES::import('admin:/tables/table');

class SocialTableStreamScheduled extends SocialTable
{
	public $id = null;
	public $stream_id = null;
	public $actor_id = null;
	public $actor_type = null;
	public $context_type = null;
	public $context_id = null;
	public $verb = null;
	public $created = null;
	public $modified = null;
	public $scheduled = null;
	public $state = null;

	static $_streamitems = array();

	public function __construct(&$db)
	{
		parent::__construct('#__social_stream_scheduled', 'id', $db);
	}

	/**
	 * Override the parent's store behavior
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		if (is_null($this->modified)) {
			$date = ES::date();
			$this->modified = $date->toSql();
		}
		
		return parent::store();
	}

	/**
	 * Retrieves a list of #__social_stream_scheduled for the user.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getItems($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream_scheduled');
		$sql->where('actor_id', $userId);

		$db->setQuery($sql);

		$items = $db->loadObjectList();

		return $items;
	}

	/**
	 * Publishing scheduled items.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publishScheduled(SocialTableStream $stream, SocialTableStreamItem $streamItem)
	{
		// Publishing the stream item.
		$streamItem->state = SOCIAL_STREAM_STATE_PUBLISHED;
		$streamItem->store();

		// Update stream timestamp to respect the scheduled date. #4769
		// Make sure the scheduled timestamp is no later than current datetime. 
		$scheduled = $this->scheduled;
		$current = ES::date()->toSql();

		if ($scheduled > $current) {
			$scheduled = $current;
		}

		$stream->created = $scheduled;

		// Publishing the stream.
		$stream->publish();

		// Simulating scheduled notification.
		$this->sendScheduledNotification($streamItem->uid);

		// Publishing scheduled taggings.
		$this->publishScheduledTaggings($streamItem->uid);

		// To publish a scheduled post, we'll just remove it from stream scheduled table.
		$state = $this->delete($this->id);

		return $state;
	}

	/**
	 * Simulating notifications and emails for scheduled.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function sendScheduledNotification($stream_id)
	{
		// Since all notifications are already created in the table, 
		// we'll just need to change the state and update the creation date to match the scheduled date.

		// Process system notification.
		$notification = ES::model('notifications');
		$notification->publishScheduledNotifications($stream_id);

		// Processing emails.
		$mailer = ES::model('mailer');
		$mailer->publishScheduledEmails($stream_id);
	}

	/**
	 * Publishing scheduled taggings.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publishScheduledTaggings($stream_id)
	{
		$db = ES::db();

		$query = 'UPDATE `#__social_stream_tags` SET `state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED) . ' WHERE `stream_id` = ' . $db->Quote($stream_id);

		$db->setQuery($query);

		return $db->query();
	}
}