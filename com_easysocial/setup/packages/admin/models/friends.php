<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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

class EasySocialModelFriends extends EasySocialModel
{
	private $data = null;

	protected $total = null;

	public function __construct()
	{
		parent::__construct('friends');
	}

	/**
	 *
	 * @since	3.0
	 * @access	public
	 *
	 **/
	public function getSuggestedFriends($userId = null, $limit = '0', $countOnly = false, $options = array())
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$user 	= ES::user($userId);
		$result = array();
		$total  = 0;

		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart', 0);

		// retrieve friends of friends, who isn't your friend yet.

		$query = "select `tfid` as `ffriend_id`, count(`score`) as `score` from (";
		$query .= " select f1.`target_id` as `tfid`, f1.`actor_id` as `score` from (";
		$query .= "	select a.`actor_id` as `mfid` from `#__social_friends` as a where a.`target_id` = $user->id and a.`state` = " . SOCIAL_FRIENDS_STATE_FRIENDS;
		$query .= "	union ";
		$query .= "	select a1.`target_id` as `mfid` from #__social_friends as a1 where a1.`actor_id` = $user->id and a1.`state` = " . SOCIAL_FRIENDS_STATE_FRIENDS;
		$query .= ") as fm";
		$query .= "	inner join #__social_friends as f1 on fm.mfid = f1.actor_id and f1.state = " . SOCIAL_FRIENDS_STATE_FRIENDS;
		$query .= "	where f1.target_id not in (	select a.actor_id as `mfid` from #__social_friends as a where a.target_id = $user->id and a.state != " . SOCIAL_FRIENDS_STATE_REJECTED;
		$query .= "	union";
		$query .= "	select a1.target_id as `mfid` from #__social_friends as a1 where a1.actor_id = $user->id and a1.state != " . SOCIAL_FRIENDS_STATE_REJECTED . ")";
		$query .= "	and f1.target_id != $user->id";
		$query .= " union";
		$query .= " select f2.actor_id as `tfid`, f2.target_id as score from (";
		$query .= "	select b.actor_id as `mfid` from #__social_friends as b where b.target_id = $user->id and b.state = " . SOCIAL_FRIENDS_STATE_FRIENDS;
		$query .= "	union";
		$query .= "	select b1.target_id as `mfid` from #__social_friends as b1 where b1.actor_id = $user->id and b1.state = " . SOCIAL_FRIENDS_STATE_FRIENDS;
		$query .= ") as fm2";
		$query .= "	inner join #__social_friends as f2 on fm2.mfid = f2.target_id and f2.state = " . SOCIAL_FRIENDS_STATE_FRIENDS;
		$query .= "	where f2.actor_id not in (	select a.actor_id as `mfid` from #__social_friends as a where a.target_id = $user->id and a.state != " . SOCIAL_FRIENDS_STATE_REJECTED;
		$query .= "	union";
		$query .= "	select a1.target_id as `mfid` from #__social_friends as a1 where a1.actor_id = $user->id and a1.state != " . SOCIAL_FRIENDS_STATE_REJECTED . ")";
		$query .= "	and f2.actor_id != $user->id";
		$query .= ") as x";
		$query .= " INNER JOIN `#__users` AS uu ON uu.`id` = x.`tfid` AND uu.`block` = '0'";

		// exclude esad users
		$query .= " INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`";
		$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";


		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';

			$query .= ' WHERE bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$query .= " group by x.tfid";

		$counterSQL = $query;

		$query .= " order by score desc";

		if (!empty($limit) && $limit > 0) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			// $limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($counterSQL, true);
			$total = $this->getTotal();

			// Get the list of users
			$result = $this->getData($query);
		} else {
			$db->setQuery($query);
			$result 	= $db->loadObjectList();

			$total = count($result);
		}

		$runTrigger = true;
		if ($limit && $total >= $limit) {
			$runTrigger = false;
		}

		// now we trigger custom fields to search users which has the similar
		// data.
		$fieldsLib = ES::fields();
		$fieldModel = ES::model('Fields');
		$fieldsResult = array();


		if ($runTrigger) {

			$fieldSQL = 'select a.*, b.' . $db->nameQuote('type') . ', b.' . $db->nameQuote('element') . ', b.' . $db->nameQuote('group');
			$fieldSQL .= ', c.' . $db->nameQuote('uid') . ' as ' . $db->nameQuote('profile_id');
			$fieldSQL .= ' FROM ' . $db->nameQuote('#__social_fields') . ' as a';
			$fieldSQL .= ' INNER JOIN ' . $db->nameQuote('#__social_apps') . ' as b';
			$fieldSQL .= ' ON a.app_id = b.id';
			$fieldSQL .= ' LEFT JOIN ' . $db->nameQuote('#__social_fields_steps') . ' as c';
			$fieldSQL .= ' ON a.step_id = c.id';
			$fieldSQL .= ' where a.' . $db->nameQuote('friend_suggest') . ' = ' . $db->Quote('1');
			$db->setQuery($fieldSQL);

			$fields = $db->loadObjectList();
			if (count($fields) > 0) {
				foreach ($fields as $item) {

					$field = ES::table('Field');
					$field->bind($item);

					$field->profile_id = $item->profile_id;
					$field->data = isset($item->data) ? $item->data : '';

					$userFieldData = $fieldModel->getCustomFieldsValue($field->id, $user->id, SOCIAL_FIELDS_GROUP_USER);

					$args = array($user, $userFieldData);
					$f = array(&$field);

					$dataResult = $fieldsLib->trigger('onFriendSuggestSearch', SOCIAL_FIELDS_GROUP_USER, $f, $args);
					$fieldsResult = array_merge($fieldsResult, $dataResult);
				}
			}
		}

		$tmpResult = array_merge($result, $fieldsResult);

		//reset $result
		$result = array();

		foreach ($tmpResult as $tmpItem) {
			if (! array_key_exists($tmpItem->ffriend_id, $result)) {
				$result[ $tmpItem->ffriend_id ] = $tmpItem;
			}
		}

		if ($countOnly) {
			return count($result);
		}


		$friends = array();

		if ($result) {
			//preload users.
			$tmp = array();
			foreach ($result as $item) {
				$tmp[] = $item->ffriend_id;
			}

			ES::user($tmp);
			ES::cache()->cacheUsersPrivacy($tmp);

			// getting the result.
			foreach ($result as $item) {
				$obj = new stdClass();

				$obj->friend = ES::user($item->ffriend_id);
				$obj->count = $item->score;

				$friends[] = $obj;
			}
		}

		return $friends;
	}

	/**
	 *
	 * @since	3.0
	 * @access	public
	 *
	 **/
	public function arrayObjectUnique($array, $keep_key_assoc = false)
	{
		$duplicate_keys = array();
		$tmp = array();

		foreach ($array as $key=>$val) {
			// convert objects to arrays, in_array() does not support objects
			if (is_object($val)) {
				$val = (array)$val;
			}

			if (!in_array($val, $tmp)) {
				$tmp[] = $val;
			} else {
				$duplicate_keys[] = $key;
			}
		}

		foreach ($duplicate_keys as $key) {
			unset($array[$key]);
		}

		return $keep_key_assoc ? $array : array_values($array);
	}

	/**
	 * Get mutuals user ids.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getMutualFriends($source, $target, $limit = 0, $options = array())
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select `fid` as `afriend` from';
		$query .= $this->buildMutualFriendQueryTableAlias($source, $target);

		// echo $query;exit;

		$rows = '';
		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($query, true);

			// Get the list of users
			$rows 	= $this->getData($query);

		} else {
			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}


		$friends	= array();
		if ($rows) {
			$tmpIds = array();
			foreach ($rows as $row) {
				$tmpIds[] = $row->afriend;
			}

			// preload user
			ES::user($tmpIds);

			foreach ($rows as $row) {
				$friends[] = ES::user($row->afriend);
			}
		}

		return $friends;
	}

	/**
	 *
	 * @since	3.0
	 * @access	public
	 *
	 **/
	public function getMutualFriendCount($source, $target)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select count(1) from';
		$query .= $this->buildMutualFriendQueryTableAlias($source, $target);

		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadResult();

		return (empty($result)) ? '0' : $result;
	}

	/**
	 *
	 * @since	3.0
	 * @access	public
	 *
	 **/
	private function buildMutualFriendQueryTableAlias($source, $target)
	{
		$db = ES::db();

		$query = " (select af1.`actor_id` as `fid` from `#__social_friends` as af1 where af1.`target_id` = $source and af1.`state` = 1";
		$query .= "		union ";
		$query .= "	select af2.`target_id` as `fid`  from `#__social_friends` as af2 where af2.`actor_id` = $source and af2.`state` = 1";
		$query .= ") as x";
		$query .= " inner join `#__users` as u on x.`fid` = u.`id` and u.`block` = 0";

		// exclude esad users
		$query .= " INNER JOIN `#__social_profiles_maps` as upm on u.`id` = upm.`user_id`";
		$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";

		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' u.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' u.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';
		}

		$query .= " where exists (";
		$query .= "		select bf1.`actor_id` from `#__social_friends` as bf1 where bf1.`target_id` = $target and bf1.`actor_id` = x.`fid` and bf1.`state` = 1";
		$query .= " 		union ";
		$query .= "		select bf2.`target_id` from `#__social_friends` as bf2 where bf2.`actor_id` = $target and bf2.`target_id` = x.`fid`  and bf2.`state` = 1";
		$query .= ")";


		if (ES::isBlockEnabled()) {
			$query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		//debug code;
		//$query = ' (select id as afriend from #__users) as z';

		return $query;
	}



	/**
	 * Determines if the target is a friends of friend with the source.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isFriendsOfFriends($target, $source)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT b.' . $db->nameQuote('target_id') . ' AS ' . $db->nameQuote('id');
		$query[] = 'FROM ' . $db->nameQuote('#__social_friends') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_friends') . ' AS b';
		$query[] = 'ON a.' . $db->nameQuote('target_id') . ' = b.' . $db->nameQuote('actor_id');
		$query[] = 'AND a.' . $db->nameQuote('actor_id') . '=' . $db->Quote($target);
		$query[] = 'AND b.' . $db->nameQuote('target_id') . '=' . $db->Quote($source);
		$query[] = 'UNION';

		$query[] = 'SELECT b.' . $db->nameQuote('actor_id');
		$query[] = 'FROM ' . $db->nameQuote('#__social_friends') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_friends') . ' AS b';
		$query[] = 'ON a.' . $db->nameQuote('actor_id') . ' = b.' . $db->nameQuote('target_id');
		$query[] = 'AND a.' . $db->nameQuote('target_id') . '=' . $db->Quote($target);
		$query[] = 'AND b.' . $db->nameQuote('target_id') . '=' . $db->Quote($source);

		$query[] = 'UNION';
		$query[] = 'SELECT ' . $db->nameQuote('id');
		$query[] = 'FROM ' . $db->nameQuote('#__social_friends');
		$query[] = 'WHERE(';
		$query[] = $db->nameQuote('actor_id') . '=' . $db->Quote($target);
		$query[] = 'AND ' . $db->nameQuote('target_id') . '=' . $db->Quote($source);
		$query[] = 'OR ' . $db->nameQuote('actor_id') . '=' . $db->Quote($source);
		$query[] = 'AND ' . $db->nameQuote('target_id') . '=' . $db->Quote($target);
		$query[] = ')';

		$db->setQuery($query);
		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Determines if the provided id's are friends.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isFriends($source, $target, $state = SOCIAL_FRIENDS_STATE_FRIENDS)
	{
		static $_cache = array();

		$idx = $source . '.' . $target . (int) $state;

		if (!isset($_cache[$idx])) {

			$db = ES::db();
			$query = array();

			$query[] = 'SELECT COUNT(1)';
			$query[] = 'FROM ' . $db->nameQuote('#__social_friends');
			$query[] = 'WHERE';
			$query[] = '(' . $db->nameQuote('actor_id') . '=' . $db->Quote($source) . ' AND ' . $db->nameQuote('target_id') . ' = ' . $db->Quote($target) . ' AND ' . $db->nameQuote('state') . '=' . $db->Quote($state) . ')';
			$query[] = 'OR';
			$query[] = '(' . $db->nameQuote('actor_id') . '=' . $db->Quote($target) . ' AND ' . $db->nameQuote('target_id') . '=' . $db->Quote($source) . ' AND ' . $db->nameQuote('state') . '=' . $db->Quote($state) . ')';

			// Debug
			// $query 	= implode(' ', $query);
			// echo str_ireplace('#__', 'jos_', $query);exit;

			// Glue back query.
			$db->setQuery($query);

			$_cache[$idx] = (bool) $db->loadResult();
		}

		return $_cache[$idx];
	}

	/**
	 * Determines if there is an existing request from source user to targeted user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isPendingFriends($source, $target)
	{
		$db = ES::db();

		$query = 'SELECT COUNT(1)';
		$query .= ' FROM ' . $db->nameQuote('#__social_friends');
		$query .= ' WHERE';
		$query .= ' (' . $db->nameQuote('actor_id') . '=' . $db->Quote($source) . ' OR ' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($target) . ')';
		$query .= ' AND';
		$query .= ' (' . $db->nameQuote('target_id') . '=' . $db->Quote($target) . ' OR ' . $db->nameQuote('target_id') . ' = ' . $db->Quote($source) . ')';
		$query .= ' AND ' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_PENDING);

		$db->setQuery($query);
		$result = (bool) $db->loadResult();

		return $result;
	}

	/**
	 * Get a list of online friends
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOnlineFriends($id, $options = array())
	{
		$config = ES::config();

		$db = ES::db();
		$query = array();

		$query[] = 'SELECT * FROM(';

		$query[] = 'SELECT a.*, IF(a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id) . ', a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('target_id') . ') as ' . $db->nameQuote('friendid');
		$query[] = 'FROM ' . $db->nameQuote('#__social_friends') . ' AS a';

		$query[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
		$query[] = 'ON uu.' . $db->nameQuote('id') . ' = if(a.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($id) . ', a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('target_id') . ')';
		$query[] = 'AND uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		// exclude esad users
		$query[] = 'INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`';
		$query[] = 'INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled()) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';

		}

		$query[] = 'WHERE (';
		$query[] = 'a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id) . ' OR a.' . $db->nameQuote('actor_id') . '=' . $db->Quote($id);
		$query[] = 'AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS);
		$query[] = ')';

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$query[] = ') AS ' . $db->nameQuote('onlinefriend');
		$query[] = 'WHERE EXISTS(SELECT ' . $db->nameQuote('userid') . ' FROM ' . $db->nameQuote('#__session') . ' AS s WHERE s.' . $db->nameQuote('userid') . '= onlinefriend.' . $db->nameQuote('friendid') . ')';

		$limit = ES::normalize($options, 'limit', null);

		if ($limit) {
			$query[] = 'LIMIT ' . $limit;
		}

		$query = implode(' ', $query);

		// echo $query;exit;

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		if (!$rows) {
			return false;
		}

		$friends = array();

		foreach ($rows as $row) {
			if ($row->actor_id != $id) {
				$friends[]	= ES::user($row->actor_id);
			}

			if ($row->target_id != $id) {
				$friends[]	= ES::user($row->target_id);
			}
		}

		return $friends;
	}

	/**
	 * Retrieves a list of friends
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFriends($id, $options = array())
	{
		$config = ES::config();
		$my = ES::user();

		$sort = isset($options['sort']) && $options['sort'] ? $options['sort'] : '';
		$everyone = isset($options['everyone']) ? $options['everyone'] : false;
		$limit = isset($options['limit']) ? $options['limit'] : 0;
		$includeAdmin = isset($options['includeAdmin']) ? $options['includeAdmin'] : null;
		$online = isset($options['online']) ? $options['online'] : null;

		if (!$config->get('friends.enabled')) {
			$everyone = true;
		}

		$db = ES::db();
		$sql = $db->sql();

		$query = array();

		if ($online) {
			$query[] = 'SELECT * FROM(';
		}

		if ($everyone) {
			$query[] = 'SELECT ' . $db->Quote($id) . ' AS `actor_id`, uu.`id` AS `target_id`, 1 AS `state` FROM ' . $db->nameQuote('#__users') . ' AS uu';

		} else {

			$query[] = 'SELECT a.*, if(a.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($id) . ', a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('target_id') . ') AS friendid';
			$query[] = 'FROM ' . $db->nameQuote('#__social_friends') . ' AS a';

			$query[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
			$query[] = 'ON uu.' . $db->nameQuote('id') . ' = if(a.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($id) . ', a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('target_id') . ')';

		}

		// exclude esad users
		$query[] = 'INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`';
		$query[] = 'INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled($my)) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';

		}

		// Check if the caller wants to filter friends by list.
		$listId = isset($options[ 'list_id' ]) ? $options[ 'list_id' ] : null;

		if (!is_null($listId)) {
			$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_lists_maps') . ' AS b';
			$query[] = 'ON (';
			$query[] = 'a.' . $db->nameQuote('target_id') . '= b.' . $db->nameQuote('target_id');
			$query[] = 'OR';
			$query[] = 'a.' . $db->nameQuote('actor_id') . '= b.' . $db->nameQuote('target_id');
			$query[] = ')';
			$query[] = 'AND b.' . $db->nameQuote('target_type') . '=' . $db->Quote(SOCIAL_TYPE_USER);
			$query[] = 'AND b.' . $db->nameQuote('list_id') . '=' . $db->Quote($listId);
		}

		$query[] = 'WHERE uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		// user block continue here
		if (ES::isBlockEnabled($my)) {
			$query[] = 'AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		// $query[] = 'AND uu.' . $db->nameQuote('id') . ' != ' . $db->Quote($id);

		if ($everyone) {
			$query[] = 'AND uu.' . $db->nameQuote('id') . ' != ' . $db->Quote($id);

		} else {

		// if (!$everyone) {

			// Check if state is passed in.
			$state = isset($options[ 'state' ]) ? $options[ 'state' ] : SOCIAL_FRIENDS_STATE_FRIENDS;
			$isRequest = isset($options[ 'isRequest' ]) ? $options[ 'isRequest' ] : false;
			$limit = isset($options[ 'limit' ]) ? $options[ 'limit' ] : false;

			// Add filtering by state.
			if (!is_null($state) && $state == SOCIAL_FRIENDS_STATE_PENDING && !$isRequest) {
				$query[] = 'AND a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id);
				$query[] = 'AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_PENDING) ;
			} else if(!is_null($state) && $state == SOCIAL_FRIENDS_STATE_PENDING && $isRequest) {
				$query[] = 'AND a.' . $db->nameQuote('actor_id') . '=' . $db->Quote($id);
				$query[] = 'AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_PENDING);
			} else {
				$query[] = 'AND';
				$query[] = '(';
				$query[] = '(a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id);
				$query[] = 'AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS) . ')';
				$query[] = 'OR';
				$query[] = '(a.' . $db->nameQuote('actor_id') . '=' . $db->Quote($id);
				$query[] = 'AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS) . ')';
				$query[] = ')';
			}
		}

		// If caller doesn't want to include admin, we need to set the ignore list.
		if ($includeAdmin === false) {
			$excludeIds = isset($options['exclude']) && $options['exclude'] ? $options['exclude'] : array();

			// Get a list of site administrators from the site.
			$userModel = ES::model('Users');
			$admins = $userModel->getSiteAdmins(true);

			// Add into existing exclude options
			if ($admins) {
				$excludeIds = !is_array($excludeIds) ? explode(',', $excludeIds) : $excludeIds;
				$excludeIds = array_merge($excludeIds, $admins);
			}

			$options['exclude'] = $excludeIds;
		}

		// used in conversations library to retrieve users who not yet a participants in a onversation.
		// #750
		if (isset($options['exclude']) && $options['exclude']) {

			$excludeIds = $options['exclude'];

			if (!is_array($excludeIds)) {
				$excludeIds = explode(',', $excludeIds);
			}

			$query[] = 'AND uu.' . $db->nameQuote('id') . ' NOT IN (' . implode(',', $excludeIds) . ')';
		}

		if ($sort) {
			// for now we only support lastseen sorting.
			if ($sort == 'lastseen') {
				$query[] = 'order by uu.`lastvisitDate` desc';
			}
		}

		if ($online) {
			$query[] = ') AS ' . $db->nameQuote('onlinefriend');
			$query[] = 'WHERE EXISTS(SELECT ' . $db->nameQuote('userid') . ' FROM ' . $db->nameQuote('#__session') . ' AS s ';

			if ($everyone) {
				$query[] = 'WHERE s.' . $db->nameQuote('userid') . ' = onlinefriend.' . $db->nameQuote('target_id');
			} else {
				$query[] = 'WHERE s.' . $db->nameQuote('userid') . ' = onlinefriend.' . $db->nameQuote('friendid');
			}

			$query[] = ')';
		}

		// Glue back query.
		$query 	= implode(' ', $query);

		// echo $query;exit;
		// $sql->raw($query);
		// echo $sql->debug();exit;

		if ($limit != 0) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($query, true);

			// Get the list of users
			$rows = $this->getData($query);
		} else {
			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}

		if (!$rows) {
			return false;
		}

		$friends = array();

		$idONLY = isset($options[ 'idonly' ]) ? true : false;

		foreach ($rows as $row) {
			if ($row->actor_id != $id) {
				$friends[] = ($idONLY) ? $row->actor_id : ES::user($row->actor_id);
			}

			if ($row->target_id != $id) {
				$friends[] = ($idONLY) ? $row->target_id : ES::user($row->target_id);
			}
		}

		return $friends;
	}

	/**
	 * Retrieves a list of friends that are in pending approval state.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPendingRequests($id)
	{
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->nameQuote('#__social_friends') . ' AS a';
		$query[] = ' INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
		$query[] = ' ON uu.' . $db->nameQuote('id') . ' = a.' . $db->nameQuote('actor_id');

		// exclude esad users
		$query[] = ' INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`';
		$query[] = ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled()) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';
		}

		$query[] = ' WHERE uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$query[] = 'AND a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id);
		$query[] = 'AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_PENDING);

		// Glue query back.
		$query = implode(' ', $query);

		// Get the total number of records before applying any pagination.
		$this->total = $this->getTotal($query);

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		if (!$rows) {
			return false;
		}

		$friends = array();

		foreach ($rows as $row) {
			$friend = ES::friends($row->target_id, $row->actor_id);
			$friends[] = $friend;
		}

		return $friends;
	}

	/**
	 * Retrieves total number of friends a user has.
	 * @since	1.0
	 * @access	public
	 **/
	public function getFriendsCount($userId)
	{
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_friends');
		$query[] = 'WHERE';
		$query[] = '((';
		$query[] = $db->nameQuote('actor_id') . '=' . $db->Quote($userId);
		$query[] = 'AND ' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS);
		$query[] = ') OR (';
		$query[] = $db->nameQuote('target_id') . '=' . $db->Quote($userId);
		$query[] = 'AND ' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS);
		$query[] = '))';

		// Glue back the query.
		$query = implode(' ', $query);
		$db->setQuery($query);

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Allows caller to make a friend request from source to target
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function request($sourceId, $targetId, $state = SOCIAL_FRIENDS_STATE_PENDING)
	{
		// Do not allow user to create a friend request to himself
		if ($sourceId == $targetId) {
			$this->setError(JText::_('COM_EASYSOCIAL_FRIENDS_UNABLE_TO_ADD_YOURSELF'));

			return false;
		}

		// If they are already friends, ignore this.
		if ($this->isFriends($sourceId, $targetId)) {
			$this->setError(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_ALREADY_FRIENDS'));
			return false;
		}

		// Check if user has already previously requested this.
		if ($this->isFriends($sourceId, $targetId, SOCIAL_FRIENDS_STATE_PENDING)) {
			$this->setError(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_ALREADY_REQUESTED'));
			return false;
		}

		// If everything is okay, we proceed to add this request to the friend table.
		$table = ES::table('Friend');
		$table->setActorId($sourceId);
		$table->setTargetId($targetId);
		$table->setState($state);
		$table->message = '';

		// Save the request
		$state 	= $table->store();

		return $table;
	}

	/**
	 * Returns the total number of friend requests a user has.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalRequests($id)
	{
		$config = ES::config();
		$db = ES::db();

		if (!$config->get('friends.enabled')) {
			return '0';
		}

		$query 	= 'SELECT COUNT(1) FROM';
		$query	.= ' ' . $db->nameQuote('#__social_friends') . ' as a ';

		$query	.= ' INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
		$query 	.= ' ON uu.' . $db->nameQuote('id') . ' = a.' . $db->nameQuote('actor_id');

		// exclude esad users
		$query .= ' INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`';
		$query .= ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';
		}

		$query 	.= ' WHERE uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$query 	.= ' AND a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id);
		$query 	.= ' AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_PENDING);

		$db->setQuery($query);
		$count 	= $db->loadResult();

		return $count;
	}


	/**
	 * Returns the total number of friend requests a user has.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getRequests($id)
	{
		$config = ES::config();
		$db = ES::db();

		$query = 'SELECT * FROM';
		$query .= ' ' . $db->nameQuote('#__social_friends') . ' as a ';

		$query .= ' INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
		$query  .= ' ON uu.' . $db->nameQuote('id') . ' = a.' . $db->nameQuote('actor_id');

		// exclude esad users
		$query .= ' INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`';
		$query .= ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';
		}

		$query 	.= ' WHERE uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$query 	.= ' AND a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id);
		$query 	.= ' AND a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_FRIENDS_STATE_PENDING);

		$db->setQuery($query);
		$count 	= $db->loadObjectList();

		return $count;
	}

	/**
	 * Returns the total number of friend a user has.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalFriends($id, $options = array())
	{
		$config = ES::config();
		$db 	= ES::db();

		$everyone = isset($options['everyone']) ? $options['everyone'] : false;
		if (!$config->get('friends.enabled')) {
			$everyone = true;
		}


		$query = array();

		$query[] = 'SELECT COUNT(1)';

		if ($everyone) {
			$query[] = 'FROM' . $db->nameQuote('#__users') . ' AS uu';

		} else {
			$query[] = 'FROM ' . $db->nameQuote('#__social_friends') . ' AS a';

			$query[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu';
			$query[] = 'ON uu.' . $db->nameQuote('id') . ' = if(a.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($id) . ', a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('target_id') . ')';
		}


		// exclude esad users
		$query[] = 'INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`';
		$query[] = 'INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled()) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' uu.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';

		}

		$query[] = 'WHERE uu.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		if ($everyone) {
			$query[] = ' AND uu.' . $db->nameQuote('id') . ' != ' . $db->Quote($id);

		} else {
			$query[] = 'AND';
			$query[] = '((';
			$query[] = 'a.' . $db->nameQuote('target_id') . '=' . $db->Quote($id);
			$query[] = 'AND a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS);
			$query[] = ') OR (';
			$query[] = 'a.' . $db->nameQuote('actor_id') . '=' . $db->Quote($id);
			$query[] = 'AND a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS);
			$query[] = '))';
		}

		$query = implode(' ', $query);

		// echo $query;exit;

		$db->setQuery($query);
		$count 	= (int) $db->loadResult();

		return $count;
	}


	/**
	 * Returns the total number of friend request a user made.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalRequestSent($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_friends', 'a');

		$sql->join('#__users', 'uu', 'INNER');
		$sql->on('uu.id', 'a.target_id');

		$sql->where('a.actor_id', $id);
		$sql->where('a.state', SOCIAL_FRIENDS_STATE_PENDING);

		$sql->where('uu.block', '0');


		$db->setQuery($sql->getTotalSql());

		$count = (int) $db->loadResult();

		return $count;
	}



	/**
	 * Returns the total number of friend a user has.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPendingFriends($id)
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_friends', 'a');

		$sql->join('#__users', 'uu', 'INNER');
		$sql->on('uu.id', 'a.actor_id');

		$sql->where('a.target_id', $id);
		$sql->where('a.state', SOCIAL_FRIENDS_STATE_PENDING);

		$sql->where('uu.block', '0');

		$db->setQuery($sql->getTotalSql());

		$count 	= (int) $db->loadResult();

		return $count;
	}

	/**
	 * Cancels a friend request.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function cancel($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_friends');
		$sql->where('id', $id);
		$db->setQuery($sql);

		$db->Query();

		return true;
	}

	/**
	 * Searches for a user's friend.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function search($id, $term, $type, $options = array())
	{
		$config = ES::config();
		$db	 = ES::db();

		// Default options
		$includeMe = isset($options['includeme']) ? $options['includeme'] : null;
		$everyone  = isset($options['everyone']) ? $options['everyone'] : null;
		$includeAdmin = isset($options['includeAdmin']) ? $options['includeAdmin'] : null;
		$maxLimit = isset($options['maxLimit']) ? (int) $options['maxLimit'] : false;

		$excludeUserIds = [];

		// overide to search everyone if friends disabled.
		if (!$config->get('friends.enabled')) {
			$everyone = true;
		}

		$query   = array();
		$query[] = 'SELECT b.' . $db->nameQuote('id') . ' FROM ' . $db->nameQuote('#__users') . ' AS b';

		// exclude esad users
		$profileTypeIds = $this->getCommunityProfileTypeIds(false, false);
		$query[] = "INNER JOIN `#__social_profiles_maps` AS upm on b.`id` = upm.`user_id` and upm.`profile_id` IN (" . implode(',', $profileTypeIds) . ")";

		if (ES::isBlockEnabled()) {
			$excludeUserIds = $this->getBlockedUserIds(JFactory::getUser()->id);
		}

		if (!$everyone) {
			$query[] = 'inner join (';
			$query[] = '	select if(f.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($id) . ', f.' . $db->nameQuote('target_id') . ', f.' . $db->nameQuote('actor_id') . ') AS ' . $db->nameQuote('friend');
			$query[] = '		from ' . $db->nameQuote('#__social_friends') . ' as f';
			$query[] = ' 			where ((f.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($id) . ' and f.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS) . ')';
			$query[] = '					OR';
			$query[] = ' 				  (f.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($id) . ' and f.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS) . '))';
		}

		if ($includeMe && !$everyone) {
			$query[] = ' UNION select ' . $id . ' AS ' . $db->nameQuote('friend');
		}

		if (!$everyone) {
			$query[] = ') as z';
			$query[] = 'ON b.' . $db->nameQuote('id') . ' = z.' . $db->nameQuote('friend');
		}

		// Searched user must be valid user.
		$query[] = 'WHERE b.' . $db->nameQuote('block') . '=' . $db->Quote(0);

		if (!$includeMe) {
			// $query[] = 'and b.' . $db->nameQuote('id') . ' != ' . $db->Quote($id);
			$excludeUserIds[] = $id;
		}

		if ($type == SOCIAL_FRIENDS_SEARCH_NAME || $type == SOCIAL_FRIENDS_SEARCH_REALNAME) {
			// There are instances prior to 3.0.0 where the stored name could have extra spacing. #2751
			// so we need to trim all the whitespaces from the name column
			$nameQuery = 'AND REPLACE(REPLACE(REPLACE(';
			$nameQuery .= $db->nameQuote('name') . ',' . $db->Quote(' ') . ',';
			$nameQuery .= $db->Quote('<>') . '),' . $db->Quote('><') . ',' . $db->Quote('') . ')';
			$nameQuery .= ',' . $db->Quote('<>') . ',' . $db->Quote(' ') . ')';

			// Now we can apply the search
			$nameQuery .= ' LIKE ' . $db->Quote('%' . $db->escape($term, true) . '%');

			$query[] = $nameQuery;
		}

		if ($type == SOCIAL_FRIENDS_SEARCH_USERNAME) {
			$query[] = 'AND b.' . $db->nameQuote('username') . ' LIKE ' . $db->Quote('%' . $db->escape($term, true) . '%');
		}

		// If caller doesn't want to include admin, we need to set the ignore list.
		if ($includeAdmin === false) {
			// Get a list of site administrators from the site.
			$userModel = ES::model('Users');
			$admins = $userModel->getSiteAdmins(true);

			// Add into existing exclude options
			if ($admins) {
				$excludeUserIds = array_merge($excludeUserIds, $admins);
			}
		}

		if (isset($options['exclude']) && $options['exclude']) {
			$excludeIds = isset($options['exclude']) && $options['exclude'] ? $options['exclude'] : array();

			$excludeIds = !is_array($excludeIds) ? explode(',', $excludeIds) : $excludeIds;

			$excludeUserIds = array_merge($excludeUserIds, $excludeIds);
		}


		if ($excludeUserIds) {

			$excludeUserIds = array_unique($excludeUserIds);

			$excludeIds = '';

			foreach ($excludeUserIds  as $id) {
				$excludeIds .= (empty($excludeIds)) ? $db->Quote($id) : ', ' . $db->Quote($id);
			}

			$query[] = 'AND b.' . $db->nameQuote('id') . ' NOT IN (' . $excludeIds . ')';
		}

		// Glue back query.
		$query = implode(' ', $query);

		// echo $query;exit;


		$limit = isset($options['limit']) ? $options['limit'] : false;
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '10';

		if ($limit && $maxLimit === false) {

			// get the total count.
			$replaceStr = 'SELECT b.' . $db->nameQuote('id') . ' FROM ';
			$totalSQL = str_replace($replaceStr, 'SELECT COUNT(1) FROM ', $query);

			$db->setQuery($totalSQL);
			$this->total = $db->loadResult();

			// now we append the limit
			$query .= " LIMIT $limitstart, $limit";
		}

		if (!$limit && $maxLimit) {
			// now we append the limit
			$query .= " LIMIT $maxLimit";
		}

		// echo $query;exit;

		$db->setQuery($query);

		$result = $db->loadColumn();

		if (!$result) {
			return false;
		}

		if (isset($options['privacy'])) {
			$my = ES::user();

			$privacyLib  = $my->getPrivacy();

			$privacyRule = $options['privacy'];

			$finalResult = array();

			foreach ($result as $rs) {
				$addItem = $privacyLib->validate($privacyRule, $rs);

				if ($addItem) {
					$finalResult[] = $rs;
				}
			}

			$result = $finalResult;
		}

		$friends = ES::user($result);

		return $friends;
	}

	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Retrieves a list of invited users
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getInvitedUsers($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_friends_invitations');
		$sql->where('user_id', $userId);
		$sql->order('created', 'DESC');

		$db->setQuery($sql);

		$invites = $db->loadObjectList();

		return $invites;
	}

	/**
	 * Retrieves the total number of invites user has already sent
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getTotalInvites($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_friends_invitations');
		$sql->column('COUNT(1)');
		$sql->where('user_id', $userId);

		$db->setQuery($sql);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Removes users from the friend list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeFromFriendLists($actorId, $targetId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_lists_maps');
		$sql->where('target_id', $actorId);
		$sql->where('target_id', $targetId, '=', 'OR');

		$db->setQuery($sql);
		return $db->Query();
	}
	/**
	 * Removes users from the friend list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeFriend($actorId, $targetId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_friends');
		$sql->where('target_id', $actorId);
		$sql->where('target_id', $targetId, '=', 'OR');

		$db->setQuery($sql);
		$db->Query();

		$query = 'SELECT alias FROM '.$db->nameQuote('#__social_users').'WHERE user_id ='. $db->Quote($targetId);
		$db->setQuery($query);
		$name = $db->loadResult();
		return $name;
	}
	/**
	 * Retrieves id of friends
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getFriendsId($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'SELECT target_id FROM '.$db->nameQuote('#__social_friends').'WHERE actor_id ='. $db->Quote($userId);
		$db->setQuery($query);

		$ids = $db->loadObjectList();

		return $ids;
	}

	/**
	 * Retrieves id of friends
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getOnlineFriendsId($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'SELECT userid as online FROM  '.$db->nameQuote('#__session').' WHERE userid ='. $db->Quote($userId);
		$db->setQuery($query);

		$ids = $db->loadObjectList();

		return $ids;
	}

	/**
	 * Retrieve friends for GDPR
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getFriendsGDPR($userId, $excludeId, $limit = 20, $options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.`id`, a.`target_id`, a.`state`, a.`created`, b.`name` as `friend` FROM ' . $db->nameQuote('#__social_friends') . ' as a';
		$query[] = 'LEFT JOIN ' . $db->nameQuote('#__users') . ' as b';
		$query[] = 'on a.`target_id` = b.`id`';

		$query[] = 'WHERE a.`actor_id` = ' . $db->Quote($userId);
		$query[] = 'OR a.`target_id` = ' . $db->Quote($userId);

		if ($excludeId) {
			$query[] = 'AND a.`id` NOT IN (' . implode(',', $excludeId) . ')';
		}

		if (isset($options['state'])) {
			$query[] = 'AND a.`state` = ' . $db->Quote($options['state']);
		}

		$query[] = 'ORDER BY a.`created` DESC';
		$query[] = 'LIMIT ' . ($limit + 1);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}
}
