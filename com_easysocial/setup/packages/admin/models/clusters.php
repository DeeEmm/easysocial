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

class EasySocialModelClusters extends EasySocialModel
{
	public function __construct( $config = array() )
	{
		parent::__construct( 'clusters' , $config );
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		$filter 	= $this->getUserStateFromRequest( 'state' , 'all' );
		$ordering 	= $this->getUserStateFromRequest( 'ordering' , 'ordering' );
		$direction	= $this->getUserStateFromRequest( 'direction' , 'ASC' );

		$this->setState( 'state' , $filter );


		parent::initStates();

		// Override the ordering behavior
		$this->setState( 'ordering' , $ordering );
		$this->setState( 'direction' , $direction );
	}

	/**
	 * Saves the ordering of profiles
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveOrder( $ids , $ordering )
	{
		$table 	= ES::table( 'Profile' );
		$table->reorder();
	}

	/**
	 * Removes all owners from the nodes
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeOwners($clusterId, $adminRights = true)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->update('#__social_clusters_nodes');
		$sql->set('owner', 0);

		if (!$adminRights) {
			$sql->set('admin', 0);
		}

		$sql->where('cluster_id', $clusterId);

		$db->setQuery( $sql );

		return $db->Query();
	}

	/**
	 * Given the cluster id, get the type of the cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getType($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->column('cluster_type', 'type');
		$sql->where('id', $id);

		$db->setQuery($sql);
		$type = $db->loadResult();

		return $type;
	}

	/**
	 * Retrieves the total number of clusters created by a user given the cluster type and the user id
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalCreated( $creatorId , $creatorType , $clusterType )
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__social_clusters' );
		$sql->column( 'COUNT(1)' );
		$sql->where( 'creator_uid' , $creatorId );
		$sql->where( 'creator_type' , $creatorType );
		$sql->where( 'cluster_type' , $clusterType );

		$sql->where( 'state' , SOCIAL_CLUSTER_PUBLISHED );
		$db->setQuery( $sql );

		$total	= $db->loadResult();

		return $total;
	}

	/**
	 * Deletes all node associations between the cluster and the node item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteNodeAssociation($clusterId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_clusters_nodes');
		$sql->where('cluster_id', $clusterId);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Deletes node
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteNode($clusterId, $nodeId, $nodeType)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_clusters_nodes');
		$sql->where('cluster_id', $clusterId);
		$sql->where('uid', $nodeId);
		$sql->where('type', $nodeType);

		$db->setQuery($sql);

		$state = $db->Query();

		return $state;
	}

	/**
	 * Retrieve a list of associated group or page events
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function getAssociatedEvents($clusterId, $clusterType)
	{
		$db = ES::db();
		$query = [];

		$clusterTypeColumn = $clusterType === SOCIAL_TYPE_GROUP ? 'group_id' : 'page_id';

		$query[] = "SELECT `cluster_id` AS `id` FROM `#__social_events_meta`";
		$query[] = "WHERE " . $db->nameQuote($clusterTypeColumn) . " = " . $db->Quote($clusterId);

		$query = implode(" ", $query);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Deletes node's email digest subscription
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function deleteNodeEmailDigestSubscription($clusterId, $nodeId)
	{
		$db = ES::db();

		$query = "delete from `#__social_clusters_subscriptions`";
		$query .= " where `cluster_id` = " . $db->Quote($clusterId);
		$query .= " and `user_id` = " . $db->Quote($nodeId);

		$db->setQuery($query);
		$state = $db->Query();

		return $state;
	}


	/**
	 * Gets the total number of nodes in a cluster category
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The cluster's category id.
	 * @return
	 */
	public function getTotalNodes($categoryId , $options = array())
	{
		$db = ES::db();

		$excludeBlocked = isset($options['excludeblocked'] ) ? $options[ 'excludeblocked' ] : 0;

		$query = array();

		$query[] = "select count(1) from `#__social_clusters` as a";

		if (ES::isBlockEnabled() && $excludeBlocked) {
			$query[] = $this->getJoinBlockQuery('a', 'created_by', 'id');
		}

		$query[] = " WHERE a.category_id = " . $db->Quote($categoryId);
		$query[] = " AND a.state = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$types = isset($options['types']) ? $options['types'] : '';
		if ($types) {
			$types = ES::makeArray($types);
			$query[] = " AND a.type IN (" . implode(",", $types) . ")";
		}

		if (ES::isBlockEnabled() && $excludeBlocked) {
			$query[] = " AND " . $this->getWhereBlockQuery();
		}

		//join the query
		$query = implode(" ", $query);

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Check if the cluster alias exist
	 *
	 * @since  1.2
	 * @access public
	 */
	public function clusterAliasExists($alias, $exclude = null, $type = SOCIAL_TYPE_GROUP)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('alias', $alias);
		$sql->where('cluster_type', $type);

		if (!empty($exclude)) {
			$sql->where('id', $exclude, '!=');
		}

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Check if cluster title exist
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function clusterTitleExists($title, $type, $clusterId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('title', $title);
		$sql->where('cluster_type', $type);

		if ($clusterId) {
			$sql->where('id', $clusterId, '!=');
		}

		$db->setQuery($sql->getTotalSql());
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Generates a unique title
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getUniqueTitle($title, $type, $clusterId = false)
	{
		$config = ES::config();

		if ($config->get('seo.clusters.allowduplicatetitle')) {
			return $title;
		}

		$i = 2;

		$tmp = $title;

		do {
			$exists = $this->clusterTitleExists($title, $type, $clusterId);

			if ($exists) {
				$title = $tmp . ' ' . $i++;
			}
		} while ($exists);

		return $title;
	}

	/**
	 * Check if the cluster category alias exist
	 *
	 * @since  1.2
	 * @access public
	 */
	public function clusterCategoryAliasExists($alias, $exclude = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');
		$sql->where('alias', $alias);

		if (!empty($exclude))
		{
			$sql->where('id', $exclude, '!=');
		}

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Delete activity streams from the cluster
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function deleteClusterStream($clusterId, $clusterType)
	{
		$db = ES::db();

		$query = "delete a, b, c, d, e";
		$query .= " from `#__social_stream` as a";
		$query .= " left join `#__social_stream_item` as d on a.`id` = d.`uid`";
		// remove all associated comments;
		$query .= " left join `#__social_comments` as b on a.`id` = b.`stream_id`";
		// remove all associated reaction;
		$query .= " left join `#__social_likes` as c on a.`id` = c.`stream_id`";
		// remove all associated hashtags;
		$query .= " left join `#__social_stream_tags` as e on a.`id` = e.`stream_id`";
		$query .= " where a.`cluster_id` = " . $db->Quote($clusterId);
		$query .= " and a.`cluster_type` = " . $db->Quote($clusterType);

		$db->setQuery($query);
		$db->query();

		// now we delete any 'left overs' reactions on those deleted comments
		$query = "delete a";
		$query .= " from `#__social_likes` as a";
		$query .= "	left join `#__social_comments` as b on a.`uid` = b.`id`";
		$query .= " where a.`type` like " . $db->Quote('comments.%');
		$query .= " and b.`id` is null";

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * delete notifications from this cluster.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function deleteClusterNotifications($clusterId, $clusterType, $clusterContextType)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'delete from `#__social_notifications`';
		$query .= ' where (`uid` = ' . $db->Quote($clusterId) . ' and `type` = ' . $db->Quote($clusterType) .')';
		$query .= ' OR (`type` = ' . $db->Quote($clusterContextType) . ' and `context_ids` = ' . $db->Quote($clusterId) . ')';

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();
		return $state;
	}

	public function preloadClusters($clusters)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select * from `#__social_clusters` where id in (" . implode(",", $clusters) . ")";

		$sql->raw($query);

		$db->setQuery($sql);

		$results = $db->loadObjectList();
		return $results;
	}

	/**
	 * Determines if the user is a member of the cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMember($userId, $clusterId)
	{
		$db 	= ES::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $clusterId);
		$sql->where('state', SOCIAL_GROUPS_MEMBER_PUBLISHED);

		$db->setQuery($sql);

		$isMember 	= $db->loadResult() > 0;

		return $isMember;
	}

	/**
	 * Determines if the user is an owner of the cluster
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function isOwner($userId, $clusterId)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $clusterId);
		$sql->where('owner', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);

		$isOwner = $db->loadResult() > 0;

		return $isOwner;
	}

	/**
	 * Retrieve the join block query
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	private function getJoinBlockQuery($tblAlias, $userColAlias = 'uid', $clusterColAlias = 'cluster_id', $options = array())
	{
		$query = [];
		$db = ES::db();

		$query[] = ' LEFT JOIN `#__social_block_users` AS `bus`';
		$query[] = 'ON (';

		// target
		$tmp = '(' . $tblAlias . '.' . $db->nameQuote($userColAlias) . ' = `bus`.`user_id` AND `bus`.`target_id` = ' . JFactory::getUser()->id;
		$tmp .= "    and bus.target_id NOT IN (";
		$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $tblAlias . "." . $db->nameQuote($clusterColAlias) . " and clsnd.`admin` = 1";
		$tmp .= "    )";
		$tmp .= ')';

		$query[] = $tmp;

		// actor
		$tmp = '(' . $tblAlias . '.' . $db->nameQuote($userColAlias) . ' = `bus`.`target_id` AND `bus`.`user_id` = ' . JFactory::getUser()->id;
		$tmp .= "    and bus.user_id NOT IN (";
		$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $tblAlias . "." . $db->nameQuote($clusterColAlias) . " and clsnd.`admin` = 1";
		$tmp .= "    )";
		$tmp .= ')';
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
	 * Determines if the user is a member of the cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalMembers($clusterId, $options = array())
	{
		$db = ES::db();

		$query = array();
		$query[] = "select count(1) from `#__social_clusters_nodes` as a";
		$query[] = " INNER JOIN `#__users` as u on a.`uid` = u.`id`";
		$query[] = " INNER JOIN `#__social_profiles_maps` as upm on a.`uid` = upm.`user_id`";
		$query[] = " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.id";

		if (ES::isBlockEnabled()) {
			$query[] = $this->getJoinBlockQuery('a');
		}

		$conds = array();
		$conds[] = "a.`cluster_id` = " . $db->Quote($clusterId);
		$conds[] = "a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		// Whe the user isn't blocked
		$conds[] = "u.`block` = " . $db->Quote(0);

		$membersOnly = isset($options['membersOnly']) ? $options['membersOnly'] : false;
		if ($membersOnly) {
			$conds[] = "a.`admin` = " . $db->Quote(0);
		}

		// for group to exclude owner option.
		$excludeOnwer = isset($options['excludeOwner']) ? $options['excludeOwner'] : false;
		if ($excludeOnwer) {
			$conds[] = "a.`owner` = " . $db->Quote(0);
		}

		if (ES::isBlockEnabled()) {
			$conds[] = $this->getWhereBlockQuery();
		}

		// join main query
		$query = implode(" ", $query);

		// join conditions
		$query .= $conds ? " WHERE " . implode(" AND ", $conds) : "";

		// echo $query;exit;


		$db->setQuery($query);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * get members / nodes for a cluster
	 * for now used in rest api
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getNodes($uid, $utype, $options = array())
	{
		$db = ES::db();
		$config = ES::config();

		$query = "select a.*";
		$query .= " FROM `#__social_clusters_nodes` as a";

		if (ES::isBlockEnabled()) {
			$query .= $this->getJoinBlockQuery('a');
		}

		$query .= " INNER JOIN `#__users` as u ON a.`uid` = u.`id` and u.`block` = 0";
		$query .= " WHERE a.`cluster_id` = " . $db->Quote($uid);

		$state = isset($options['state']) ? $options['state'] : '';
		if ($state) {
			$query .= " AND a.`state` = " . $db->Quote($state);
		}

		// Determine if we should retrieve admins only
		$adminOnly = isset($options['admin']) ? $options['admin'] : '';
		if ($adminOnly) {
			$query .= " AND a.`admin` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		}

		// Determines if we should retrieve members only
		$membersOnly = isset($options['members']) && $options['members'] ? true : false;
		if ($membersOnly) {
			$query .= " AND a.`admin` = " . $db->Quote('0');
		}

		// where
		if (ES::isBlockEnabled()) {
			$query .= " and " . $this->getWhereBlockQuery();
		}

		if (isset($options['ordering'])) {
			$direction = isset($options['direction']) ? $options['direction'] : 'asc';
			$query .= " order by " . $options['ordering'] . " " . $direction;
		}

		// Should we apply pagination
		$limit = isset($options['limit']) ? $options['limit'] : 0;
		$limit = (int) $limit;

		if ($limit) {

			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '0';
			$limitstart = (int) $limitstart;

			$query .= " LIMIT $limitstart, $limit";
		}

		$db->setQuery($query);
		$users = $db->loadObjectList();

		// Bind guests user
		if ($utype === SOCIAL_TYPE_EVENT) {
			$users = $this->bindTable('EventGuest', $users);
		}

		return $users;
	}


	/**
	 * get number of cluster a user need to review.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPendingReview($userId, $clusterType)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select count(1) from `#__social_clusters` as a";
		// $query .= "		inner join `#__social_clusters_reject` as b on a.`id` = b.`cluster_id`";
		$query .= " where a.`creator_uid` = " . $db->Quote($userId);
		$query .= " and a.`creator_type` = " . $db->Quote(SOCIAL_TYPE_USER);
		$query .= " and a.`cluster_type` = " . $db->Quote($clusterType);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_CLUSTER_DRAFT);

		$sql->raw($query);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * get number of cluster that pending moderation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalPendingModeration($options)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select count(1) from `#__social_clusters` as a";
		$query .= " where a.`creator_type` = " . $db->Quote(SOCIAL_TYPE_USER);

		if (isset($options['filter']) && $options['filter'] != 'all') {
			$query .= " and a.`cluster_type` = " . $db->Quote($options['filter']);
		}

		$query .= " and a.`state` IN(" . $db->Quote(SOCIAL_CLUSTER_PENDING) . ", " . $db->Quote(SOCIAL_CLUSTER_UPDATE_PENDING) . ")";

		$sql->raw($query);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Get cluster that in pending moderation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getPendingModeration($options)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select * from `#__social_clusters` as a";
		$query .= " where a.`creator_type` = " . $db->Quote(SOCIAL_TYPE_USER);

		if (isset($options['filter']) && $options['filter'] != 'all') {
			$query .= " and a.`cluster_type` = " . $db->Quote($options['filter']);
		}

		$query .= " and a.`state` IN(" . $db->Quote(SOCIAL_CLUSTER_PENDING) . ", " . $db->Quote(SOCIAL_CLUSTER_UPDATE_PENDING) . ")";

		$sql->raw($query);

		$db->setQuery($sql);
		$results = $db->loadObjectList();

		$clusters = array();
		// Load the cluster library
		foreach ($results as $result) {
			$clusters[] = ES::cluster($result->cluster_type, $result->id);
		}

		return $clusters;
	}

	/**
	 * retrieve the cluster's rejected reason.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRejectedReasons($clusterId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select b.* from `#__social_clusters` as a";
		$query .= "		inner join `#__social_clusters_reject` as b on a.`id` = b.`cluster_id`";
		$query .= " where a.`id` = " . $db->Quote($clusterId);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_CLUSTER_DRAFT);
		$query .= " order by b.`id` desc";

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();
		return $results;
	}


	/**
	 * Searches for a user's friend.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function searchNodes($clusterId, $term, $type, $options = array())
	{
		$config = ES::config();
		$db	 = ES::db();

		$query = "select a." . $db->nameQuote('id') . " from " . $db->nameQuote('#__users') . " as a";
		$query .= " inner join " . $db->nameQuote('#__social_clusters_nodes') . " as b on a.`id` = b.`uid` and b.`type` = 'user'";

		if (ES::isBlockEnabled()) {
			// user block
			$query .= " LEFT JOIN " . $db->nameQuote( '#__social_block_users' ) . " as bus";

			$query .= ' ON (';
			$query .= ' a.' . $db->nameQuote( 'id' ) . ' = bus.' . $db->nameQuote( 'user_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote( 'target_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id );
			$query .= ') OR (';
			$query .= ' a.' . $db->nameQuote( 'id' ) . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( JFactory::getUser()->id ) ;
			$query .= ')';
		}

		$query .= " where a." . $db->nameQuote('block') ." = " . $db->Quote('0');
		$query .= "	and b." . $db->nameQuote('state') . " = " . $db->Quote(SOCIAL_USER_STATE_ENABLED);
		$query .= "	and b." . $db->nameQuote('cluster_id') . " = " . $db->Quote($clusterId);


		if (ES::isBlockEnabled()) {
			// user block continue here
			$query .= " AND bus." . $db->nameQuote('id') . " IS NULL";
		}

		if ($type == SOCIAL_FRIENDS_SEARCH_NAME || $type == SOCIAL_FRIENDS_SEARCH_REALNAME) {
			$query .= " AND a." . $db->nameQuote('name') . " LIKE " . $db->Quote('%' . $term . '%');
		}

		if ($type == SOCIAL_FRIENDS_SEARCH_USERNAME) {
			$query .= " AND a." . $db->nameQuote('username') . " LIKE " . $db->Quote('%' . $term . '%');
		}

		if (isset($options['exclude'] ) && $options['exclude']) {
			$excludeIds = '';

			if (!is_array($options['exclude'])) {
				$options['exclude'] = explode(',', $options['exclude']);
			}

			foreach ($options['exclude']  as $id) {
				$excludeIds .= ( empty( $excludeIds ) ) ? $db->Quote( $id ) : ', ' . $db->Quote( $id );
			}

			$query .= " AND a." . $db->nameQuote('id') . " NOT IN (" . $excludeIds . ")";
		}

		$limit = isset($options['limit']) ? $options['limit'] : false;
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '10';

		if ($limit) {

			// get the total count.
			$replaceStr = "SELECT a." . $db->nameQuote('id') . " FROM ";
			$totalSQL = str_replace($replaceStr, "SELECT COUNT(1) FROM ", $query);

			$db->setQuery($totalSQL);
			$this->total = $db->loadResult();

			// now we append the limit
			$query .= " LIMIT $limitstart, $limit";
		}

		$db->setQuery($query);
		$result = $db->loadColumn();

		if (!$result) {
			return false;
		}

		$members = ES::user($result);

		return $members;
	}

	public function deleteUserStreams($clusterId, $clusterType, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "delete a, b from `#__social_stream` as a";
		$query .= "		inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
		$query .= " where a.`actor_id` = " . $db->Quote($userId);
		$query .= " and a.`cluster_id` = " . $db->Quote($clusterId);
		$query .= " and a.`cluster_type` = " . $db->Quote($clusterType);

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}

	public function getFilters($clusterId, $clusterType, $userId = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select * from `#__social_stream_filter`';
		$query .= ' where `uid` = ' . $db->Quote($clusterId);
		$query .= ' and `utype` = ' . $db->Quote($clusterType);

		// Always search for global
		$query .= ' and `global` = ' . $db->Quote(1);

		if ($userId) {
			$query .= ' and `user_id` = ' . $db->Quote($userId);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$items = array();

		if ($result) {
			foreach ($result as $row) {
				$streamFilter = ES::table('StreamFilter');
				$streamFilter->bind($row);

				$items[] = $streamFilter;
			}
		}

		return $items;
	}


	/**
	 * update stream's cluster access
	 *
	 * @since  2.1
	 * @access public
	 */
	public function updateStreamClusterAccess($clusterId, $clusterType, $accessType)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'update `#__social_stream` set `cluster_access` = ' . $db->Quote($accessType);
		$query .= " where `cluster_id` = " . $db->Quote($clusterId);
		$query .= " and `cluster_type` = " . $db->Quote($clusterType);

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();
		return $state;
	}

	/**
	 * Determine if this user was invited to join cluster
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function isInvited($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_friends_invitations');
		$sql->column('uid', '', 'distinct');
		$sql->where('registered_id', $userId);
		$sql->where('utype', array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT), 'IN');

		$db->setQuery($sql);
		$total = $db->loadColumn();

		return $total;
	}

}
