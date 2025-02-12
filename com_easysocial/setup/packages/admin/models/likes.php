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

ES::import('admin:/includes/model');

class EasySocialModelLikes extends EasySocialModel
{
	static $cache = array();

	public function __construct()
	{
		parent::__construct('likes');
	}

	/**
	 * Retrieves reactions listing for the back end
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.* FROM `#__social_likes` AS a';

		// Ordering
		$ordering = $this->getState('ordering', 'created');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$query[] = 'ORDER BY ' . $ordering . ' ' . $direction;
		}

		$sql->raw($query);

		// Set the total
		$this->setTotal($sql->getTotalSql());

		$result = parent::getData($sql->getSql());
		$this->pagination = parent::getPagination();

		if (!$result) {
			return $result;
		}

		return $result;
	}

	/**
	 * Retrieves a list of reactions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReactionsWithState()
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = [
			'SELECT * FROM `#__social_reactions`'
		];

		// State
		$ordering = $this->getState('ordering', 'created');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$query[] = 'ORDER BY ' . $ordering . ' ' . $direction;
		}

		$sql->raw($query);

		// Set the total
		$this->setTotal($sql->getTotalSql());

		$result = parent::getData($sql->getSql());
		$this->pagination = parent::getPagination();

		if (!$result) {
			return $result;
		}

		return $result;
	}

	private function _getLikesCount($uid, $type)
	{
		static $counts 	= array();

		$key 	= $uid . $type;

		if( !isset( $counts[ $key ] ) )
		{
			$db		= ES::db();
			$sql	= $db->sql();

			$sql->select( '#__social_likes' )
				->column( '1', '', 'count', true )
				->where( 'type', $type )
				->where( 'uid', $uid );

			$db->setQuery( $sql );
			$cnt   = $db->loadResult();

			$counts[ $key ]	= $cnt;
		}

		return $counts[ $key ];
	}


	public function setCommentLikesBatch( $data )
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();


		$dataset = array();

		// Go through each of the items
		foreach ($data as $item) {
			// Get related items
			$uid = $item->id;

			// pre-fill the array 1st;
			$key = $uid . '.comments.user.like';

			self::$cache[$key] = array();

			$dataset[] = $uid;
		}

		// lets build the sql now.
		if ($dataset) {

			$query 	= "SELECT a.* FROM `#__social_likes` AS a";
			$query	.= " INNER JOIN `#__users` AS b";
			$query 	.= " ON a.`created_by` = b.`id`";

			if (ES::isBlockEnabled()) {
				// user block
				$query .= ' LEFT JOIN ' . $db->nameQuote( '#__social_block_users' ) . ' as bus';

				$query .= ' ON (';
				$query .= ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
				$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
				$query .= ') OR (';
				$query .= ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
				$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
				$query .= ')';
			}

			$query 	.= " WHERE a.uid IN (" . implode(',' , $dataset). ")";
			$query 	.= " AND a.`type` = 'comments.user.like'";
			$query 	.= " AND b.`block` = " . $db->Quote(0);

			if (ES::isBlockEnabled()) {
				// user block continue here
				$query .= ' AND bus.' . $db->nameQuote( 'id' ) . ' IS NULL';
			}

			$sql->raw( $query );
			$db->setQuery( $sql );

			$result = $db->loadObjectList();

			if ($result) {

				foreach ($result as $row) {

					$key = $row->uid . '.comments.user.like';

					$table = ES::table('Likes');
					$table->bind($row);

					$this->insertCache($key, $table);
				}
			}
		}
	}

	/**
	 * Allows caller to prepopulate data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setStreamLikesBatch($data)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$model = ES::model('Stream');
		$dataset = array();

		if (!$data) {
			return false;
		}

		$clusterIds = array();

		// Initialize the cache structure
		foreach ($data as $item) {
			$uid = $item->id;

			// If there's no context_id, skip this.
			if (!$uid) {
				continue;
			}

			if ($item->cluster_id) {
				$clusterIds[] = $item->cluster_id;
			}

			$key = $item->id . '.stream';

			self::$cache[$key] = array();

			$dataset[] = $uid;
		}

		// Build the main query
		if ($dataset) {

			$mainSQL = '';

			$query 	= "SELECT a.* FROM `#__social_likes` AS a";
			$query	.= " INNER JOIN `#__users` AS b";
			$query 	.= " ON a.`created_by` = b.`id`";

			if (ES::isBlockEnabled()) {
				// user block
				$query .= ' LEFT JOIN ' . $db->nameQuote( '#__social_block_users' ) . ' as bus';

				$query .= ' ON (';
				$query .= ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
				$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);

				// exclude cluster's admin user #4448
				$query .= 'and NOT EXISTS (';
				$query .= ' select clsnd.`uid` from `#__social_clusters_nodes` as clsnd';
				$query .= ' inner join `#__social_stream` as ss on clsnd.`cluster_id` = ss.`cluster_id`';
				$query .= ' where ss.`id` = a.`stream_id` and clsnd.`uid` = bus.`target_id` and clsnd.`admin` = 1)';


				$query .= ') OR (';
				$query .= ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
				$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
				$query .= ')';

			}

			$query 	.= " WHERE a.stream_id IN (" . implode(',' , $dataset). ")";
			$query 	.= " AND b.`block` = " . $db->Quote(0);

			// user block continue here
			if (ES::isBlockEnabled()) {
				$query .= ' AND bus.' . $db->nameQuote( 'id' ) . ' IS NULL';
			}

			$sql->raw($query);
			$db->setQuery($sql);

			$result = $db->loadObjectList();

			if ($result) {

				foreach ($result as $row) {

					$table = ES::table('Likes');
					$table->bind($row);

					$key = $row->stream_id . '.stream';

					$this->insertCache($key, $table);
				}
			}
		}

		return true;
	}

	/**
	 * Inserts a new item into the cache
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function insertCache($key, $table)
	{
		// If the cache structure doesn't exist yet, create one
		if (!isset(self::$cache[$key])) {
			self::$cache[$key] = array();
		}

		self::$cache[$key][$table->created_by . '.' . $table->react_as] = $table;

		return true;
	}

	/**
	 * Removes a like data from the cache
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeCache($key, $userId, $reactAs = 'user')
	{
		if (!$reactAs) {
			// make sure the react_as always have something.
			$reactAs = 'user';
		}

		$idx = $userId . '.' . $reactAs;;

		// Test if the item really exists
		if (!isset(self::$cache[$key][$idx])) {
			return false;
		}

		unset(self::$cache[$key][$idx]);

		return true;
	}

	/**
	 * Delete likes related to an object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_likes');
		$sql->where('uid' , $uid);
		$sql->where('type' , $type);

		$db->setQuery($sql);

		$db->Query();

		return true;
	}

	/**
	 * Retrieves likes statistics
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLikeStats($dates, $userId)
	{
		$db = ES::db();
		$likes = array();

		foreach ($dates as $date) {
			// Registration date should be Y, n, j
			$date	= ES::date( $date )->format( 'Y-m-d' );

			$query 		= array();
			$query[] 	= 'SELECT `a`.`id`, COUNT( `a`.`id`) AS `cnt` FROM `#__social_likes` AS a';
			$query[]	= 'WHERE `a`.`created_by`=' . $db->Quote( $userId );
			$query[]	= 'AND DATE_FORMAT( `a`.`created`, GET_FORMAT( DATE , "ISO") ) = ' . $db->Quote( $date );
			$query[]    = 'group by a.`created_by`';


			$query 		= implode( ' ' , $query );
			$sql		= $db->sql();
			$sql->raw( $query );

			$db->setQuery( $sql );

			$items				= $db->loadObjectList();

			// There is nothing on this date.
			if( !$items )
			{
				$likes[]	= 0;
				continue;
			}

			foreach( $items as $item )
			{
				$likes[]	= $item->cnt;
			}
		}

		// Reset the index.
		$likes 	= array_values( $likes );

		return $likes;
	}

	/**
	 * $uuid - the unique id of the liked item
	 * $uType - the item type being liked - stream type (status, groups, photos ), comment etc.
	 *
	 * return - int
	 */

	public function getLikesCount($uid, $type, $options = array())
	{
		$likes = $this->getItems($uid, $type, $options);
		$likeCount = count($likes);

		return (empty($likeCount)) ? 0 : $likeCount;
	}

	/**
	 * Retrieve the join block query
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	private function getJoinBlockQuery($tblAlias, $options = array())
	{
		$query = [];

		$db = ES::db();

		$clusterId = isset($options['clusterId']) && $options['clusterId'] ? $options['clusterId'] : 0;

		$query[] = ' LEFT JOIN `#__social_block_users` AS `bus`';
		$query[] = 'ON (';

		// target
		$tmp = '(' . $tblAlias . '.`id` = `bus`.`user_id` AND `bus`.`target_id` = ' . JFactory::getUser()->id;
		if ($clusterId) {
			$tmp .= "    and bus.target_id NOT IN (";
			$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $db->Quote($clusterId) . " and clsnd.`admin` = 1";
			$tmp .= "    )";
		}
		$tmp .=  ')';

		$query[] = $tmp;

		// actor
		$tmp = '(' . $tblAlias . '.`id` = `bus`.`target_id` AND `bus`.`user_id` = ' . JFactory::getUser()->id;
		if ($clusterId) {
			$tmp .= "    and bus.user_id NOT IN (";
			$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $db->Quote($clusterId) . " and clsnd.`admin` = 1";
			$tmp .= "    )";
		}
		$tmp .=  ')';
		$query[] = 'OR ' . $tmp;
		$query[] = ')';

		$query = implode(' ', $query);

		return $query;
	}

	/**
	 * Retrieve the where block query
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	private function getWhereBlockQuery($operator = null)
	{
		$query = ' bus.`id` IS NULL';

		if ($operator) {
			$query = $operator . $query;
		}

		return $query;
	}

	/**
	 * Retrieve the likes
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getItems($id, $type, $options = array(), $debug = false)
	{
		// Build the index for the like
		$key = $id . '.' . $type;

		$forceReload = isset($options['forceReload']) ? $options['forceReload'] : false;

		if (!isset(self::$cache[$key]) || $forceReload) {

			$db = ES::db();

			$query = "select a.* from `#__social_likes` as a";
			$query .= " INNER JOIN `#__users` as b on a.`created_by` = b.`id`";

			if (ES::isBlockEnabled()) {
				$query .= $this->getJoinBlockQuery('b', $options);
			}

			$conds = array();

			if ($type == 'stream') {
				$conds[] = 'a.`stream_id` = ' . $db->Quote($id);
			} else {
				$conds[] = 'a.`uid` = ' . $db->Quote($id);
				$conds[] = 'a.`type` = ' . $db->Quote($type);
			}

			$conds[] = 'b.`block` = 0';

			if (ES::isBlockEnabled()) {
				$conds[] = $this->getWhereBlockQuery();
			}

			// join the conditions
			$query .= ($conds) ? ' WHERE ' . implode(' AND ', $conds) : '';

			// set the ordering
			$query .= ' ORDER BY a.`id` DESC';

			$db->setQuery($query);

			$result = $db->loadObjectList();

			// Initialize the items at index
			self::$cache[$key] = array();

			// Pre-load the users for the liked items
			if ($result) {
				foreach ($result as $row) {
					$table = ES::table('Likes');
					$table->bind($row);

					$this->insertCache($key, $table);
				}
			}
		}

		$result = self::$cache[$key];
		$result = is_array($result) ? $result : array($result);

		return $result;
	}

	/**
	 * Retrieves likes for a particular item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLikes($id, $type, $options = array(), $debug = false)
	{
		$likes = $this->getItems($id, $type, $options, $debug);

		return $likes;
	}

	/**
	 * Retrieve the likes for notifications
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getNotificationLikes($id, $type, $excludeUsers, $limit, $debug = false)
	{
		// Build the index for the like
		$key = $id . '.' . $type . '.notifications';

		if (!isset(self::$cache[$key])) {

			$db = ES::db();

			$query = "select a.* from `#__social_likes` as a";
			$query .= " INNER JOIN `#__users` as b on a.`created_by` = b.`id`";

			if (ES::isBlockEnabled()) {
				$query .= $this->getJoinBlockQuery('b');
			}

			$conds = array();

			if ($type == 'stream') {
				$conds[] = 'a.`stream_id` = ' . $db->Quote($id);
			} else {
				$conds[] = 'a.`uid` = ' . $db->Quote($id);
				$conds[] = 'a.`type` = ' . $db->Quote($type);
			}

			$conds[] = 'a.`created_by` NOT IN (' . implode(',', $excludeUsers) . ')';
			$conds[] = 'b.`block` = 0';

			if (ES::isBlockEnabled()) {
				$conds[] = $this->getWhereBlockQuery();
			}

			// join the conditions
			$query .= ($conds) ? ' WHERE ' . implode(' AND ', $conds) : '';

			// set the ordering
			$query .= ' ORDER BY a.`id` DESC';

			// set limi
			$query .= ' LIMIT ' . $limit;


			$db->setQuery($query);

			$result = $db->loadObjectList();

			// Initialize the items at index
			self::$cache[$key] = array();

			// Pre-load the users for the liked items
			if ($result) {
				foreach ($result as $row) {
					$table = ES::table('Likes');
					$table->bind($row);

					$this->insertCache($key, $table);
				}
			}
		}

		$result = self::$cache[$key];
		$result = is_array($result) ? $result : array($result);

		return $result;
	}

	/**
	 * Retrieves user ids who liked the item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLikerIds($id, $type, $exclude = array())
	{
		$likes = $this->getItems($id, $type);

		$likers = array();

		if ($likes) {
			foreach ($likes as $like) {
				if ($exclude && !in_array($like->created_by, $exclude)) {
					$likers[] = $like->created_by;
				} else {
					$likers[] = $like->created_by;
				}
			}
		}

		return $likers;
	}

	/**
	 * Retrieves a list of reactions
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getReactions()
	{
		static $_cache = null;

		if (is_null($_cache)) {

			$db = ES::db();

			$query = array();
			$query[] = 'SELECT * FROM `#__social_reactions` WHERE `published`=' . $db->Quote(1);

			$sql = $db->sql();
			$sql->raw($query);

			$db->setQuery($sql);

			$rows = $db->loadObjectList();

			if (!$rows) {
				return $rows;
			}

			$reactions = array();

			foreach ($rows as $row) {
				$reactions[] = ES::reaction($row);
			}

			$_cache = $reactions;
		}

		return $_cache;
	}

	/**
	 * Retrieves a list of reactions and it's count for a particular item
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getReactionsResult($id, $type, $reaction = null, $requireInstance = true, $options = array())
	{
		$db = ES::db();
		$config = ES::config();

		$query = array();
		$query[] = 'SELECT a.*,';
		$query[] = '(SELECT count(1) from `#__social_likes` AS b';
		$query[] = 'INNER JOIN `#__users` AS u ON b.`created_by` = u.`id`';

		if (ES::isBlockEnabled()) {
			$query[] = $this->getJoinBlockQuery('u', $options);
		}

		$query[] = 'WHERE a.`action` = b.`reaction`';

		if ($type == 'stream') {
			$query[] = 'AND b.`stream_id`=' . $db->Quote($id);
		}

		if ($type != 'stream') {
			$query[] = 'AND b.`uid`=' . $db->Quote($id);
			$query[] = 'AND b.`type`=' . $db->Quote($type);
		}

		if ($reaction) {
			$query[] = 'AND b.`reaction`=' . $db->Quote($reaction);
		}

		$query[] = 'AND u.`block` = 0';

		if (ES::isBlockEnabled()) {
			// user block continue here
			// $query[] = 'AND bus.' . $db->nameQuote( 'id' ) . ' IS NULL';

			$query[] = 'AND ' . $this->getWhereBlockQuery();
		}

		$query[] = ') as `total` FROM `#__social_reactions` AS a';

		if ($reaction) {
			$query[] = 'WHERE a.`action` = ' . $db->Quote($reaction);
		}

		$query = implode(' ', $query);

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		if (!$rows || !$requireInstance) {
			return $rows;
		}

		$reactions = [];

		foreach ($rows as $row) {
			$reaction = ES::reaction($row);
			$reaction->setTotal($row->total);

			if (!$row->published) {
				continue;
			}

			$reactions[] = $reaction;
		}
		return $reactions;
	}

	/**
	 * Retrieves a list of reactions and it's count for a particular item
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getReactionsUsers($id, $type, $reaction = null, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$limit = isset($options['limit']) ? $options['limit'] : null;
		$nextstart = isset($options['nextstart']) ? $options['nextstart'] : 0;

		$query = [];
		$query[] = 'SELECT a.`created_by`, a.`react_as`, a.`params`  FROM `#__social_likes` AS a';

		if (ES::isBlockEnabled()) {
			$query[] = 'INNER JOIN `#__users` AS u';
			$query[] = 'ON a.`created_by` = u.`id`';
			$query[] = $this->getJoinBlockQuery('u', $options);

		}

		$glue = 'AND';

		if ($reaction) {
			$query[] = 'WHERE a.`reaction`=' . $db->Quote($reaction);
		} else {
			$glue = 'WHERE';
		}

		if ($type == 'stream') {
			$query[] = $glue . ' a.`stream_id`=' . $db->Quote($id);
			$glue = 'AND';
		}

		if ($type != 'stream') {
			$query[] = $glue . ' a.`uid`=' . $db->Quote($id);
			$query[] = 'AND a.`type`=' . $db->Quote($type);
		}

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = 'AND ' . $this->getWhereBlockQuery();
		}

		$query[] = 'ORDER BY a.`created` ASC';

		if ($limit) {
			$query[] = 'LIMIT ' . $nextstart . ',' . $limit;
		}

		$sql->raw($query);

		// echo $sql->debug(); exit;
		$db->setQuery($sql);

		$rows = $db->loadObjectList();

		if (!$rows) {
			return $rows;
		}

		$users = array();

		foreach ($rows as $row) {
			if ($row->react_as == SOCIAL_TYPE_PAGE) {
				$clusterId = ES::registry($row->params)->get('clusterId');
				$obj = ES::page($clusterId);
			} else {
				$obj = ES::user($row->created_by);
			}

			$obj->reaction = $reaction;
			$users[] = $obj;
		}

		return $users;
	}
	/**
	 * Retrieves the reaction of the user against a particular object
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUserReaction($id, $type, $userId, $streamId = null, $reactAs = 'user')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = [
			'SELECT `reaction` FROM `#__social_likes` AS a',
			'INNER JOIN `#__social_reactions` AS b',
			'ON a.`reaction` = b.`action`',
			'WHERE a.`created_by`=' . $db->Quote($userId),
			'AND a.`uid` = ' . $db->Quote($id),
			'AND a.`type` = ' . $db->Quote($type),
			'AND a.`react_as` = ' . $db->Quote($reactAs),
			'AND b.`published`=' . $db->Quote(1)
		];

		if ($streamId) {
			$query[] = 'AND `stream_id`=' . $db->Quote($streamId);
		}

		$sql->raw($query);

		$db->setQuery($sql);

		$row = $db->loadResult();

		if (!$row) {
			return false;
		}

		$reaction = ES::reaction($row);

		return $reaction;
	}

	/**
	 * Determines if a user has already liked an item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasLiked($id , $type , $userId, $useStreamId = false)
	{
		$likes = null;

		if ($useStreamId) {
			$likes = $this->getItems( $useStreamId, 'stream' );
		} else {
			$likes = $this->getItems( $id, $type );
		}

		if ($likes) {
			foreach ($likes as $like) {

				if ($like->created_by == $userId) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Inserts a new reaction
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function react($id, $type, $userId, $reaction = 'like', $streamId = null, $uri = '', $react_as = 'user', $params = array())
	{
		$table = ES::table('Likes');

		// There is instances where the user just updated their reaction
		$options = array('uid' => $id, 'type' => $type, 'created_by' => $userId, 'react_as' => $react_as);

		// Try to load the reaction instead
		$table->load($options);

		$table->uid = $id;
		$table->type = $type;
		$table->created_by = $userId;
		$table->stream_id = $streamId;
		$table->reaction = $reaction;
		$table->created = JFactory::getDate()->toSql();
		$table->uri = $uri;
		$table->react_as = $react_as;
		$table->params = $params;

		$state = $table->store();

		// If there's an error storing, log this down.
		if (!$state) {
			$this->setError($table->getError());
			return false;
		}

		// Updated the key
		$key = $id . '.' . $type;

		// Remove from the cache if it exists
		$this->removeCache($key, $userId, $react_as);
		$this->insertCache($key, $table);

		return $state;
	}

	/**
	 * Deprecated. Use @react instead
	 *
	 * @deprecated	2.1
	 */
	public function like($id, $type, $userId, $streamId = null)
	{
		return $this->react($id, $type, $userId, 'like', $streamId);
	}

	/**
	 * Deprecated. Use @withdraw instead
	 *
	 * @deprecated	2.1
	 */
	public function unlike($id, $type, $userId, $streamId = null)
	{
		return $this->withdraw($id, $type, $userId, $streamId);
	}

	/**
	 * Withdraws a reaction
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function withdraw($id, $type, $userId, $streamId = null, $reactAs = null)
	{
		$table = ES::table('Likes');
		$options = array('uid' => $id, 'type' => $type, 'created_by' => $userId);

		if ($streamId) {
			$options['stream_id'] = $streamId;
		}

		if ($reactAs) {
			$options['react_as'] = $reactAs;
		}

		$exists = $table->load($options);

		if (!$exists) {
			$this->setError('COM_EASYSOCIAL_LIKES_OBJECT_DOES_NOT_EXIST');
			return false;
		}

		// Delete the table now
		$state = $table->delete();

		// Set the error to the model.
		if (!$state) {
			$this->setError($table->getError());
			return false;
		}

		//update like static variable
		$key = $id . '.' . $type;
		$this->removeCache($key, $userId, $table->react_as);

		return $state;
	}

	/**
	 * Retrieve the user mode based on user's reaction on specific lapsed time.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUserMood($userId, $lapsed_period)
	{
		$db = ES::db();
		$now = ES::date()->toSql();

		$query = "SELECT a.`reaction`, count(1) AS `count`";
		$query .= " FROM `#__social_likes` AS `a`";
		$query .= " WHERE a.`created_by` = " . $db->Quote($userId);
		$query .= " AND a.`created` > DATE_SUB(" . $db->Quote($now) . ", INTERVAL " . $lapsed_period . " DAY)";
		$query .= " group by a.`reaction`";
		$query .= " order by `count` desc, a.`created` desc";
		$query .= " limit 1";

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Retrieve a list of user reactions user data.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getReactionsGDPR($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		$limit = $this->normalize($options, 'limit', false);
		$userId = $this->normalize($options, 'userid', null);
		$exclusion = $this->normalize($options, 'exclusion', null);

		$query[] = 'SELECT a.* FROM ' . $db->nameQuote('#__social_likes') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->qn('#__social_stream') . ' AS b';
		$query[] = 'ON a.' . $db->qn('stream_id') . ' = b.' . $db->qn('id');
		$query[] = 'WHERE a.' . $db->nameQuote('created_by') . ' = ' . $db->Quote($userId);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND a.' . $db->nameQuote('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		// We can only fetch reactions made on the stream
		$query[] = 'AND a.' . $db->nameQuote('stream_id') . '!=' . $db->Quote(0);

		if ($limit) {
			$totalQuery = implode(' ', $query);

			// Set the total number of items.
			$this->setTotal($totalQuery, true);
		}

		// Get the limitstart.
		$limitstart = JFactory::getApplication()->input->get('limitstart', 0, 'int');
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0 );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$query[] = "LIMIT $limitstart, $limit";

		$query = implode(' ', $query);

		$sql->clear();
		$sql->raw($query);

		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();

		return $result;
	}
}
