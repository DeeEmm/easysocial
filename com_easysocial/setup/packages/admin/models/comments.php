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

class EasySocialModelComments extends EasySocialModel
{
	public $table 	= '#__social_comments';
	static $_counts = array();
	static $_data = array();

	public function __construct($config = array())
	{
		parent::__construct('comments', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('filter', 'all');

		$this->setState('filter', $filter);

		parent::initStates();
	}

	/**
	 * Retrieves a list of comments
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getComments($options = array())
	{
		// Available options
		// element
		// uid
		// start
		// limit
		// order
		// direction

		// Define the default parameters
		$defaults = array(
							'start' => 0,
							'limit' => 5,
							'order' => 'lft',
							'includeReplies' => 1,
							'direction'	=> 'asc',
						);

		$options = array_merge($defaults, $options);

		$db = ES::db();
		$config = ES::config();
		$query = [];

		$useCache = true;

		// SELECT
		$query[] = 'SELECT `a`.* FROM `#__social_comments` AS `a`';
		$query[] = 'INNER JOIN `#__social_comments` AS `b` ON a.`element` = b.`element` and a.`uid` = b.`uid`';

		if (ES::isBlockEnabled()) {
			$query[] = $this->getJoinBlockQuery($options);
		}

		$where = [];

		// WHERE
		if (isset($options['stream_id']) && $options['stream_id']) {
			$where[] = ' a.`stream_id` = ' . $db->Quote($options['stream_id']);
		}

		if (ES::isBlockEnabled()) {
			$where[] = $this->getWhereBlockQuery();
		}

		if (isset( $options['element'])) {
			$where[] = ' a.`element` = ' . $db->Quote($options['element']);
		}

		if (isset($options['uid'])) {
			$where[] = ' a.`uid` = ' . $db->Quote($options['uid']);
		}

		if (isset($options['commentid'])) {
			$useCache = false;
			$where[] = ' a.`id` >= ' . $db->Quote($options['commentid']);
		}

		if (isset($options['parentid'])) {
			if ($options['parentid']) {
				$useCache = false;
			}

			$where[] = ' a.`parent` = ' . $db->Quote($options['parentid']);
		}

		$where[] = 'a.`lft` BETWEEN b.`lft` AND b.`rgt`';

		if (isset($options['since'])) {
			$where[] = ' a.`created` > ' . $db->Quote($options['since']);
		}

		if ($options['order'] != 'created') {
			$useCache = false;
		}

		if (count($where) > 0) {
			$whereQuery = 'WHERE ';
			$whereQuery .= (count($where) == 1) ? $where[0] : implode(' AND ', $where);

			$query[] = $whereQuery;
		}

		$query[] = ' GROUP BY a.`id`';

		// ORDER
		$query[] = 'ORDER BY ' . $db->nameQuote($options['order']) . ' ' . $options['direction'];

		// LIMIT
		if(!empty($options['limit'])) {
			$query[] = 'LIMIT ' . $options['start'] . ' ,' . $options['limit'];
		}

		$comments = false;
		$loadSQL  = true;

		$key = '';

		if ($useCache) {

			if (isset($options['stream_id'])) {
				// lets try to get the count from the static variable.
				$key = $options['stream_id'] . '.' . 'stream';
			} else {
				$key = $options['uid'] . '.' . $options['element'];
			}

			if (isset(self::$_data[$key])) {
				$loaded = self::$_data[$key];

				if( $loaded )
				{
					if ($options['direction'] == 'asc') {
						asort($loaded);
					} else {
						arsort( $loaded );
					}

					if (!empty($options['limit'])) {
						$loaded = array_slice($loaded, $options['start'], $options['limit']);
					}

					$comments = $loaded;
					$loadSQL = false;
				}
			}
		}

		$query = implode(' ', $query);

		// debug
		// echo $query;
		// echo '<br><br>';
		// exit;

		if ($loadSQL) {
			$db->setQuery($query);
			$comments = $db->loadObjectList();
		}

		if ($comments === false) {
			return false;
		}

		$tables = [];

		foreach ($comments as $comment) {
			$table = ES::table('comments');
			$table->bind($comment);
			$tables[] = $table;

			if ($options['includeReplies']) {
				// Retrieve the total childs to be shown initally that been set
				if ($table->isParent() && $table->child > 0) {
					$childs = $this->getChilds($table->id, 0, $config->get('comments.totalreplies'), $options);

					$tables = array_merge($tables, $childs);
				}
			}
		}

		return $tables;
	}

	/**
	 * Retrieve the total child comments of a parent comment
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function getTotalChilds($parentId, $options = [])
	{
		$db = ES::db();
		$query[] = 'SELECT COUNT(1) FROM `#__social_comments` AS `a`';

		if (ES::isBlockEnabled()) {
			$query[] = $this->getJoinBlockQuery($options);
		}

		$query[] = 'WHERE `a`.`parent` = ' . $db->Quote($parentId);

		if (ES::isBlockEnabled()) {
			$query[] = $this->getWhereBlockQuery('AND');
		}

		$db->setQuery($query);

		$total = $db->loadResult();
		return $total;
	}

	/**
	 * Retrieve a list of child comments
	 *
	 * @since	4.0.1
	 * @access	public
	 */
	public function getChilds($parentId, $start = 0, $limit = null, $options = [])
	{
		$db = ES::db();
		$query[] = 'SELECT `a`.* FROM `#__social_comments` AS `a`';

		if (ES::isBlockEnabled()) {
			$query[] = $this->getJoinBlockQuery($options);
		}

		$query[] = 'WHERE `a`.`parent` = ' . $db->Quote($parentId);

		if (ES::isBlockEnabled()) {
			$query[] = $this->getWhereBlockQuery('AND');
		}

		$query[] = 'ORDER BY a.`lft` ASC';

		if ($limit) {
			$query[] = 'LIMIT ' . $start . ', ' . $limit;
		}

		$query = implode(' ', $query);

		$db->setQuery($query);
		$childs = $db->loadObjectList();

		if (!$childs) {
			return [];
		}

		$totalChilds = $this->getTotalChilds($parentId, $options);

		// TODO: Use array_key_last() instead when ES enforced to PHP 7 onwards
		$lastIndex = $limit ? $limit - 1 : count($childs) - 1;

		$hasMore = $totalChilds - 1;
		$comments = [];

		foreach ($childs as $index => $child) {
			$comment = ES::table('comments');
			$comment->bind($child);
			$comment->last = false;
			$comment->hasMore = false;

			if ($index == $lastIndex) {
				$comment->last = true;

				// To determine if there is more child comments after this child comment
				if ($totalChilds > $limit) {
					$comment->hasMore = true;
				}
			}

			$comments[] = $comment;
		}

		return $comments;
	}

	/**
	 * Retrieve the join block query
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getJoinBlockQuery($options = array())
	{
		$query = [];

		$db = ES::db();

		$clusterId = isset($options['clusterId']) && $options['clusterId'] ? $options['clusterId'] : 0;

		$query[] = 'LEFT JOIN `#__social_block_users` AS `bus`';
		$query[] = 'ON (';

		// target
		$tmp = '(`a`.`created_by` = `bus`.`user_id` AND `bus`.`target_id` = ' . JFactory::getUser()->id;
		if ($clusterId) {
			$tmp .= "    and bus.target_id NOT IN (";
			$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $db->Quote($clusterId) . " and clsnd.`admin` = 1";
			$tmp .= "    )";
		}
		$tmp .=  ')';

		$query[] = $tmp;

		// actor
		$tmp = '(`a`.`created_by` = `bus`.`target_id` AND `bus`.`user_id` = ' . JFactory::getUser()->id;
		if ($clusterId) {
			$tmp .= "    and bus.user_id NOT IN (";
			$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $db->Quote($clusterId) . " and clsnd.`admin` = 1";
			$tmp .= "    )";
		}
		$tmp .=  ')';
		$query[] = 'OR ' . $tmp;
		$query[] = ')';

		$query = implode(' ', $query);

		// $sql->leftjoin( '#__social_block_users' , 'bus');
		// $sql->on( 'a.created_by' , 'bus.user_id' );
		// $sql->on( 'bus.target_id', JFactory::getUser()->id);
		// $sql->isnull('bus.id');


		// $sql->leftjoin( '#__social_block_users' , 'bus2');
		// $sql->on( 'a.created_by' , 'bus2.target_id' );
		// $sql->on( 'bus2.user_id', JFactory::getUser()->id );
		// $sql->isnull('bus2.id');

		return $query;
	}

	/**
	 * Retrieve the where block query
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getWhereBlockQuery($operator = null)
	{
		$query = ' `bus`.`id` IS NULL';

		if ($operator) {
			$query = $operator . $query;
		}

		return $query;
	}

	/**
	 * Retrieve a list of comments from the site for back end listings
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_comments');

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('comment', '%' . $search . '%', 'LIKE');
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		$this->setTotal($sql->getTotalSql());

		// Get the list of users
		$result = $this->getData($sql, true);

		if (!$result) {
			return $result;
		}

		$comments = array();

		foreach ($result as $row) {
			$comment = ES::table('Comments');
			$comment->bind($row);

			$comments[] = $comment;
		}

		return $comments;
	}

	/**
	 * Get total comments made by a user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalCommentsBy($userId = 0)
	{
		$db = ES::db();
		$user = ES::user($userId);

		$query = 'SELECT COUNT(*) AS total';
		$query .= ' FROM ' . $db->nameQuote('#__social_comments');
		$query .= ' WHERE ' . $db->nameQuote('created_by') . '=' . $user->id;

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result->total;
	}

	/**
	 * Retrieves the comment statistics for a particular poster
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCommentStats( $dates , $userId )
	{
		$db 		= ES::db();
		$comments	= array();

		foreach( $dates as $date )
		{
			// Registration date should be Y, n, j
			$date	= ES::date( $date )->format( 'Y-m-d' );

			$query 		= array();
			$query[] 	= 'SELECT `a`.`id`, COUNT( `a`.`id`) AS `cnt` FROM `#__social_comments` AS a';
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
				$comments[]	= 0;
				continue;
			}

			foreach( $items as $item )
			{
				$comments[]	= $item->cnt;
			}
		}

		// Reset the index.
		$comments 	= array_values( $comments );

		return $comments;
	}

	/**
	 * Retrieves a list of missing comments given a list of comment ids.
	 * This is useful to detect for changes made on the table
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMissingItems($ids = array())
	{
		$db = ES::db();
		$query = array();
		$query[] = 'SELECT b.' . $db->qn('id') . ' FROM ' . $db->qn('#__social_comments') . ' AS a';
		$query[] = 'RIGHT JOIN (';

		foreach ($ids as $id) {
			$query[] = 'SELECT ' . $db->Quote($id) . ' AS ' . $db->qn('id');

			if (next($ids) !== false) {
				$query[] = 'UNION';
			}
		}

		$query[] = ') AS b';
		$query[] = 'ON a.' . $db->qn('id') . '= b.' . $db->qn('id');
		$query[] = 'WHERE a.' . $db->qn('id') . ' IS NULL';

		$query = implode(' ', $query);
		$sql = $db->sql();
		$sql->raw($query);

		$db->setQuery($sql);

		$missing = $db->loadColumn();

		return $missing;
	}

	public function getCommentCount( $options = array() )
	{
		$key = '';

		$force = isset($options['force']) ? $options['force'] : false;

		if (!$force && isset($options['stream_id']) && isset($options['element'])) {

			// lets try to get the count from the static variable.
			$key = $options['stream_id'] . '.stream.' . $options['uid'] . '.' . $options['element'];

			if (isset(self::$_counts[$key])) {
				return self::$_counts[ $key ];
			}
		}

		// We only use static variable if passed in options is element and uid
		// It is possible that other options are passed in and we will need to count separately
		if (!$force && isset($options['element']) && isset($options['uid'])) {

			// lets try to get the count from the static variable.
			$key = $options['uid'] . '.' . $options['element'];

			if (isset(self::$_counts[$key])) {
				return self::$_counts[ $key ];
			}
		}

		$clusterId = isset($options['clusterId']) && $options['clusterId'] ? $options['clusterId'] : 0;

		$db = ES::db();

		$query = "select count(1) from `#__social_comments` as a";

		if (ES::isBlockEnabled()) {
			$query .= ' ' . $this->getJoinBlockQuery($options);
		}

		$cond = array();

		if (isset( $options['stream_id'] ) && $options['stream_id']) {
			$cond[] = 'a.`stream_id` = ' . $db->Quote($options['stream_id']);
		}

		if (isset($options['element'])) {
			$cond[] = 'a.`element` = ' . $db->Quote($options['element']);
		}

		if (isset($options['uid'])) {
			$cond[] = 'a.`uid` = ' . $db->Quote($options['uid']);
		}

		if (isset($options['parentid'])) {
			$cond[] = 'a.`parent` = ' . $db->Quote($options['parentid']);
		}

		if (ES::isBlockEnabled()) {
			$cond[] = $this->getWhereBlockQuery();
		}


		// incase some replies are under blocked users parent comment, we need to exclude these replies in the count.
		if (ES::isBlockEnabled()) {

			$my = ES::user();
			// Get a list of blocked users for this user
			$blockModel = ES::model('Blocks');
			$blockedUserIds = $blockModel->getBlockedUsers($my->id, true);

			if ($blockedUserIds) {
				$cond[] = 'NOT EXISTS (select b.`id` from `#__social_comments` as b where a.`parent` = b.`id` and b.`created_by` IN (' . implode($blockedUserIds) . '))';
			}
		}

		$query .= $cond ? ' WHERE ' . implode(' AND ', $cond) : '';

		// echo $query;
		// echo '<br><br>';
		// exit;

		$db->setQuery($query);
		$count = $db->loadResult();

		//lets save into static variable for later reference.
		if (!$force && $key) {
			self::$_counts[$key] = $count;
		}

		return $count;
	}

	/**
	 * Allows caller to remove comments
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function deleteComments($uid, $element)
	{
		$db = ES::db();

		// Get a list of ids that needs to be deleted
		$query = array();
		$query[] = 'SELECT ' . $db->qn('id') . ' FROM ' . $db->qn('#__social_comments');
		$query[] = 'WHERE ' . $db->qn('uid') . '=' . $db->Quote($uid);
		$query[] = 'AND ' . $db->qn('element') . '=' . $db->Quote($element);

		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!$ids) {
			return;
		}

		// Need to delete the reactions made on the comment notifications as well
		foreach ($ids as $id) {
			$notifications = ES::model('Notifications');
			$notifications->deleteNotificationsWithUid($id, 'comments.user.like');
		}

		$ids = implode(',', $ids);

		// Lets get any left over attachments from the comments first. #3864
		$query = 'SELECT * from `#__social_files`';
		$query .= ' WHERE `uid` IN(' . $ids . ') AND `type` = ' . $db->Quote('comments');

		// Set maximum to 100 files only here. The rest of the files will get deleted with cron.
		$query .= ' limit 100';

		$db->setQuery($query);
		$files = $db->loadObjectList();

		if ($files) {
			foreach ($files as $row) {
				$file = ES::table('File');
				$file->bind($row);

				$file->delete();
			}
		}

		// Delete the comments
		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__social_comments');
		$query[] = 'WHERE ' . $db->qn('id') . ' IN (' . $ids . ')';

		$db->setQuery($query);
		$db->Query();

		// Delete reactions related to the comments
		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__social_likes');
		$query[] = 'WHERE ' . $db->qn('uid') . ' IN (' . $ids . ')';
		$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote('comments.user.like');

		$db->setQuery($query);
		$db->Query();

		// Delete reports related to the comments
		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__social_reports');
		$query[] = 'WHERE ' . $db->qn('uid') . ' IN (' . $ids . ')';

		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Deprecated. Use @deleteComments instead
	 *
	 * @deprecated	2.1.11
	 */
	public function deleteCommentBlock($uid, $element)
	{
		return $this->deleteComments($uid, $element);
	}

	/**
	 * Allows caller to retrieve a lists of user who added comment on specific stream item.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getParticipants($uid, $element)
	{
		$db	= ES::db();
		$config = ES::config();

		$query = array();

		$query[] = 'SELECT x.`created_by` FROM (';

		$query[] = 'SELECT a.*';
		$query[] = 'FROM `#__social_comments` AS a';

		if (ES::isBlockEnabled(ES::user())) {
			$query[] = 'LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';
			$query[] = 'ON (';
			$query[] = 'a.' . $db->nameQuote('created_by') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = 'AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(ES::user()->id);
			$query[] = ') OR (';
			$query[] = 'a.' . $db->nameQuote('created_by') . ' = bus.' . $db->nameQuote('target_id');
			$query[] = 'AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(ES::user()->id);
			$query[] = ')';
		}

		$query[] = 'WHERE a.' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid);
		$query[] = 'AND a.' . $db->nameQuote('element') . ' = ' . $db->Quote($element);

		// user block continue here
		if (ES::isBlockEnabled(ES::user())) {
			$query[] = " AND bus." . $db->nameQuote('id') . " IS NULL";
		}

		$query[] = 'ORDER BY a.' . $db->nameQuote('id') . ' DESC';

		$query[] = ') AS x';
		$query[] = 'GROUP BY x.`created_by`';
		$query[] = 'ORDER BY x.`id` DESC';

		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}

	public function getLastSibling( $parent )
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select( $this->table )
			->where( 'parent', $parent )
			->order( 'lft', 'desc' )
			->limit( 1 );

		$db->setQuery( $sql );

		$result = $db->loadObject();

		return $result;
	}

	public function updateBoundary( $node )
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "UPDATE `{$this->table}` SET `lft` = `lft` + 2 WHERE `lft` > {$node}";

		$sql->raw( $query );

		$db->setQuery( $sql );

		$db->query();

		$query = "UPDATE `{$this->table}` SET `rgt` = `rgt` + 2 WHERE `rgt` > {$node}";

		$sql->raw( $query );

		$db->setQuery( $sql );

		$db->query();

		return true;
	}

	public function setStreamCommentBatch($data)
	{

		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		// Retrieve the stream model
		$model = ES::model('Stream');
		$dataset = array();

		$clusterIds = array();

		// Go through each of the items
		foreach ($data as $item) {
			// Get related items
			$uid = $item->id;

			// If there's no context_id, skip this.
			if (!$uid) {
				continue;
			}

			// need to pre-fill the data 1st.
			$group = ($item->cluster_id) ? $item->cluster_type : SOCIAL_APPS_GROUP_USER;

			if ($item->cluster_id) {
				$clusterIds[] = $item->cluster_id;
			}

			$key = $uid . '.stream';
			self::$_data[ $key ] = array();

			$dataset[] = $uid;
		}

		// lets build the sql now.
		if( $dataset )
		{
			$query = "select x.* from `#__social_comments` as x";

			if (ES::isBlockEnabled()) {
				// user block
				$query .= ' LEFT JOIN ' . $db->nameQuote( '#__social_block_users' ) . ' as bus';
				$query .= ' ON (';
				$query .= ' x.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
				$query .= ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );

				// exclude cluster's admin user #4448
				$query .= 'and NOT EXISTS (';
				$query .= ' select clsnd.`uid` from `#__social_clusters_nodes` as clsnd';
				$query .= ' inner join `#__social_stream` as ss on clsnd.`cluster_id` = ss.`cluster_id`';
				$query .= ' where ss.`id` = x.`stream_id` and clsnd.`uid` = bus.`target_id` and clsnd.`admin` = 1)';

				$query .= ') OR (';
				$query .= ' x.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
				$query .= ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
				$query .= ')';

			}

			$query .= ' where x.stream_id IN (' . implode(',', $dataset) . ')';

			if (ES::isBlockEnabled()) {
				$query .= ' AND bus.' . $db->nameQuote( 'id' ) . ' IS NULL';
			}

			// echo $query;
			// exit;

			$sql->raw( $query );
			$db->setQuery( $sql );

			$result = $db->loadObjectList();

			if( $result )
			{
				$cids = array();

				foreach( $result as $rItem )
				{
					$cids[] = $rItem->id;
					//$key = $rItem->uid . '.' . $rItem->element;
					//
					$key = $rItem->stream_id . '.stream';

					self::$_data[ $key ][$rItem->created] = $rItem;
				}

				// based on the comments id, we need to pre fetch the likes for commetns
				$like = ES::model( 'Likes' );
				$like->setCommentLikesBatch( $result );


				// lets do the same for comment tagging.
				$tags = ES::model( 'Tags' );
				$tags->setTagBatch( $cids, 'comments' );
			}
		}
	}

	public function setStreamCommentCountBatch($data)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		// Retrieve the stream model
		$model 	= ES::model( 'Stream' );

		$dataset = array();

		// var_dump($data);exit;

		$clusterIds = array();

		// Go through each of the items
		foreach ($data as $item) {
			// Get related items
			$uid = $item->id;

			// If there's no context_id, skip this.
			if (!$uid) {
				continue;
			}

			// need to pre-fill the data 1st.
			$group = ($item->cluster_id) ? $item->cluster_type : SOCIAL_APPS_GROUP_USER;
			$key = $uid . '.stream';


			if ($item->cluster_id) {
				$clusterIds[] = $item->cluster_id;
			}

			self::$_counts[$key] = 0;

			$dataset[] = $uid;
		}

		// lets build the sql now.
		if ($dataset) {
			$query = "select count(1) as `cnt`, x.`uid`, x.`element`, x.`stream_id` from `#__social_comments` as x";

			if (ES::isBlockEnabled()) {
				// user block
				$query .= ' LEFT JOIN ' . $db->nameQuote( '#__social_block_users' ) . ' as bus';

				$query .= ' ON (';
				$query .= ' x.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
				$query .= ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );

				// exclude cluster's admin user #4448
				$query .= 'and NOT EXISTS (';
				$query .= ' select clsnd.`uid` from `#__social_clusters_nodes` as clsnd';
				$query .= ' inner join `#__social_stream` as ss on clsnd.`cluster_id` = ss.`cluster_id`';
				$query .= ' where ss.`id` = x.`stream_id` and clsnd.`uid` = bus.`target_id` and clsnd.`admin` = 1)';

				$query .= ') OR (';
				$query .= ' x.' . $db->nameQuote( 'created_by' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
				$query .= ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
				$query .= ')';

			}


			$query .= ' where stream_id IN (' . implode(',', $dataset) . ')';
			$query .= ' AND x.`parent` = ' . $db->Quote(0);

			if (ES::isBlockEnabled()) {
				$query .= ' AND bus.' . $db->nameQuote( 'id' ) . ' IS NULL';
			}

			$query .= " group by x.`element`, x.`uid`, x.`stream_id`";

			// echo $query;
			// exit;

			$sql->raw( $query );
			$db->setQuery( $sql );

			$result = $db->loadObjectList();

			if ($result) {
				foreach ($result as $rItem) {
					// $key = $rItem->uid . '.' . $rItem->element;
					$key = $rItem->stream_id . '.stream.' . $rItem->uid . '.' . $rItem->element;

					self::$_counts[$key] = $rItem->cnt;
				}
			}

		}
	}

	/**
	 * Retrieves all comments posted by specific user, in conjuction with GDPR compliance.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getCommentGDPR($userId, $options)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.`id`, a.`element`, a.`uid`, a.`comment`, a.`stream_id`, a.`created_by`, a.`created`, b.`cluster_id`, b.`actor_id`, b.`cluster_type` as `type`';
		$query[] = 'FROM ' . $db->quoteName('#__social_comments') . ' AS a';
		$query[] = 'LEFT JOIN ' . $db->quoteName('#__social_stream') . ' AS b';
		$query[] = 'ON a.`stream_id` = b.`id`';
		$query[] = 'WHERE a.`created_by` = ' . $db->Quote($userId);

		$exclusion = $this->normalize($options, 'exclusion', array());

		if ($exclusion) {
			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND a.' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		$cluster = $this->normalize($options, 'clusters', '');

		if ($cluster) {
			$query[] = 'AND b.`cluster_type` = ' . $db->Quote($cluster);

			$id = (int) $this->normalize($options, 'id', 0);

			if ($id) {
				$query[] = 'AND b.`cluster_id` = ' . $db->Quote($id);
			}
		}

		$limit = (int) $this->normalize($options, 'limit', 20);

		$query[] = 'ORDER BY a.`id` DESC';
		$query[] = 'LIMIT ' . $limit;

		$query = implode(' ', $query);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		foreach ($result as &$row) {
			$element = explode('.', $row->element);

			$row->type = $element[0];

			// Reformat articles
			if ($row->type == 'article') {
				$table = JTable::getInstance('content');
				$table->load($row->uid);

				$row->actor = $table->get('title');
			}

			if ($row->stream_id == '0' && is_null($row->cluster_id)) {

				// Reformat news/announcements
				// News/announcements only appear in clusters.
				// So, we'll need to assign the appropriate clusters.
				if ($row->type == 'news') {
					$table = ES::table('clusternews');
					$table->load($row->uid);

					$row->actor = $table->title;
					$row->cluster_id = $table->cluster_id;

					if (is_null($row->actor_id)) {
						$row->actor_id = $table->created_by;
					}
				}

				if ($row->type == 'albums') {
					$table = ES::table('album');
					$table->load($row->uid);

					$row->actor = $table->title;

					if (is_null($row->actor_id)) {
						$row->actor_id = $table->user_id;
					}
				}

				if ($row->type == 'blog') {
					$table = EB::table('post');
					$table->load($row->uid);

					$row->actor = $table->title;

					if (is_null($row->actor_id)) {
						$row->actor_id = $table->created_by;
					}
				}

				// Set the cluster_id to 0 if there is none.
				$row->cluster_id = $row->cluster_id ? $row->cluster_id : '0';
			}
		}

		return $result;
	}
}
