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

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelBroadcast extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('broadcast', $config);
	}

	/**
	 * Retrieves a list of broadcasts created on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getBroadcasts($userId)
	{
		$db = ES::db();
		$sql = $db->sql();
		$now = ES::date()->toSql(true);

		$sql->select('#__social_broadcasts');
		$sql->where('target_id', $userId);
		$sql->where('target_type', SOCIAL_TYPE_USER);
		$sql->where('state', 1);
		$sql->where('(');
		$sql->where('expiry_date', $now, '>=');
		$sql->where('expiry_date', '0000-00-00 00:00:00', '=', 'OR');
		$sql->where(')');
		$sql->order('created', 'DESC');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		$broadcasts = array();

		foreach ($result as $row) {

			$broadcast = ES::table('Broadcast');
			$broadcast->bind($row);

			// When the broadcasts are alredy retrieved from the system, it should be marked as read.
			// Otherwise it would keep on spam the user's screen.
			$broadcast->markAsRead();

			$broadcasts[] = $broadcast;
		}

		return $broadcasts;
	}

	/**
	 * Broadcast a message to a set of profiles on the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function broadcast($ids, $content, $createdBy, $title = '', $link = '', $expiryDate = '', $context = '', $streamId = 0, $scheduled = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Default state
		$state = SOCIAL_STATE_PUBLISHED;

		if ($scheduled) {
			$state = SOCIAL_STATE_SCHEDULED;
		}

		$query = [
			'INSERT INTO ' . $db->quoteName('#__social_broadcasts'),
			'(`stream_id`, `target_id`, `target_type`, `title`, `content`, `link`, `state`, `created`, `created_by`, `expiry_date`)'
		];

		// Get the creation date
		$date = ES::date();

		if (empty($expiryDate)) {
			$expiryDate = '0000-00-00 00:00:00';
		}

		if ($context == 'profile') {
			// get the users
			$query[] = 'SELECT';
			$query[] = $db->Quote($streamId) . ', `user_id`,' . $db->Quote(SOCIAL_TYPE_USER) . ',' . $db->Quote($title) . ',' . $db->Quote($content) . ',' . $db->Quote($link) . ',' . $db->Quote($state) . ',' . $db->Quote($date->toSql()) . ',' . $db->Quote($createdBy) . ',' . $db->Quote($expiryDate);
			$query[] = 'FROM ' . $db->quoteName('#__social_profiles_maps');
			$query[] = 'WHERE 1';

			// If this is not an array, make it as an array.
			if (!is_array($ids)){
				$ids = array($ids);
			}

			$ids = implode(',', $ids);

			// If the id is empty, send to all
			if (!empty($ids)) {
				$query[] = 'AND ' . $db->quoteName('profile_id') . ' IN (' . $ids . ')';
			}

			// Exclude the broadcaster because it would be pretty insane if I am spamming myself
			$my = ES::user();
			$query[] = 'AND `user_id` !=' . $db->Quote($my->id);
		}

		// If the context is group, we will get the group members
		if ($context == 'group') {

			$query[] = 'VALUES';

			// get all group members
			$userIds = $this->getGroupMembers($ids);

			if (empty($userIds)) {
				return;
			}

			$count = 1;

			// generate the SQL query
			foreach ($userIds as $userId) {
				$query[] = '(' . $db->Quote($streamId) . ',' . $db->Quote($userId) . ',' . $db->Quote(SOCIAL_TYPE_USER) . ',' . $db->Quote($title) . ',' . $db->Quote($content) . ',' . $db->Quote($link) . ',' . $db->Quote($state) . ',' . $db->Quote($date->toSql()) . ',' . $db->Quote($createdBy) . ',' . $db->Quote($expiryDate) . ')';

				if ($count < count($userIds)) {
					$query[] = ',';
				}

				$count++;
			}
		}

		$query = implode(' ', $query);

		$sql->raw($query);

		$db->setQuery($sql);

		$state = $db->Query();

		if (!$state) {
			return $state;
		}

		// Get the id of the new broadcasted item
		$id = $db->insertid();

		return $id;
	}

	/**
	 * Notify a broadcast a message to a set of profiles on the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function notifyBroadcast($ids, $title, $content, $link, $createdBy, $streamItem, $context, $scheduled = false)
	{
		$db  = ES::db();
		$sql = $db->sql();
		$my = ES::user();

		// Default state
		$publishState = SOCIAL_STATE_PUBLISHED;

		if ($scheduled) {
			$publishState = SOCIAL_STATE_SCHEDULED;
		}

		$systemOptions = array(
			'uid' => $my->id,
			'sid' => $streamItem->uid,
			'actor_id' => $my->id,
			'title' => $title,
			'content' => $content,
			'type' => 'broadcast',
			'url' => ESR::stream(array('layout' => 'item', 'id' => $streamItem->uid))
		);

		$emailParams = array('content' => $content, 'title' => $title);

		$emailOptions = array(
			'title' => 'APP_USER_BROADCAST_EMAILS_NEW_BROADCAST_TITLE',
			'template' => 'apps/user/broadcast/new.broadcast',
			'permalink' => ESR::stream(array('layout' => 'item', 'id' => $streamItem->uid)),
			'params' => $emailParams,
			'sid' => $streamItem->uid
		);

		// Prepare scheduled notification.
		if ($streamItem->isScheduled()) {
			$emailOptions['scheduled'] = $scheduled;
			$systemOptions['scheduled'] = $scheduled;
		}

		$state = false;

		if ($context == 'profile') {
			$state = ES::notifyProfileMembers('broadcast.notify', $ids, $emailOptions, $systemOptions);
		}

		if ($context == 'group') {
			$state = ES::notifyClusterMembers('broadcast.notify', $ids, $emailOptions, $systemOptions);
		}

		if ($state) {
			// Create an empty broadcast record for stream item
			$query = [];

			// Get the creation date
			$date = ES::date();

			$query[] = 'INSERT INTO ' . $db->quoteName('#__social_broadcasts');
			$query[] = '(`stream_id`, `target_id`,`target_type`,`title`,`content`,`link`,`state`,`created`,`created_by`, `expiry_date`) VALUES';
			$query[] = '(' . $db->Quote($streamItem->uid) . ',' . $db->Quote(0) . ','. $db->Quote($context) .',' . $db->Quote($title) . ',' . $db->Quote($content) . ',' . $db->Quote($link) . ',' . $db->Quote($publishState) . ',' . $db->Quote($date->toSql()) . ',' . $db->Quote($createdBy) . ',' . $db->Quote('0000-00-00 00:00:00') . ')';

			$query = implode(' ', $query);

			$sql->raw($query);

			$db->setQuery($sql);

			$state = $db->Query();

			if (!$state) {
				return $state;
			}

			// Get the id of the new broadcasted item
			$id = $db->insertid();

			return $id;
		}

		return $state;
	}

	/**
	 * Retrieve group members
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getGroupMembers($ids)
	{
		$model = ES::model('Groups');
		$my = ES::user();

		// If this is not an array, make it as an array.
		if (!is_array($ids)){
			$ids = array($ids);
		}

		// if empty ids, just get all members from all groups
		if (empty($ids)) {
			// get all groups with published state
			$groups = $model->getGroups(array('state' => SOCIAL_CLUSTER_PUBLISHED));

			foreach ($groups as $group) {
				$ids[] = $group->id;
			}
		}

		$users = array();

		foreach ($ids as $id) {

			// get the active members exclude myself
			$members = $model->getMembers($id, array('state' => SOCIAL_STATE_PUBLISHED, 'exclude' => $my->id));

			// Merge members from all groups
			$users = array_merge($users, $members);
		}

		$result = array();

		foreach ($users as $user) {
			$result[] = $user->id;
		}

		// We have to make it unique so that there will be no duplicate user
		return array_unique($result);
	}

	/**
	 * Retrieve the users
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUsers($ids)
	{
		$db  = ES::db();
		$sql = $db->sql();

		$query  = array();

		$query[] = 'SELECT';
		$query[] = '`user_id`';
		$query[] = 'FROM ' . $db->quoteName('#__social_profiles_maps');
		$query[] = 'WHERE 1';

		// If this is not an array, make it as an array.
		if (!is_array($ids)){
			$ids = array($ids);
		}

		$ids = implode(',', $ids);

		// If the id is empty, send to all
		if (!empty($ids)) {
			$query[] = 'AND ' . $db->quoteName('profile_id') . ' IN (' . $ids . ')';
		}

		// Exclude the broadcaster because it would be pretty insane if I am spamming myself
		$my = ES::user();
		$query[] = 'AND `user_id` !=' . $db->Quote($my->id);

		$query = implode(' ', $query);

		$sql->raw($query);

		$db->setQuery($sql);

		$users = $db->loadObjectList();

		$result = array();

		foreach ($users as $user) {
			$result[] = $user->user_id;
		}

		return $result;
	}

	/**
	 * Updating broadcast's state.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function updateBroadcasts($options = array())
	{
		$state = isset($options['state']) ? $options['state'] : SOCIAL_STATE_PUBLISHED;
		$streamId = isset($options['streamId']) ? $options['streamId'] : false;

		$db = ES::db();

		$query = "UPDATE `#__social_broadcasts` SET `state` = " . $db->Quote($state);
		$query .= " WHERE `stream_id` = " . $db->Quote($streamId);

		$db->setQuery($query);

		return $db->query();
	}
}
