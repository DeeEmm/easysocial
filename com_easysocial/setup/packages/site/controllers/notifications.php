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

class EasySocialControllerNotifications extends EasySocialController
{
	/**
	 * Checks for new friend requests
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function friends()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Friends');
		$total = $model->getTotalRequests($this->my->id);

		return $this->view->call(__FUNCTION__, $total);
	}

	/**
	 * Allows caller to set all notification items as read
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setAllRead()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Notifications');
		$result = $model->setAllState(SOCIAL_NOTIFICATION_STATE_READ);

		if (!$result) {
			return $this->view->exception('COM_EASYSOCIAL_NOTIFICATIONS_FAILED_TO_MARK_AS_READ');
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to clear all notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function clear()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Notifications');
		$result = $model->setAllState('clear');

		if (!$result) {
			return $this->view->exception('COM_EASYSOCIAL_NOTIFICATIONS_FAILED_TO_REMOVE');
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows the caller to set the state of the notification item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setState()
	{
		ES::requireLogin();
		ES::checkToken();

		$state = $this->input->get('state', '', 'string');
		$id = $this->input->get('id', 0, 'int');

		if ($state != 'clear' && (!$id)) {
			return $this->view->exception('COM_EASYSOCIAL_NOTIFICATIONS_INVALID_ID_PROVIDED');
		}

		// remove all notification from this user.
		if ($state == 'clear') {
			if (!$id) {
				$state = ES::notification()->deleteAll();
			} else {
				$state = ES::notification()->delete($id);
			}

			if (!$state) {
				return $this->view->exception('COM_EASYSOCIAL_NOTIFICATIONS_FAILED_TO_REMOVE');
			}
		} else {
			$notification = ES::table('Notification');
			$notification->load($id);

			if (!$notification->id) {
				return $this->view->exception('COM_EASYSOCIAL_NOTIFICATIONS_INVALID_ID_PROVIDED');
			}

			if ($notification->target_id != $this->my->id) {
				return $this->view->exception('COM_EASYSOCIAL_NOTIFICATIONS_NOT_ALLOWED');
			}

			if ($state == 'read') {
				$notification->markAsRead();
			}

			if ($state == 'unread') {
				$notification->markAsUnread();
			}

			if ($state == 'hidden') {
				$notification->markAsHidden();
			}
		}


		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Check for conversation counters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function conversations()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Conversations');
		$total = $model->getNewCount($this->my->id, 'inbox');

		return $this->view->call(__FUNCTION__, $total);
	}

	/**
	 * Retrieves the counter for system based notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function system()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Notifications');
		$options = array(
						'unread' => true,
						'target' => array('id' => $this->my->id, 'type' => SOCIAL_TYPE_USER)
					);
		$total = $model->getCount($options);

		return $this->view->call(__FUNCTION__, $total);
	}

	/**
	 * Retrieves the counter for system based notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function counters()
	{
		ES::requireLogin();
		ES::checkToken();

		$info = new stdClass();
		$info->total = -1;
		$info->data = '';

		$data = new stdClass();
		$data->system = clone $info;
		$data->friend = clone $info;
		$data->conversation = clone $info;

		if ($this->config->get('notifications.system.enabled')) {
			$model = ES::model('Notifications');
			$options = array(
							'unread' => true,
							'target' => array('id' => $this->my->id, 'type' => SOCIAL_TYPE_USER)
						);
			$total = $model->getCount($options);
			$data->system->total = $total;
		}

		if ($this->config->get('notifications.friends.enabled')) {
			$model = ES::model('Friends');
			$total = $model->getTotalRequests($this->my->id);
			$data->friend->total = $total;
		}

		if ($this->config->get('notifications.conversation.enabled')) {
			$model = ES::model('Conversations');
			$total = $model->getNewCount($this->my->id, 'inbox');
			$data->conversation->total = $total;

			$convData = new stdClass();
			$convData->uid = uniqid();
			$convData->title = JText::sprintf('You have %1$s new conversations', $total);
			$convData->contents = '';
			$data->conversation->data = $convData;
		}

		return $this->view->call(__FUNCTION__, $data);
	}

	/**
	 * Retrieves a list of new system notifications for the user.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getNotifications()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load up the notifications library
		$notification = ES::notification();

		$limit = ES::getLimit('notifications.general.pagination');

		$options = array(
						'target_id' => $this->my->id,
						'target_type' => SOCIAL_TYPE_USER,
						'unread' => true,
						'limit' => $limit
					);

		$items = $notification->getItems($options);

		// Mark all items as read if auto read is enabled.
		if ($this->config->get('notifications.system.autoread')) {
			$model = ES::model('Notifications');
			$result = $model->setAllState(SOCIAL_NOTIFICATION_STATE_READ);
		}

		return $this->view->call(__FUNCTION__, $items);
	}

	/**
	 * Retrieves a list of broadcasts
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getBroadcasts()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the model
		$model = ES::model('Broadcast');
		$broadcasts = $model->getBroadcasts($this->my->id);

		return $this->view->call(__FUNCTION__, $broadcasts);
	}

	/**
	 * Load more notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadmore()
	{
		ES::requireLogin();
		ES::checkToken();

		$paginationLimit = ES::getLimit('notifications.general.pagination');
		$startLimit = $this->input->get('startlimit', 0, 'int');

		// Get notification model.
		$options = array(
				'target_id' => $this->my->id,
				'target_type' => SOCIAL_TYPE_USER,
				'group' => SOCIAL_NOTIFICATION_GROUP_ITEMS,
				'limit' => $paginationLimit,
				'startlimit' => $startLimit
			);

		$notification = ES::notification();
		$items = $notification->getItems($options);

		$groupCount = count($items);
		$recurvsiveCount = count($items, COUNT_RECURSIVE);
		$actualCount = $recurvsiveCount - $groupCount;

		$nextlimit = $options['startlimit'] + $paginationLimit;

		if ($actualCount < $paginationLimit) {
			$nextlimit = -1;
		}

		return $this->view->call(__FUNCTION__, $items, $nextlimit);
	}
}
