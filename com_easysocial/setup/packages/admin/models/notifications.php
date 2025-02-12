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

ES::import('admin:/includes/model');

class EasySocialModelNotifications extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('notifications');
	}

	/**
	 * Allows caller to remove notifications
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function deleteNotificationsWithUid($uid, $contextType)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'DELETE FROM `#__social_notifications` WHERE `uid`=' . $db->Quote($uid);
		$query[] = 'AND `context_type`=' . $db->Quote($contextType);

		$sql = $db->sql();
		$sql->raw($query);
		$db->setQuery($sql);

		return $db->query();
	}

	/**
	 * Allows caller to remove notifications
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function deleteNotificationsWithContextId($contextId, $contextType)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'DELETE FROM `#__social_notifications` WHERE `context_ids`=' . $db->Quote($contextId);
		$query[] = 'AND `context_type`=' . $db->Quote($contextType);

		$sql = $db->sql();
		$sql->raw($query);
		$db->setQuery($sql);

		return $db->query();
	}

	public function setAllState($state)
	{
		$my = ES::user();

		$db = ES::db();
		$sql = $db->sql();

		$query = '';

		if ($state == 'clear') {
			$query = 'delete from `#__social_notifications`';
			$query .= ' where `target_id` = ' . $db->Quote($my->id);
			$query .= ' and `target_type` = ' . $db->Quote(SOCIAL_TYPE_USER);
		} else {
			$query = 'update `#__social_notifications` set `state` = ' . $db->Quote($state);
			$query .= ' where `target_id` = ' . $db->Quote($my->id);
			$query .= ' and `target_type` = ' . $db->Quote(SOCIAL_TYPE_USER);
		}

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onNotificationAfterAllStateChange', array($state));

		return $state;
	}

	/**
	 * Saves a notification settings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveNotifications($systemNotifications, $emailNotifications, SocialUser $user)
	{
		$allNotifications = $systemNotifications + $emailNotifications;

		// Get the id's of all the notifications
		$keys = array_keys($allNotifications);
		$rules = array();

		foreach ($keys as $key) {
			$obj = new stdClass();
			$obj->id = $key;
			$obj->email = isset($emailNotifications[$key]) ? $emailNotifications[$key] : true;
			$obj->system = isset($systemNotifications[$key]) ? $systemNotifications[$key] : true;

			$rules[] = $obj;
		}

		// Now that we have the rules, store them.
		foreach ($rules as $rule) {
			$map = ES::table('AlertMap');
			$state = $map->load(array('alert_id' => $rule->id, 'user_id' => $user->id));

			$map->alert_id = $rule->id;
			$map->user_id = $user->id;

			$map->email = $rule->email;
			$map->system = $rule->system;

			$map->store();
		}

		return true;
	}

	/**
	 * Retrieve a list of notification items from the database.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of options.
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('a.*');
		$sql->select('#__social_notifications', 'a');

		$config = ES::config();

		if (ES::isBlockEnabled()) {
			$sql->leftjoin( '#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on( 'a.actor_id' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.actor_id' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		// We should not show banned user's notification to the users #3683
		$sql->innerjoin('#__users', 'b');
		$sql->on('a.actor_id', 'b.id');

		// If published options is provided, only search for respective notification items.
		if (isset($options['unread'])) {
			$sql->where('a.state', SOCIAL_NOTIFICATION_STATE_UNREAD);
		}

		// Only fetch items from specific target id and type if necessary.
		$target = isset($options['target_id']) ? $options['target_id'] : null;

		if ($target) {
			$targetType = $options['target_type'];

			$sql->where('a.target_id', $target);
			$sql->where('a.target_type', $targetType);
		}

		$sql->where('b.block', SOCIAL_JOOMLA_USER_UNBLOCKED);

		// if badges / achievement system disabled, then we shouldn't retrive badges related notifications.
		$config = ES::config();

		if (!$config->get('badges.enabled')) {
			$sql->where('a.type', SOCIAL_TYPE_BADGES, '!=');
		}

		$limit = isset($options['limit']) ? $options['limit'] : 0;

		if ($limit) {
			$startlimit = isset($options['startlimit']) ? $options['startlimit'] : 0;
			$sql->limit($startlimit, $limit );
		}

		// Always order by latest first
		$ordering = isset($options['ordering']) ? $options['ordering'] : '';

		if ($ordering) {
			$direction = isset($options['direction']) ? $options['direction'] : 'DESC';
			$sql->order($ordering , $direction);
		} else {
			$sql->order('a.created', 'DESC');
		}

		$db->setQuery($sql);

		$items = $db->loadObjectList();

		if (!$items) {
			return $items;
		}

		$result = array();

		foreach ($items as $item) {
			$notification = ES::table('Notification');
			$notification->bind($item);

			$result[] = $notification;
		}

		return $result;
	}

	/**
	 * Retrieves the count of notification items.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of options. unread - Only count unread items
	 * @return	int		The count.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getCount($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_notifications', 'a');
		$sql->column('COUNT(1)');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin( '#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.actor_id' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.actor_id' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		// We should not count banned user's notification #3683
		$sql->innerjoin('#__users', 'b');
		$sql->on('a.actor_id', 'b.id');

		// Only fetch items from specific target id and type if necessary.
		$target = isset($options['target']) ? $options['target'] : null;

		if (!is_null($target) && is_array($target)) {
			$targetId = $target['id'];
			$targetType = $target['type'];

			$sql->where('a.target_id', $targetId);
			$sql->where('a.target_type', $targetType);
		}

		$sql->where('b.block', SOCIAL_JOOMLA_USER_UNBLOCKED);

		// Only fetch unread items
		if (isset($options['unread'])) {
			$sql->where('a.state', SOCIAL_NOTIFICATION_STATE_UNREAD);
		}

		// if badges / achievement system disabled, then we shouldn't retrive badges related notifications.
		$config = ES::config();

		if (!$config->get('badges.enabled')) {
			$sql->where('a.type', SOCIAL_TYPE_BADGES, '!=');
		}

		$db->setQuery($sql);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Get notification items that need to be clean up
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getItemsToCleanup($months, $limit = 100)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();
		$now = ES::date();

		// clean the registration temp data for records that exceeded $months.
		$query = "select `id` from `#__social_notifications`";
		$query .= " where date_add( `created` , INTERVAL $months MONTH) <= " . $db->Quote($now->toMySQL());
		$query .= " order by `created` asc";
		$query .= " limit " . $limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadColumn();

		return $results;
	}

	/**
	 * Truncate notifications items
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cleanup($months, $includeUnreadNotifications = false)
	{
		$db = ES::db();
		$now = ES::date();

		$query = 'DELETE FROM `#__social_notifications`';
		$query .= ' WHERE ';

		if (!$includeUnreadNotifications) {
			$query .= ' `state` = ' . $db->Quote('1');
			$query .= ' AND ';
		}

		$query .= ' DATE_ADD(`created`, INTERVAL ' . $months . ' MONTH) <= ' . $db->Quote($now->toMySQL());

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * Publishing scheduled notification.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publishScheduledNotifications($sid)
	{
		$db = ES::db();
		$now = ES::date();

		$query = 'UPDATE `#__social_notifications` SET `state` = ' . $db->Quote(SOCIAL_NOTIFICATION_STATE_UNREAD) . ', `created` = ' . $db->Quote($now->toMySQL());
		$query .= ' WHERE `sid` = ' . $db->Quote($sid);
		$query .= ' AND `state` = ' . $db->Quote(SOCIAL_NOTIFICATION_STATE_SCHEDULED);

		$db->setQuery($query);

		return $db->query();
	}
}
