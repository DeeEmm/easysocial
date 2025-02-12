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

class EasySocialModelPages extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('pages', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('state', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');
		$type = $this->getUserStateFromRequest('type', 'all');
		$category = $this->getUserStateFromRequest('category', -1);

		$this->setState('category', $category);
		$this->setState('type', $type);
		$this->setState('state', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Retrieves the total number of pending pages from the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPendingCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('COUNT(1)', 'count');
		$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);
		$sql->where('a.state', array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Get the list of Pages from the site
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('a.*');
		$sql->column('b.title', 'categoryTitle');

		// check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('a.title', '%' . $search . '%', 'LIKE');
		}

		// Determines if we should load the pending pages
		$pending = $this->normalize($options, 'pending', false);
		$state = $this->getState('state');

		if ($state != 'all') {
			$sql->where('a.state', $state);
		} else {
			if ($pending) {
				$sql->where('a.state', array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');
			} else {
				$sql->where('a.state',  array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'NOT IN');
			}
		}

		$type = $this->getState('type');

		if ($type != 'all') {
			$sql->where('a.type', $type);
		}

		$category = $this->getState('category');

		if ($category && $category != -1) {
			$sql->where('a.category_id', $category);
		}

		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction');
			$sql->order($ordering, $direction);
		}

		// Join the category we need to order by category
		$sql->join('#__social_clusters_categories', 'b');
		$sql->on('b.id', 'a.category_id');

		// This must always be checked
		$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return false;
		}

		$pages = array();

		foreach ($result as $row) {
			$page = ES::page($row->id);

			$pages[] = $page;
		}

		return $pages;
	}

	/**
	 * Retrieves the meta data of a list of pages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMeta($ids = array())
	{
		static $loaded = array();

		// Store items that needs to be loaded
		$loadItems = array();

		foreach ($ids as $id) {
			$id = (int) $id;

			if (!isset($loaded[$id])) {
				$loadItems[] = $id;

				// Initialize this with a false value first.
				$loaded[$id] = false;
			}
		}

		// Determines if there is new items to be loaded
		if ($loadItems) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_clusters', 'a');
			$sql->column('a.*');
			$sql->column('b.small');
			$sql->column('b.medium');
			$sql->column('b.large');
			$sql->column('b.square');
			$sql->column('b.avatar_id');
			$sql->column('b.photo_id');
			$sql->column('b.storage', 'avatarStorage');
			$sql->column('f.id', 'cover_id');
			$sql->column('f.uid', 'cover_uid');
			$sql->column('f.type', 'cover_type');
			$sql->column('f.photo_id', 'cover_photo_id');
			$sql->column('f.cover_id'	, 'cover_cover_id');
			$sql->column('f.x', 'cover_x');
			$sql->column('f.y', 'cover_y');
			$sql->column('f.modified', 'cover_modified');
			$sql->join('#__social_avatars', 'b');
			$sql->on('b.uid', 'a.id');
			$sql->on('b.type', 'a.cluster_type');
			$sql->join('#__social_covers', 'f');
			$sql->on('f.uid', 'a.id');
			$sql->on('f.type', 'a.cluster_type');

			if (count($loadItems) > 1) {
				$sql->where('a.id', $loadItems, 'IN');
				$sql->group('a.id');
			} else {
				$sql->where('a.id', $loadItems[0]);
			}

			$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);

			$db->setQuery($sql);

			$pages = $db->loadObjectList();

			if ($pages) {
				foreach ($pages as $page) {
					$loaded[$page->id] = $page;
				}
			}
		}

		// Format the return result
		$data = array();

		foreach ($ids as $id) {
			$data[] = $loaded[$id];
		}

		return $data;
	}

	/**
	 * Retrieves a list of followers from a particular page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMembers($pageId, $options = array())
	{
		static $cache = array();

		ksort($options);

		$optionskey = serialize($options);

		$load = array();
		if (is_array($pageId)) {
			foreach($pageId as $pid) {
				if (! isset($cache[$pid][$optionskey])) {
					$load[] = $pid;
				}
			}
		} else {

			if (! isset($cache[$pageId][$optionskey])) {
				$load[] = $pageId;
			}
		}

		$reload = isset($options['reload']) ? $options['reload'] : false;

		if ($load || $reload) {

			// prefill empty array
			if (count($load) > 1) {
				foreach($load as $ld) {
					$cache[$ld][$optionskey] = array();
				}
			} else {
				if ($reload && !isset($load[0])) {
					$load[] = $pageId;
				}

				$cache[$load[0]][$optionskey] = array();
			}

			$db = ES::db();
			$my = ES::user();

			$q = array();
			$q[] = 'select a.* from `#__social_clusters_nodes` as a';
			$q[] = ' INNER JOIN `#__users` as u on a.`uid` = u.`id`';

			// exclude esad users
			$q[] = ' INNER JOIN `#__social_profiles_maps` as upm on u.`id` = upm.`user_id`';
			$q[] = ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

			if (ES::isBlockEnabled()) {
				$q[] = $this->getUserBlockingJoinQuery($my, 'bus', 'uid', 0, array('clusterColumnAlias' => 'cluster_id'));
			}


			$q[] = ' WHERE u.`block` = 0';

			// By specific pages
			if (count($load) > 1) {
				$q[] = ' AND a.`cluster_id` IN (' . implode(',', $load) . ')';
			} else {
				$q[] = ' AND a.`cluster_id` = ' . $db->Quote($load[0]) ;
			}


			$state = isset($options['state']) ? $options['state'] : '';
			if ($state) {
				$q[] = ' AND a.`state` = ' . $db->Quote($state);
			}

			// Determine if we should retrieve admins only
			$adminOnly = isset($options['admin']) ? $options['admin'] : '';
			if ($adminOnly) {
				$q[] = ' AND a.`admin` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
			}

			// Determine if we should retrieve members only
			$membersOnly = isset($options['member']) ? $options['member'] : '';
			if ($membersOnly) {
				$q[] = ' AND a.`admin` = ' . $db->Quote(SOCIAL_STATE_UNPUBLISHED);
			}

			// Determines if we should retrieve followers only
			$followersOnly = isset($options['followers']) && $options['followers'] ? true : false;
			if ($followersOnly) {
				$q[] = ' AND a.`admin` = ' . $db->Quote(0);
			}

			// Determine if we want to exclude this.
			$exclude = isset($options['exclude']) ? $options['exclude'] : '';
			if ($exclude) {
				$q[] = ' AND a.`uid` != ' . $db->Quote($exclude);
			}

			// Search followers
			$search = isset($options['search']) ? $options['search'] : '';

			if ($search) {
				$usernameType = ES::config()->get('users.displayName');

				if ($usernameType == SOCIAL_FRIENDS_SEARCH_NAME || $usernameType == SOCIAL_FRIENDS_SEARCH_REALNAME) {
					$q[] = ' AND u.`name` LIKE ' . $db->Quote('%' . $search . '%');
				}

				if ($usernameType == SOCIAL_FRIENDS_SEARCH_USERNAME) {
					$q[] = ' AND u.`username` LIKE ' . $db->Quote('%' . $search . '%');
				}
			}

			if (ES::isBlockEnabled()) {
				$q[] = $this->getUserBlockingClauseQuery($my);
			}


			// prepare the row count query
			$countSQL = implode(' ', $q);

			if (!empty($options['ordering'])) {
				$direction = !empty($options['direction']) ? $options['direction'] : 'asc';
				$q[] = ' ORDER BY ' . $options['ordering'] . ' ' . $direction;
			}


			// prepare the query.
			$query = implode(' ', $q);


			// echo $query;
			// echo '<br><br>';

			$result = array();

			// Should we apply pagination
			$limit = isset($options['limit']) ? $options['limit'] : '';
			if ($limit) {

				$this->setState('limit', $limit);

				// Get the limitstart.
				$limitstart = $this->getUserStateFromRequest('limitstart', 0);
				$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

				$this->setState('limitstart', $limitstart);

				// Set the total records for pagination.
				$this->setTotal($countSQL, true);

				// Get the final result
				$result = $this->getData($query);
			} else {
				// Run the main query to get the list of users
				$db->setQuery($query);
				$result = $db->loadObjectList();
			}

			if ($result) {
				foreach($result as $item) {
					$cache[$item->cluster_id][$optionskey][] = $item;
				}
			}

		}

		if (is_array($pageId)) {
			// when this is an array of page ids, we know we are doign preload. lets return true.
			return true;
		}

		$result = $cache[$pageId][$optionskey];
		$usersObject = isset($options['users']) ? $options['users'] : true;
		$users = array();

		if ($usersObject) {
			//preload users
			$userIds = array();

			foreach ($result as $row) {
				$userIds[] = $row->uid;
			}

			ES::user($userIds);

			foreach ($result as $row) {
				$user = ES::user($row->uid);
				$users[] = $user;
			}
		} else {
			// return plain object lists since we no longer need to bind to jtable for members checking.
			$users = $result;
		}

		return $users;
	}

	/**
	 * Retrieve the total pending followers for the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalPendingFollowers($pageId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->innerjoin('#__users', 'b');
		$sql->on('a.uid', 'b.id');

		$sql->where('a.cluster_id', $pageId);
		$sql->where('a.state', SOCIAL_PAGES_MEMBER_PENDING);
		$sql->where('b.block', SOCIAL_JOOMLA_USER_UNBLOCKED);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Generates a unique alias for the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUniqueAlias($title, $exclude = null)
	{
		// Pass this back to Joomla to ensure that the permalink would be safe.
		$alias = ES::generatePermalink($title);
		$model = ES::model('Clusters');

		// This is used if the alias is already exists
		$i = 2;

		// Set this to a temporary alias
		$tmp = $alias;

		do {
			$exists = $model->clusterAliasExists($alias, $exclude, SOCIAL_TYPE_PAGE);

			if ($exists) {
				$alias	= $tmp . '-' . $i++;
			}

		} while ($exists);

		return $alias;
	}

	/**
	 * Retrieves a list of pages from the site
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');

		// Check the search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Determines if we should load the pending page
		$pending = isset($options['pending']) ? $options['pending'] : false;

		if ($pending) {
			$sql->where('state', array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');
		} else {
			$sql->where('state',  array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'NOT IN');
		}


		// This must always be checked
		$sql->where('cluster_type', SOCIAL_TYPE_PAGE);

		// Set the total records
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return false;
		}

		$pages = array();

		foreach ($result as $row) {
			$page = ES::page($row->id);
			$pages[] = $page;
		}

		return $pages;
	}

	/**
	 * Get a list of pages from the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPages($filter = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('DISTINCT(a.id)');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.creator_uid' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.creator_uid' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);

		// Test to filter by category
		$category = $this->normalize($filter, 'category', '');

		if ($category) {
			$sql->where('a.category_id', $category, 'in');
		}

		// Test to filter by creator
		$uid = $this->normalize($filter, 'uid', '');

		if ($uid) {
			$sql->where('a.creator_uid', $uid);
			$sql->where('a.creator_type', SOCIAL_TYPE_USER);
		}

		// Test to filter by invitation
		$invited = $this->normalize($filter, 'invited', '');

		if ($invited !== '') {

			$sql->join('#__social_clusters_nodes', 'b', 'INNER');
			$sql->on('b.cluster_id', 'a.id');

			$sql->where('b.state', SOCIAL_PAGES_MEMBER_INVITED);
			$sql->where('b.uid', $invited);
		}

		// Test to filter featured items
		$featured = $this->normalize($filter, 'featured', '');

		if ($featured !== '') {
			$sql->where('a.featured', $featured);
		}

		// Test to filter liked items
		$liked = $this->normalize($filter, 'liked', '');

		if ($liked !== '') {
			$sql->join('#__social_clusters_nodes', 'b', 'INNER');
			$sql->on('b.cluster_id', 'a.id');

			$sql->where('b.state', SOCIAL_PAGES_MEMBER_PUBLISHED);
			$sql->where('b.uid', $liked);
		}

		// Test if there is an inclusion
		$inclusion = $this->normalize($filter, 'inclusion', '');

		if ($inclusion !== '') {
			$sql->where('a.id', $inclusion, 'in');
		}

		// Test to filter all page types
		$types = isset($filter['types']) ? $filter['types'] : '';

		if ($types != 'all') {

			$userid = isset($filter['userid']) ? $filter['userid'] : ES::user()->id;

			// currentuser type is currently used in pages module.
			if ($types === 'currentuser' && $userid) {

				$sql->innerjoin('#__social_clusters_nodes', 'nodes');
				$sql->on('a.id', 'nodes.cluster_id');
				$sql->where('nodes.uid', $userid);

			} else if ($types === 'user' && $userid) {

				$sql->leftjoin('#__social_clusters_nodes', 'nodes');
				$sql->on('a.id', 'nodes.cluster_id');
				$sql->where('(');
				$sql->where('a.type', array(SOCIAL_PAGES_PRIVATE_TYPE, SOCIAL_PAGES_PUBLIC_TYPE), 'IN');
				$sql->where('(', '', '', 'OR');
				$sql->where('a.type', SOCIAL_PAGES_INVITE_TYPE);
				$sql->where('nodes.uid', $userid);
				$sql->where(')');
				$sql->where(')');

			} else {
				$sql->where('a.type', array(SOCIAL_PAGES_PRIVATE_TYPE, SOCIAL_PAGES_PUBLIC_TYPE), 'IN');
			}
		}

		// Search page title
		$search = $this->normalize($filter, 'search', '');

		if ($search) {
			$sql->where('a.title', '%' . $search . '%', 'LIKE');
		}


		// Test to filter published / unpublished pages
		$state = isset($filter['state']) ? $filter['state'] : SOCIAL_STATE_PUBLISHED;
		$sql->where('a.state', $state);

		// Determines if there are ordering options supplied
		$ordering = isset($filter['ordering']) ? $filter['ordering'] : 'latest';

		$cntSQL = '';

		if ($ordering == 'followers') {
			$sql->join('#__social_clusters_nodes', 'f', 'INNER');
			$sql->on('f.cluster_id', 'a.id');
			$sql->on('f.state', SOCIAL_PAGES_MEMBER_PUBLISHED);

			// Since this is default behaviour admin shouldn't consider as follower, so we need to skip this.
			$sql->where('f.admin', 0);

			// We should not fetch banned users as well
			$sql->join('#__users', 'u');
			$sql->on('f.uid', 'u.id');

			// When the user isn't blocked
			$sql->where('u.block', 0);

			// lets get the sql without the order by condition.
			$cntSQL = $sql->getSql();

			$sql->order('COUNT(f.`id`)', 'DESC');
			$sql->group('a.id');
		} else {

			// lets get the sql without the order by condition.
			$cntSQL = $sql->getSql();

			if ($ordering == 'popular') {
				$sql->order('a.hits', 'DESC');
			}

			if ($ordering == 'latest') {
				$sql->order('a.created', 'DESC');
			}

			if ($ordering == 'random') {
				$sql->order('', 'DESC', 'RAND');
			}

			if ($ordering == 'name') {
				$sql->order('a.title', 'ASC');
			}
		}

		$limit = $this->normalize($filter, 'limit', '');

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			//$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = $this->input->get('limitstart', 0, 'int');

			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total records for pagination.
			$this->setTotal($cntSQL, true);

			// Get the list of ids
			$ids = $this->getData($sql);
		} else {
			$db->setQuery($sql);

			$ids = $db->loadObjectList();
		}

		if (!$ids) {
			return $ids;
		}

		$pages = array();

		foreach ($ids as $id) {
			$pages[] = ES::page($id->id);
		}

		return $pages;
	}

	/**
	 * Retrieves the total number of pages in the system.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPages($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$checkUserBlock = isset($options['userblock']) ? $options['userblock'] : true;

		$sql->select('#__social_clusters', 'a');
		$sql->column('a.id', 'id', 'count distinct');

		// Check for blocked users
		if ($checkUserBlock && ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.creator_uid' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.creator_uid' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.state', SOCIAL_CLUSTER_PUBLISHED);
		$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);

		$types = isset($options['types']) ? $options['types'] : '';

		if ($types != 'all') {
			if ($types === 'user') {
				$userid = isset($options['userid']) ? $options['userid'] : ES::user()->id;

				$sql->leftjoin('#__social_clusters_nodes', 'nodes');
				$sql->on('a.id', 'nodes.cluster_id');

				$sql->where('(');
				$sql->where('a.type', array(SOCIAL_PAGES_PRIVATE_TYPE, SOCIAL_PAGES_PUBLIC_TYPE), 'IN');
				$sql->where('(', '', '', 'OR');
				$sql->where('a.type', SOCIAL_PAGES_INVITE_TYPE);
				$sql->where('nodes.uid', $userid);
				$sql->where(')');
				$sql->where(')');
			} else {

				// Get the current logged in user
				$my = ES::user();

				if (!$my->isSiteAdmin()) {
					$sql->where('a.type', array(SOCIAL_PAGES_PRIVATE_TYPE, SOCIAL_PAGES_PUBLIC_TYPE), 'IN');
				}
			}
		}

		// Test to check against category id
		$category = isset($options['category_id']) ? $options['category_id'] : '';

		if ($category) {
			$sql->where('a.category_id', $category, 'IN');
		}

		// Test to filter featured items
		$featured 	= isset($options['featured']) ? $options['featured'] : '';

		if ($featured !== '') {
			$sql->where('a.featured', $featured);
		}

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Retrieves the total number of pages a user has created.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalCreatedPages($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('COUNT(1)');

		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.creator_uid', $userId);
		$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of unpublished pages a user has created.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalUnpublishedPages($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('COUNT(1)');

		$sql->where('a.state', SOCIAL_STATE_UNPUBLISHED);

		// For the site admin need to count all the unpublished page because site admin has permission to access it.
		if (!ES::user()->isSiteAdmin()) {
			$sql->where('a.creator_uid', $userId);
		}

		$sql->where('a.cluster_type', SOCIAL_TYPE_PAGE);

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of pages a user is participating in.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalParticipatedPages($userId, $filter = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');

		// Ensure that the page is published
		$sql->join('#__social_clusters', 'b', 'INNER');
		$sql->on('b.id', 'a.cluster_id');

		// Check for blocked users
		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'b.creator_uid' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'b.creator_uid' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.uid', $userId);
		$sql->where('a.type', SOCIAL_TYPE_USER);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('b.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('b.cluster_type', SOCIAL_TYPE_PAGE);

		$types = isset($filter['types']) ? $filter['types'] : '';

		if ($types) {
			if ($types == 'invited') {
				$sql->where('b.type', SOCIAL_PAGES_INVITE_TYPE);
			} else {
				$sql->where('b.type', array(SOCIAL_PAGES_PRIVATE_TYPE, SOCIAL_PAGES_PUBLIC_TYPE), 'IN');
			}
		}

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of invited pages in the system.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalInvites($userId)
	{
		static $_cache = array();

		if (!isset($_cache[$userId])) {

			$options = array('invited' => $userId, 'types' => 'user');
			$invitedPage = $this->getPages($options);
			$total = count($invitedPage);
			$_cache[$userId] = $total;
		}

		return $_cache[$userId];
	}

	/**
	 * Retrieves the total number of invited followers.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTotalInvitedFollowers($pageId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->where('a.cluster_id', $pageId);
		$sql->where('a.state', SOCIAL_PAGES_MEMBER_INVITED);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Retrieves a list of cluster categories on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCreatableCategories($profileId, $parentOnly = false, $containerOnly = false)
	{
		static $_cache = array();

		$idx = $profileId . '-' . (int) $parentOnly;

		if (!isset($_cache[$idx])) {

			$db = ES::db();
			$sql = $db->sql();

			$query = array();

			$query[] = "SELECT DISTINCT `a`.* FROM `#__social_clusters_categories` AS `a`";
			$query[] = "LEFT JOIN `#__social_clusters_categories_access` AS `b`";
			$query[] = "ON `a`.`id` = `b`.`category_id`";
			$query[] = "WHERE `a`.`type` = 'page'";
			$query[] = "AND `a`.`state` = '1'";

			// We want to get parent only
			if ($parentOnly) {
				$query[] = "AND `a`.`parent_id` = '0'";
			} else {
				$query[] = "AND `a`.`container` = '0'";
			}

			if (!ES::user()->isSiteAdmin()) {
				$query[] = "AND (`b`.`profile_id` = " . $profileId;
				$query[] = "OR `a`.`id` NOT IN (SELECT `category_id` FROM `#__social_clusters_categories_access`))";
			}

			$query[] = "ORDER BY `a`.`ordering`";

			$db->setQuery($sql->raw(implode(' ', $query)));


			$result = $db->loadObjectList();

			$categories = $this->bindTable('PageCategory', $result);

			$_cache[$idx] = $categories;
		}

		return $_cache[$idx];
	}

	/**
	 * Create new page on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function createPage(SocialTableStepSession &$session)
	{
		$config = ES::config();

		// Set the basic page details here.
		// Other page details should be fulfilled by the respective custom fields.
		ES::import('admin:/includes/page/page');

		$page = new SocialPage();
		$page->creator_uid = ES::user()->id;
		$page->creator_type = SOCIAL_TYPE_USER;
		$page->category_id = $session->uid;
		$page->cluster_type = SOCIAL_TYPE_PAGE;
		$page->hits = 0;
		$page->created = ES::date()->toSql();

		// Load the page category
		$category = ES::table('PageCategory');
		$category->load($session->uid);

		// Generate a unique key for this page which serves as a password
		$page->key = md5(JFactory::getDate()->toSql() . ES::user()->password . uniqid());

		// Load up the values which the user inputs
		$param = ES::get('Registry');

		// Bind the JSON values.
		$param->bind($session->values);

		// Convert the data into an array of result.
		$data = $param->toArray();

		$model = ES::model('Fields');

		// Get all published fields for the page.
		$fields = $model->getCustomFields(array('workflow_id' => $category->getWorkflow()->id, 'group' => SOCIAL_TYPE_PAGE, 'visible' => SOCIAL_PAGES_VIEW_REGISTRATION));

		// Pass in data and new user object by reference for fields to manipulate
		$args = array(&$data, &$page);

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		// @trigger onRegisterBeforeSave
		$lib = ES::getInstance('Fields');

		// Get the trigger handler
		$handler = $lib->getHandler();

		// Trigger onRegisterBeforeSave
		$errors = $lib->trigger('onRegisterBeforeSave', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args, array($handler, 'beforeSave'));

		// If there are any errors, throw them on screen.
		if (is_array($errors)) {
			if (in_array(false, $errors, true)) {
				$this->setError($errors);
				return false;
			}
		}

		// If there is still no alias generated, we need to automatically build one for the page
		if (!$page->alias) {
			$page->alias = $this->getUniqueAlias($page->getName());
		}

		// If pages required to be moderated, unpublish it first.
		$my = ES::user();
		$page->state = $my->getAccess()->get('pages.moderate') ? SOCIAL_CLUSTER_PENDING : SOCIAL_CLUSTER_PUBLISHED;

		// If the creator is a super admin, they should not need to be moderated
		if ($my->isSiteAdmin()) {
			$page->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$page, &$my, true);

		// @trigger: onPageBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onPageBeforeSave', $triggerArgs);

		// Custom trigger to retrieve all published custom fields that may or may not be visible on the registration. See #800
		$publishedFields = $model->getCustomFields(array('uid' => $session->uid, 'group' => SOCIAL_TYPE_PAGE));
		$lib->trigger('onCustomPageBeforeSave', SOCIAL_FIELDS_GROUP_PAGE, $publishedFields, $args, array($handler, 'beforeSave'));

		// Let's try to save the page now.
		$state = $page->save();

		// If there's a problem saving the page object, set error message.
		if (!$state) {
			$this->setError($page->getError());
			return false;
		}

		// Send e-mail notification to site admin to approve / reject the page.
		if ($my->getAccess()->get('pages.moderate') && !$my->isSiteAdmin()) {
			$this->notifyAdminsModeration($page);
		} else {
			// If the creator is a site admin, we don't need to notify the admins
			if (!$my->isSiteAdmin()) {
				$this->notifyAdmins($page);
			}
		}

		// Once the page is stored, we just re-load it with the proper data
		$page = ES::page($page->id);

		// After the page is created, assign the current user as the node item
		$page->createOwner($my->id);

		// Reform the args with the binded custom field data in the user object
		$args = array(&$data, &$page);

		// Allow fields app to make necessary changes if necessary. At this point, we wouldn't want to allow
		// the field to stop the registration process already.
		// @trigger onRegisterAfterSave
		$lib->trigger('onRegisterAfterSave', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// Bind custom fields for this user.
		$page->bindCustomFields($data);

		// @trigger onRegisterAfterSaveFields
		$lib->trigger('onRegisterAfterSaveFields', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// @trigger: onPageAfterSave
		$triggerArgs = array(&$page, &$my, true);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onPageAfterSave', $triggerArgs);

		// We need to set the "data" back to the registration table
		$newData = ES::json()->encode($data);
		$session->values = $newData;

		return $page;
	}

	/**
	 * Notify site admins that a page is created and it is pending moderation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function notifyAdminsModeration(SocialPage $page, $edited = false)
	{
		$config = ES::config();

		if (!$config->get('notifications.email.enabled')) {
			return;
		}

		// Push arguments to template variables so users can use these arguments
		$params = array(
						'title' => $page->getName(),
						'creatorName' => $page->getCreator()->getName(),
						'categoryTitle' => $page->getCategory()->get('title'),
						'avatar' => $page->getAvatar(SOCIAL_AVATAR_LARGE),
						'permalink' => JURI::root() . 'administrator/index.php?option=com_easysocial&view=pages&layout=pending',
						'reject' => ESR::controller('pages', array('external' => true, 'task' => 'rejectPage', 'id' => $page->id, 'key' => $page->key)),
						'approve' => ESR::controller('pages', array('external' => true, 'task' => 'approvePage', 'id' => $page->id, 'key' => $page->key)),
						'alerts' => false
						);

		$params['type'] = $edited ? 'EDITED' : 'CREATED';

		// Set the e-mail title
		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_PAGE_' . $params['type'] . '_MODERATOR_EMAIL_TITLE', $page->getName());

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		foreach ($admins as $admin) {

			// Immediately send out emails
			$mailer = ES::mailer();

			// Set the admin's name.
			$params['adminName'] = $admin->name;

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($admin->name, $admin->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the template
			$mailTemplate->setTemplate('site/page/moderate', $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Notify site admins that a page is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function notifyAdmins(SocialPage $page)
	{
		$config = ES::config();

		if (!$config->get('notifications.email.enabled')) {
			return;
		}

		// Push arguments to template variables so users can use these arguments
		$params = array(
						'title' => $page->getName(),
						'creatorName' => $page->getCreator()->getName(),
						'creatorLink' => $page->getCreator()->getPermalink(false, true),
						'categoryTitle' => $page->getCategory()->get('title'),
						'avatar' => $page->getAvatar(SOCIAL_AVATAR_LARGE),
						'permalink' => $page->getPermalink(true, true),
						'alerts' => false
						);

		// Set the e-mail title
		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_PAGE_CREATED_MODERATOR_EMAIL_TITLE', $page->getName());

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		foreach ($admins as $admin) {

			// Immediately send out emails
			$mailer = ES::mailer();

			// Set the admin's name.
			$params['adminName'] = $admin->name;

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($admin->name, $admin->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the template
			$mailTemplate->setTemplate('site/page/created', $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Retrieves total number of friends in the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalFriendsInPage($pageId, $options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT COUNT(DISTINCT(a.uid)) FROM ' . $db->nameQuote('#__social_clusters_nodes') . ' AS ' . $db->nameQuote('a');
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_friends') . ' AS ' . $db->nameQuote('b');
		$query[] = 'ON(';

		$query[] = '(';
		$query[] = 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('actor_id') . ' AND b.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($options['userId']);
		$query[] = ')';
		$query[] = 'OR';
		$query[] = '(';
		$query[] = 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('target_id') . ' AND b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($options['userId']);
		$query[] = ')';

		$query[] = ')';
		$query[] = 'AND b.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = 'WHERE a.' . $db->nameQuote('cluster_id') . '=' . $db->Quote($pageId);

		$publishedOnly = isset($options['published']) ? $options['published'] : false;

		if ($publishedOnly) {
			$query[] = 'AND (';
			$query[] = 'a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_PAGES_MEMBER_PUBLISHED);
			$query[] = ')';
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of friends from a particular page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFriendsInPage($pageId, $options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT DISTINCT(a.uid) FROM ' . $db->nameQuote('#__social_clusters_nodes') . ' AS ' . $db->nameQuote('a');

		$config = ES::config();
		$showNonFriend = $config->get('pages.invite.nonfriends');

		if (!$showNonFriend) {
			$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_friends') . ' AS ' . $db->nameQuote('b');
			$query[] = 'ON(';

			$query[] = '(';
			$query[] = 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('actor_id') . ' AND b.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($options['userId']);
			$query[] = ')';
			$query[] = 'OR';
			$query[] = '(';
			$query[] = 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('target_id') . ' AND b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($options['userId']);
			$query[] = ')';

			$query[] = ')';
			$query[] = 'AND b.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		}

		$query[] = 'WHERE a.' . $db->nameQuote('cluster_id') . '=' . $db->Quote($pageId);

		$publishedOnly = isset($options['published']) ? $options['published'] : false;
		$invited = isset($options['invited']) ? $options['invited'] : false;

		if ($publishedOnly) {
			$query[] = 'AND (';
			$query[] = 'a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_PAGES_MEMBER_PUBLISHED);

			if ($invited) {
				$query[] = 'OR a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_PAGES_MEMBER_INVITED);
			}

			$query[] = ')';
		}

		$db->setQuery($query);
		$result	= $db->loadColumn();

		if (!$result) {
			return $result;
		}

		$users = array();
		foreach ($result as $id) {
			$user = ES::user($id);
			$users[] = $user;
		}

		return $users;
	}

	/**
	 * Retrieves a list of online users from the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOnlineFollowers($pageId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the session life time so we can know who is really online.
		$jConfig = ES::jConfig();
		$lifespan = $jConfig->getValue('lifetime');
		$online = time() - ($lifespan * 60);

		$sql->select('#__session', 'a');
		$sql->column('b.id');
		$sql->join('#__users', 'b', 'INNER');
		$sql->on('a.userid', 'b.id');
		$sql->join('#__social_clusters_nodes', 'c', 'INNER');
		$sql->on('c.uid', 'b.id');
		$sql->on('c.type', SOCIAL_TYPE_USER);

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('c.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'b.id' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'b.id' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.time', $online, '>=');
		$sql->where('b.block', 0);
		$sql->where('c.cluster_id', $pageId);
		$sql->group('a.userid');

		$db->setQuery($sql);

		$result = $db->loadColumn();

		if (!$result) {
			return array();
		}

		$users = ES::user($result);

		return $users;
	}

	/**
	 * Retrieves a list of random members from a particular category
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRandomCategoryFollowers($categoryId, $limit = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('DISTINCT(a.uid)');
		$sql->innerjoin('#__social_clusters', 'b');
		$sql->on('a.cluster_id', 'b.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->innerjoin('#__social_users', 'u');
		$sql->on('a.uid', 'u.user_id');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.uid' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.uid' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('b.category_id', $categoryId, 'IN');
		$sql->where('u.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('b.type', SOCIAL_PAGES_PUBLIC_TYPE);

		$sql->order('', 'ASC', 'RAND');

		if ($limit) {
			$this->setState('limit', $limit);
			$sql->limit($limit);
		}

		// Get the final result
		$result = $this->getData($sql);

		if (!$result) {
			return $result;
		}

		$users = array();

		foreach ($result as $row) {
			$user = ES::user($row->uid);
			$users[] = $user;
		}

		return $users;
	}

	/**
	 * Retrieves page creation stats for a particular category
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCreationStats($categoryId)
	{
		$db = ES::db();
		$sql = $db->sql();
		$dates = array();

		// Get the past 7 days
		$curDate = ES::date();
		for ($i = 0; $i < 7; $i++) {
			$obj = new stdClass();

			if ($i == 0) {
				$obj->date = $curDate->toMySQL();
			} else {
				$unixdate = $curDate->toUnix();
				$new_unixdate = $unixdate - ($i * 86400);
				$newdate = ES::date($new_unixdate);

				$obj->date = $newdate->toSql();
			}
			$dates[] = $obj;
		}

		// Reverse the dates
		$dates = array_reverse($dates);
		$result = array();

		foreach ($dates as &$row) {
			// Registration date should be Y, n, j
			$date = ES::date($row->date)->format('Y-m-d');

			$query = array();
			$query[] = 'SELECT COUNT(1) AS `cnt` FROM ' . $db->nameQuote('#__social_clusters') . ' AS a';
			$query[] = 'WHERE DATE_FORMAT(a.created, GET_FORMAT(DATE, "ISO")) =' . $db->Quote($date);
			$query[] = 'AND a.`category_id` = ' . $db->Quote($categoryId);
			$query[] = 'AND a.`type` = ' . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE);
			$query[] = 'group by a.`category_id`';

			$query = implode(' ', $query);
			$sql->raw($query);

			$db->setQuery($sql);

			$total = $db->loadResult();
			$result[] = (int) $total;
		}

		return $result;
	}

	/**
	 * Retrieves the total number of albums in the page.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalAlbums($options = array())
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select count(1)';
		$query .= ' from `#__social_albums` as a';

		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' a.' . $db->nameQuote('user_id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' a.' . $db->nameQuote('user_id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';

		}

		$query .= ' inner join `#__social_clusters` as b on a.`uid` = b.`id`';
		$query .= ' where a.`type` = ' . $db->Quote(SOCIAL_TYPE_PAGE);
		$query .= ' and a.`core` = ' . $db->Quote('0'); // do not get core album
		$query .= ' and b.`state` = ' . $db->Quote(SOCIAL_CLUSTER_PUBLISHED);
		$query .= ' and b.`type` != ' . $db->Quote(SOCIAL_PAGES_INVITE_TYPE);


		// Test to check against category id
		$category = isset($options['category_id']) ? $options['category_id'] : '';

		if ($category) {

			if (is_array($category) || is_object($category)) {
				$query .= ' and b.`category_id` IN(' . implode(',', $category) . ')';
			} else {
				$query .= ' and b.`category_id` = ' . $db->Quote($category);
			}
		}

		// Test to filter featured items
		$featured = isset($options['featured']) ? $options['featured'] : '';

		if ($featured !== '') {
			$query .= ' and b.`featured` = ' . $db->Quote($featured);
		}

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$sql->raw($query);
		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Retrieves random albums
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRandomAlbums($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_albums', 'a');
		$sql->column('a.*');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users', 'bus');

			$sql->on('(');
			$sql->on( 'a.user_id' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'a.user_id' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		// Filter by category id
		$category = isset($options['category_id']) ? $options['category_id'] : '';

		if ($category) {
			$sql->join('#__social_clusters', 'b');
			$sql->on('a.uid', 'b.id');
			$sql->join('#__social_clusters_categories', 'c');
			$sql->on('c.id', 'b.category_id');

			$sql->where('c.id', $category, 'IN');
			$sql->where('a.type', SOCIAL_TYPE_PAGE);
			$sql->where('b.type', SOCIAL_PAGES_PUBLIC_TYPE);
		}

		// Determine if we should include the core albums
		$coreAlbums = isset($options['core']) ? $options['core'] : true;

		if (!$coreAlbums) {
			$sql->where('a.core', 0);
		}

		$coreAlbumsOnly	= isset($options['coreAlbumsOnly']) ? $options['coreAlbumsOnly'] : '';

		if ($coreAlbumsOnly) {
			$sql->where('a.core', 0, '>');
		}

		$withCoversOnly	= isset($options['withCovers']) ? $options['withCovers'] : '';

		if ($withCoversOnly) {
			$sql->join('#__social_photos', 'b', 'INNER');
			$sql->on('a.cover_id', 'b.id');
		}

		$ordering = isset($options['order']) ? $options['order'] : '';

		if ($ordering) {
			$direction 	= isset($options['direction']) ? $options['direction'] : 'desc';
			$sql->order($ordering, $direction);
		}

		$pagination = isset($options['pagination']) ? $options['pagination'] : false;

		$result = array();

		if ($pagination) {
			// Set the total number of items.
			$totalSql = $sql->getTotalSql();
			$this->setTotal($totalSql);
			$result	= $this->getData($sql);
		} else {
			$limit = isset($options['limit']) ? $options['limit'] : '';

			if ($limit) {
				$sql->limit($limit);
			}

			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$albums = array();

		foreach ($result as $row) {
			$album = ES::table('Album');
			$album->bind($row);
			$albums[] = $album;
		}

		return $albums;
	}

	/**
	 * Get a list of pages from this user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUserPages($userId, $limitstart = null, $limit = null, $search = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.`cluster_id` from `#__social_clusters_nodes` as a';
		$query .= '	inner join `#__social_clusters` as b on a.`cluster_id` = b.`id`';
		$query .= '		and b.`cluster_type` = ' . $db->Quote(SOCIAL_TYPE_PAGE) . ' and b.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= ' where a.`uid` = ' . $db->Quote($userId);
		$query .= ' and a.`state` = ' . $db->Quote(SOCIAL_PAGES_MEMBER_PUBLISHED);

		if ($search) {
			$query .= ' AND b.`title` LIKE ' . $db->Quote('%' . $search . '%');
		}

		$query .= ' ORDER BY `a`.`created` DESC';

		if (!is_null($limit)) {
			$limit = (int) $limit;
			$query .= ' LIMIT 0,' . $limit;
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$ids = $db->loadColumn();

		$pages 	= array();

		if ($ids) {
			$pages = ES::page()->loadPages($ids);
		}

		return $pages;
	}

	/**
	 * Retrieves the About information from the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAbout(SocialPage $page, $activeStep = 0)
	{
		static $items = array();

		if (!isset($itmes[$page->id])) {
			// Load admin's language file
			ES::language()->loadAdmin();

			// Get the available workflow for the page
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($page->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, SOCIAL_PAGES_VIEW_DISPLAY);

			// Initialize the field library
			$fieldsLib = ES::fields();
			$fieldsLib->init(array('privacy' => false));

			$fieldsModel =ES::model('Fields');

			$index = 1;

			foreach ($steps as &$step) {
				$fieldOptions = array('step_id' => $step->id, 'data' => true, 'dataId' => $page->id, 'dataType' => SOCIAL_TYPE_PAGE, 'visible' => SOCIAL_PAGES_VIEW_DISPLAY);

				$step->fields = $fieldsModel->getCustomFields($fieldOptions);

				// If there are fields, we should trigger the app to prepare them
				if (!empty($step->fields)) {
					$args = array(&$page);
					$fieldsLib->trigger('onDisplay', SOCIAL_FIELDS_GROUP_PAGE, $step->fields, $args);
				}

				// Default hide the step
				$step->hide = true;

				// As long as one of the field in the step has an output, then this step shouldn't be hidden
				// If step has been marked false, then no point marking it as false again
				// We don't break from the loop here because there is other checking going on
				foreach ($step->fields as $field) {

					// We do not want to consider "separator" field as a valid output. #555
					if ($field->element == 'separator') {
						continue;
					}

					if (!empty($field->output) && $step->hide === true) {
						$step->hide = false;
					}
				}

				$step->url = ESR::pages(array('layout' => 'item', 'id' => $page->getAlias(), 'type' => 'info', 'infostep' => $index), false);

				if ($index === 1) {
					$step->url = ESR::pages(array('layout' => 'item', 'id' => $page->getAlias(), 'type' => 'info'), false);
				}

				// Get the step title
				$step->title = JText::_($step->title);
				$step->active = !$step->hide && $activeStep == $index;
				$step->index = $index;

				$index++;
			}

			$items[$page->id] = $steps;
		}

		return $items[$page->id];
	}

	public function searchPageByHours($options = array())
	{
		$sort = isset($options['sort']) ? $options['sort'] : 'latest';

		$sModel = ES::model('SearchCluster');

		$cOptions = array();
		$cOptions['clusterType'] = SOCIAL_TYPE_PAGE;
		$cOptions['ignoreInvite'] = true;
		// $cOptions['clusterCategoryIds'] = isset($options['categoryIds']) ? $options['categoryIds'] : array();
		// $cOptions['clusterAuthorIds'] = isset($options['authorIds']) ? $options['authorIds'] : array();
		$cOptions['clusterCategoryIds'] = array();
		$cOptions['clusterAuthorIds'] = array();

		// clean up data
		if (isset($options['authorIds'])) {
			$tmp = is_array($options['authorIds']) ? $options['authorIds'] : array($options['authorIds']);
			foreach ($tmp as $tdata) {
				$tdata = (int) $tdata;
				if ($tdata) {
					$cOptions['clusterAuthorIds'][] = $tdata;
				}
			}
		}

		// clean up data
		if (isset($options['categoryIds'])) {
			$tmp = is_array($options['categoryIds']) ? $options['categoryIds'] : array($options['categoryIds']);
			foreach ($tmp as $tdata) {
				$tdata = (int) $tdata;
				if ($tdata) {
					$cOptions['clusterCategoryIds'][] = $tdata;
				}
			}
		}

		$daytimes = isset($options['daytimes']) ? $options['daytimes'] : array();

		if (!$daytimes) {
			// give a default search.
			$daytimes[] = 'all|09:00|18:00';
		}

		$queries = array();

		foreach ($daytimes as $item) {

			// here we need to build the format that advanced seach model will understand.

			$criterias = array();
			$datakeys = array();
			$operators = array();
			$conditions = array();

			$data = explode('|', $item);
			list($day, $start, $end) = $data;

			$idx = 0;

			// if ($day != 'all') {

			// 	// day
			// 	$criterias[$idx] = 'HOURS|hours';
			// 	$datakeys[$idx] = 'day';
			// 	$operators[$idx] = 'equal';
			// 	$conditions[$idx] = $day;

			// 	// increase array idx for second condition.
			// 	$idx++;
			// }

			// hours
			$criterias[$idx] = 'HOURS|hours';
			$datakeys[$idx] = 'hour';

			// this is a new operator where it only support 'hours' search in which, the search must be accompany with days.
			$operators[$idx] = 'hourswithday';

			// if ($start == '00:00') {
			// 	// this is so that the condition will not fall into 'always' checking.
			// 	$start = '00:01';
			// }

			$conditions[$idx] = $day . '|' . $start . '|' . $end;

			$cOptions['criterias'] = $criterias;
			$cOptions['datakeys'] = $datakeys;
			$cOptions['operators'] = $operators;
			$cOptions['conditions'] = $conditions;

			$subQuery = $sModel->buildAdvSearch('all', $cOptions);

			if ($subQuery) {
				$queries[] = $subQuery;
			}
		}

		if (! $queries) {
			return array();
		}

		$db = ES::db();

		$subQuery = "select * from (";
		$subQuery .= implode(' UNION ', $queries);
		$subQuery .= ") as sq";

		$cntQuery = $subQuery;


		// now we need to contruct another query wrapper so that
		// we can sort the result sets.
		$masterQuery = "select c.id";
		$masterQuery .= " from `#__social_clusters` as c";
		$masterQuery .= " inner join (" . $subQuery . ") as csq on c.`id` = csq.`id`";
		// $masterQuery .= " where c.`id` exists (" . $subquery . ")";
		//
		if ($sort == 'name') {
			$masterQuery .= ' ORDER BY c.`title` ASC';
		} else {
			$masterQuery .= ' ORDER BY c.`created` DESC';
		}

		// echo $masterQuery;
		// exit;

		$limit 	= isset($options['limit']) ? $options['limit'] : '';
		$results = array();

		if ($limit != 0) {
			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);

			// Set the total number of items.
			$this->setTotal($cntQuery, true);

			// Get the list of users
			$results = $this->getData($masterQuery);

		} else {
			$db->setQuery($masterQuery);
			$results = $db->loadObjectList();
		}

		$pages = array();

		if ($results) {
			foreach ($results as $item) {
				$pages[] = ES::page($item->id);
			}
		}

		return $pages;
	}


	/**
	 * Search for Pages
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function search($keyword, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('cluster_type', SOCIAL_TYPE_PAGE);
		$sql->where('title', '%' . $keyword . '%', 'LIKE');

		$unpublished = isset($options['unpublished']) && $options['unpublished'] ? true : false;

		if (!$unpublished) {
			$sql->where('state', SOCIAL_STATE_PUBLISHED);
		}

		$exclusion = isset($options['exclusion']) && $options['exclusion'] ? $options['exclusion'] : false;

		if ($exclusion) {
			$exclusion = ES::makeArray($exclusion);

			$sql->where('id', $exclusion, 'NOT IN');
		}

		$db->setQuery($sql);
		$result = $db->loadObjectList();
		$pages = array();

		if (!$result) {
			return $pages;
		}

		foreach ($result as $row) {
			$page = ES::page();
			$page->bind($row);

			$pages[] = $page;
		}

		return $pages;
	}

	/**
	 * Get a list of pages for the particular user.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getPagesGDPR($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		$limit = $this->normalize($options, 'limit', false);
		$userId = $this->normalize($options, 'userid', null);
		$exclusion = $this->normalize($options, 'exclusion', null);

		$query[] = 'SELECT `id`, `title`, `description`, `created`, `cluster_type` FROM ' . $db->nameQuote('#__social_clusters');
		$query[] = ' WHERE ' . $db->nameQuote('creator_uid') . ' = ' . $db->Quote($userId);
		$query[] = ' AND ' . $db->nameQuote('cluster_type') . ' = ' . $db->Quote(SOCIAL_TYPE_PAGE);
		$query[] = ' AND ' . $db->nameQuote('creator_type') . ' = ' . $db->Quote(SOCIAL_TYPE_USER);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND ' . $db->nameQuote('id') . ' NOT IN (' . $exclusionIds . ')';
		}

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

		$query[] = "limit $limitstart, $limit";

		$query = implode(' ', $query);

		$sql->clear();
		$sql->raw($query);

		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();

		return $result;
	}

	/**
	 * Determines if an email exists in this page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isEmailExists($email, $pageId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');
		$sql->join('#__users', 'b');
		$sql->on('a.uid', 'b.id');
		$sql->where('b.email', $email);
		$sql->where('a.type', SOCIAL_TYPE_USER);
		$sql->where('a.cluster_id', $pageId);
		$sql->where('a.state', SOCIAL_PAGES_MEMBER_PUBLISHED);

		$db->setQuery($sql);

		$exists = $db->loadResult() > 0;

		return $exists;
	}

	/**
	 * Retrieves the verification count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getVerificationCount()
	{
		$model = ES::model('Verifications');
		return $model->getVerificationCount(SOCIAL_TYPE_PAGE);
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Retrieves the total number of followers
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getTotalMembers($clusterId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->where('a.cluster_id', $clusterId);
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}
}
