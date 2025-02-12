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

class EasySocialModelGroups extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('groups', $config);

		$this->config = ES::config();
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('state', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'DESC');
		$type = $this->getUserStateFromRequest('type', 'all');
		$category = $this->getUserStateFromRequest('category', -1);

		$this->setState('type', $type);
		$this->setState('category', $category);
		$this->setState('state', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Saves the ordering of profiles
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveOrder($ids, $ordering)
	{
		$table 	= ES::table('Profile');
		$table->reorder();
	}

	/**
	 * Retrieves group creation stats for a particular category
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCreationStats($categoryId)
	{
		$db			= ES::db();
		$sql 		= $db->sql();
		$dates 		= array();

		// Get the past 7 days
		$curDate 	= ES::date();
		for($i = 0 ; $i < 7; $i++)
		{
			$obj 		= new stdClass();

			if ($i == 0) {
				$obj->date 	= $curDate->toMySQL();
			}
			else
			{
				$unixdate 		= $curDate->toUnix();
				$new_unixdate 	= $unixdate - ($i * 86400);
				$newdate  		= ES::date($new_unixdate);

				$obj->date 		= $newdate->toSql();
			}

			$dates[]	= $obj;
		}

		// Reverse the dates
		$dates 		= array_reverse($dates);
		$result		= array();

		foreach ($dates as &$row) {
			// Registration date should be Y, n, j
			$date	= ES::date($row->date)->format('Y-m-d');

			$query 		= array();
			$query[]	= 'SELECT COUNT(1) AS `cnt` FROM ' . $db->nameQuote('#__social_clusters') . ' AS a';
			$query[]	= 'WHERE DATE_FORMAT(a.created, GET_FORMAT(DATE, "ISO")) =' . $db->Quote($date);
			$query[]	= 'AND a.`category_id` = ' . $db->Quote($categoryId);
			$query[]	= 'AND a.`type` IN(' . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ',' . $db->Quote(SOCIAL_GROUPS_SEMI_PUBLIC_TYPE) .')';
			$query[]    = 'group by a.`category_id`';

			$query 		= implode(' ', $query);
			$sql->raw($query);


			$db->setQuery($sql);

			$total		= $db->loadResult();

			$result[]	= (int) $total;
		}

		return $result;
	}

	/**
	 * Retrieves a list of random members from a particular category
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getRandomCategoryMembers($categoryId, $limit = false)
	{
		$db = ES::db();

		$query = array();

		$query[] = "select DISTINCT(a.uid) from `#__social_clusters_nodes` as a";
		$query[] = " INNER JOIN `#__social_clusters` as b on a.`cluster_id` = b.`id`";

		// exclude esad users
		$query[] = " INNER JOIN `#__social_profiles_maps` as upm on a.`uid` = upm.`user_id`";
		$query[] = " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";

		$query[] = " INNER JOIN `#__social_users` as u on a.`uid` = u.`user_id`";

		if (ES::isBlockEnabled()) {
			$query[] = $this->getJoinBlockQuery('a');
		}

		$categoryIds = ES::makeArray($categoryId);

		$query[] = " WHERE b.`category_id` IN (" . implode(",", $categoryIds) . ")";
		$query[] = " AND u.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = " AND b.`type` IN (" . implode(",", array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE)) . ")";

		if (ES::isBlockEnabled()) {
			$query[] = " AND " . $this->getWhereBlockQuery();
		}

		$query[] = " ORDER BY RAND()";

		if ($limit) {
			$query[] = " LIMIT " . $limit;
		}

		// join the query
		$query = implode(' ', $query);

		// echo $query;exit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

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
	 * Retrieves the "About" information from a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAbout(SocialGroup $group, $activeStep = 0)
	{
		static $items = array();

		if (!isset($items[$group->id])) {
			// Load admin's language file
			ES::language()->loadAdmin();

			// Get available workflows for this group
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($group->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, SOCIAL_GROUPS_VIEW_DISPLAY);

			// Initialize the fields library
			$fieldsLib = ES::fields();
			$fieldsLib->init(array('privacy' => false));

			$fieldsModel = ES::model('Fields');

			$index = 1;

			foreach ($steps as &$step) {

				$fieldOptions = array('step_id' => $step->id, 'data' => true, 'dataId' => $group->id, 'dataType' => SOCIAL_TYPE_GROUP, 'visible' => SOCIAL_GROUPS_VIEW_DISPLAY);

				$step->fields = $fieldsModel->getCustomFields($fieldOptions);

				// If there are fields, we should trigger the apps to prepare them
				if (!empty($step->fields)) {
					$args = array(&$group);
					$fieldsLib->trigger('onDisplay', SOCIAL_FIELDS_GROUP_GROUP, $step->fields, $args);
				}

				// Default to hide the step
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

				$step->url = ESR::groups(array('layout' => 'item', 'id' => $group->getAlias(), 'type' => 'info', 'infostep' => $index), false);

				if ($index === 1) {
					$step->url = FRoute::groups(array('layout' => 'item', 'id' => $group->getAlias(), 'type' => 'info'), false);
				}

				// Get the step title
				$step->title = JText::_($step->title);

				$step->active = !$step->hide && $activeStep == $index;

				$step->index = $index;

				$index++;
			}

			$items[$group->id] = $steps;
		}

		return $items[$group->id];
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

		$query[] = ' LEFT JOIN `#__social_block_users` AS `bus`';
		$query[] = 'ON (';

		// target
		$tmp = '(' . $tblAlias . '.`uid` = `bus`.`user_id` AND `bus`.`target_id` = ' . JFactory::getUser()->id;
		$tmp .= "    and bus.target_id NOT IN (";
		$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $tblAlias . ".`cluster_id` and clsnd.`admin` = 1";
		$tmp .= "    )";
		$tmp .= ')';

		$query[] = $tmp;

		// actor
		$tmp = '(' . $tblAlias . '.`uid` = `bus`.`target_id` AND `bus`.`user_id` = ' . JFactory::getUser()->id;
		$tmp .= "    and bus.user_id NOT IN (";
		$tmp .= "        select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = " . $tblAlias . ".`cluster_id` and clsnd.`admin` = 1";
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
	 * Retrieves a list of members from a particular group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMembers($groupId, $options = array())
	{
		static $cache = array();

		ksort($options);
		$optionskey = serialize($options);
		$load = array();

		if (is_array($groupId)) {
			foreach($groupId as $gid) {
				if (! isset($cache[$gid][$optionskey])) {
					$load[] = $gid;
				}
			}
		} else {

			if (! isset($cache[$groupId][$optionskey])) {
				$load[] = $groupId;
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
					$load[] = $groupId;
				}

				$cache[$load[0]][$optionskey] = array();
			}

			$db = ES::db();

			$query = "select a.* from `#__social_clusters_nodes` as a";
			// We should not fetch banned users
			$query .= " INNER JOIN `#__users` as u on a.`uid` = u.`id`";
			// exclude esad users
			$query .= " INNER JOIN `#__social_profiles_maps` as upm on u.`id` = upm.`user_id`";
			$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.id and up.community_access = 1";

			if (ES::isBlockEnabled()) {
				$query .= $this->getJoinBlockQuery('a');
			}

			$conds = array();

			// By specific groups
			if (count($load) > 1) {
				$conds[] = "a.`cluster_id` IN (" . implode(',', $load) . ")";
			} else {
				$conds[] = "a.`cluster_id` = " . $db->Quote($load[0]);
			}

			// Whe the user isn't blocked
			$conds[] = "u.`block` = 0";

			$state = isset($options['state']) ? $options['state'] : '';
			if ($state) {
				$conds[] = "a.`state` = " . $db->Quote($state);
			} else {
				// Invited member does not considered as pending
				$conds[] = "a.`state` != " . $db->Quote(SOCIAL_GROUPS_MEMBER_INVITED);
			}

			// Determine if we should retrieve admins only
			$adminOnly = isset($options['admin']) ? $options['admin'] : '';

			if ($adminOnly) {
				$conds[] = "a.`admin` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
			}

			// Determines if we should retrieve members only
			$membersOnly = isset($options['members']) && $options['members'] ? true : false;

			if ($membersOnly) {
				$conds[] = "a.`admin` = " . $db->Quote(0);
			}

			// Determine if we want to exclude this.
			$exclude = isset($options['exclude']) ? $options[ 'exclude' ] : '';

			if ($exclude) {
				if (is_array($exclude)) {
					$exclude = implode(',', $exclude);
				}
				$conds[] = "a.`uid` NOT IN (" . $exclude . ")";
			}

			// Search members
			$search = isset($options['search']) ? $options['search'] : '';

			if ($search) {
				$usernameType = ES::config()->get('users.displayName');

				if ($usernameType == SOCIAL_FRIENDS_SEARCH_NAME || $usernameType == SOCIAL_FRIENDS_SEARCH_REALNAME) {
					$conds[] = "u.`name` LIKE " . $db->Quote('%' . $search . '%');

				}

				if ($usernameType == SOCIAL_FRIENDS_SEARCH_USERNAME) {
					$conds[] = "u.`username` LIKE " . $db->Quote('%' . $search . '%');
				}
			}


			// now join the conditions.
			$query .= $conds ? " WHERE " . implode(" AND ", $conds) : "";

			$counterSQL = $query;

			if (!empty($options['ordering'])) {
				$direction = !empty($options['direction']) ? $options['direction'] : 'asc';
				$query .= " ORDER BY " . $options['ordering'] . " " . $direction;
			}

			// Should we apply pagination
			$limit = isset($options['limit']) ? $options['limit'] : '';

			if ($limit) {

				$this->setState('limit', $limit);

				// Get the limitstart.
				$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '';

				if (!$limitstart) {
					$limitstart = $this->getUserStateFromRequest('limitstart', 0);
					$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
				}

				$this->setState('limitstart', $limitstart);

				$this->setTotal($counterSQL, true);

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

		if (is_array($groupId)) {
			// when this is an array of group ids, we know we are doign preload. lets return true.
			return true;
		}

		$result = $cache[$groupId][$optionskey];
		$usersObject = isset($options[ 'users' ]) ? $options[ 'users' ] : true;

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
	 * Retrieves random albums
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getRandomAlbums($options = array())
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

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
		$category 	= isset($options[ 'category_id' ]) ? $options[ 'category_id' ] : '';

		if ($category) {
			$sql->join('#__social_clusters', 'b');
			$sql->on('a.uid', 'b.id');
			$sql->join('#__social_clusters_categories', 'c');
			$sql->on('c.id', 'b.category_id');

			$sql->where('c.id', $category, 'IN');
			$sql->where('a.type', SOCIAL_TYPE_GROUP);
			$sql->where('b.type', array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE), 'IN');
		}

		// Determine if we should include the core albums
		$coreAlbums 	= isset($options[ 'core' ]) ? $options[ 'core' ] : true;

		if (!$coreAlbums) {
			$sql->where('a.core', 0);
		}

		$coreAlbumsOnly	= isset($options[ 'coreAlbumsOnly' ]) ? $options[ 'coreAlbumsOnly' ] : '';

		if ($coreAlbumsOnly) {
			$sql->where('a.core', 0, '>');
		}

		$withCoversOnly	= isset($options[ 'withCovers' ]) ? $options[ 'withCovers' ] : '';

		if ($withCoversOnly) {
			$sql->join('#__social_photos', 'b', 'INNER');
			$sql->on('a.cover_id', 'b.id');
		}

		$ordering 		= isset($options[ 'order' ]) ? $options[ 'order' ] : '';

		if ($ordering) {
			$direction 	= isset($options[ 'direction' ]) ? $options[ 'direction' ] : 'desc';

			$sql->order($ordering, $direction);
		}


		$pagination 	= isset($options[ 'pagination' ]) ? $options[ 'pagination' ] : false;


		$result = array();

		if ($pagination) {
			// Set the total number of items.
			$totalSql 		= $sql->getTotalSql();
			$this->setTotal($totalSql);

			$result			= $this->getData($sql);
		}
		else
		{
			$limit 		= isset($options[ 'limit' ]) ? $options[ 'limit' ] : '';
			if ($limit) {
				$sql->limit($limit);
			}

			$db->setQuery($sql);
			$result 	= $db->loadObjectList();
		}



		if (!$result) {
			return $result;
		}

		$albums 	= array();

		foreach ($result as $row) {
			$album 	= ES::table('Album');
			$album->bind($row);

			$albums[]	= $album;
		}

		return $albums;
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Retrieves the total number of groups in the system.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalGroups($options = array())
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
		$sql->where('a.cluster_type', SOCIAL_TYPE_GROUP);

		$types = isset($options['types']) ? $options['types'] : '';

		if ($types != 'all') {
			if ($types === 'user') {
				$userid = isset($options['userid']) ? $options['userid'] : ES::user()->id;

				$sql->leftjoin('#__social_clusters_nodes', 'nodes');
				$sql->on('a.id', 'nodes.cluster_id');

				$sql->where('(');
				$sql->where('a.type', array(SOCIAL_GROUPS_PRIVATE_TYPE, SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE), 'IN');
				$sql->where('(', '', '', 'OR');
				$sql->where('a.type', SOCIAL_GROUPS_INVITE_TYPE);
				$sql->where('nodes.uid', $userid);
				$sql->where(')');
				$sql->where(')');
			} else {

				// Get the current logged in user
				$my = ES::user();

				if (!$my->isSiteAdmin()) {
					$sql->where('a.type', array(SOCIAL_GROUPS_PRIVATE_TYPE, SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE), 'IN');
				}
			}
		}

		// Test to check against category id
		$category = isset($options[ 'category_id' ]) ? $options[ 'category_id' ] : '';

		if ($category) {
			$sql->where('a.category_id', $category, 'IN');
		}

		// Test to filter featured items
		$featured 	= isset($options[ 'featured' ]) ? $options[ 'featured' ] : '';

		if ($featured !== '') {
			$sql->where('a.featured', $featured);
		}

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Retrieves the total number of groups in the system.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalAlbums($options = array())
	{
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
		$query .= ' where a.`type` = ' . $db->Quote(SOCIAL_TYPE_GROUP);
		$query .= ' and a.`core` = ' . $db->Quote('0'); // do not get core album
		$query .= ' and b.`state` = ' . $db->Quote(SOCIAL_CLUSTER_PUBLISHED);
		$query .= ' and b.`type` != ' . $db->Quote(SOCIAL_GROUPS_INVITE_TYPE);


		// Test to check against category id
		$category = isset($options[ 'category_id' ]) ? $options[ 'category_id' ] : '';

		if ($category) {

			if (is_array($category) || is_object($category)) {
				$query .= ' and b.`category_id` IN(' . implode(',', $category) . ')';
			} else {
				$query .= ' and b.`category_id` = ' . $db->Quote($category);
			}
		}

		// Test to filter featured items
		$featured = isset($options[ 'featured' ]) ? $options[ 'featured' ] : '';

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
	 * Retrieves a list of online users from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOnlineMembers($groupId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the session life time so we can know who is really online.
		$jConfig 	= ES::jConfig();
		$lifespan 	= $jConfig->getValue('lifetime');
		$online 	= time() - ($lifespan * 60);

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
		$sql->where('c.cluster_id', $groupId);
		$sql->group('a.userid');

		$db->setQuery($sql);

		$result = $db->loadColumn();

		if (!$result) {
			return array();
		}

		$users	= ES::user($result);

		return $users;
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
		return $model->getVerificationCount(SOCIAL_TYPE_GROUP);
	}

	/**
	 * Retrieves the total number of invited group for this user.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalInvites($userId)
	{
		$invitedGroups = $this->getGroups(array('invited' => $userId, 'types' => 'user'));

		$total = count($invitedGroups);

		return $total;
	}

	/**
	 * Retrieves the total number of profiles in the system.
	 *
	 * @since	1.0
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

	/**
	 * Retrieves the total number of profiles in the system.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPendingMembers($groupId)
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

		$sql->where('a.cluster_id', $groupId);
		$sql->where('a.state', SOCIAL_GROUPS_MEMBER_PENDING);
		$sql->where('b.block', SOCIAL_JOOMLA_USER_UNBLOCKED);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Retrieves the total number of invited members.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalInvitedMembers($groupId)
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

		$sql->where('a.cluster_id', $groupId);
		$sql->where('a.state', SOCIAL_GROUPS_MEMBER_INVITED);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Dprecated since 1.2. Use EasySocialModelGroupCategories::getItems() instead.
	 * Retrieves a list of cluster categories on the site.
	 *
	 * @since	1.0
	 * @deprecated  1.2
	 * @access	public
	 * @param	null
	 * @return	Array	An array list of SocialTableProfile
	 *
	 */
	public function getCategoriesWithState($options = array())
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_clusters_categories');

		// Check for search
		$search 	= $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Check for state
		$state 		= $this->getState('state');

		if ($state != 'all') {
			$sql->where('state', $state);
		}

		// This must always be checked
		$sql->where('type', SOCIAL_TYPE_GROUP);

		$ordering = $this->getState('ordering', 'ordering');
		$direction = $this->getState('direction', 'asc');

		$sql->order($ordering, $direction);

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return false;
		}

		$categories	= array();
		$total      = count($result);

		for($i = 0; $i < $total; $i++)
		{
			$category       = ES::table('GroupCategory');
			$category->bind($result[ $i ]);

			$categories[]    = $category;
		}

		return $categories;
	}

	/**
	 * Deprecated. Use EasySocialModelGroupCategories::getCategories() instead.
	 * Retrieves a list of cluster categories on the site.
	 *
	 * @since	1.0
	 * @deprecated 1.2
	 * @access	public
	 * @param	null
	 * @return	Array	An array list of SocialTableProfile
	 *
	 */
	public function getCategories($options = array())
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_clusters_categories', 'a');
		$sql->column('a.*');

		// Check for search
		$search 	= isset($options[ 'search' ]) ? $options[ 'search' ] : '';

		if ($search) {
			$sql->where('a.title', '%' . $search . '%', 'LIKE');
		}

		// Check for state
		$state 		= isset($options[ 'state' ]) ? $options[ 'state' ] : '';

		if ($state != 'all') {
			$sql->where('a.state', $state);
		}

		// Check for profile access
		$profileId 	= isset($options['profile_id']) ? $options['profile_id'] : '';

		if ($profileId) {

			$sql->join('#__social_clusters_categories_access', 'c');
			$sql->on('a.id', 'c.category_id');
			$sql->on('c.type', 'create');

			$sql->where('c.profile_id', $profileId);
		}

		// This must always be checked
		$sql->where('a.type', SOCIAL_TYPE_GROUP);

		// Determine the ordering
		$ordering 	= isset($options[ 'ordering' ]) ? $options[ 'ordering' ] : 'ordering';

		if ($ordering == 'title') {
			$sql->order('a.title', 'ASC');
		}

		// Order by total number of groups
		if ($ordering == 'groups') {
			$sql->join('#__social_clusters', 'b');
			$sql->on('b.category_id', 'a.id');
			$sql->on('b.state', SOCIAL_CLUSTER_PUBLISHED);
			$sql->order('COUNT(b.id)', 'DESC');
			$sql->group('a.id');
		}

		if ($ordering == 'ordering') {
			$sql->order('a.ordering');
		}

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result		= $this->getData($sql);

		if (!$result) {
			return false;
		}

		$categories	= array();
		$total      = count($result);

		for($i = 0; $i < $total; $i++)
		{
			$category       = ES::table('GroupCategory');
			$category->bind($result[ $i ]);

			$categories[]    = $category;
		}

		return $categories;
	}

	/**
	 * Retrieves a list of cluster categories on the site
	 *
	 * @since	1.0
	 * @access	public
	 *
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
			$query[] = "WHERE `a`.`type` = 'group'";
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

			$categories = $this->bindTable('GroupCategory', $result);

			$_cache[$idx] = $categories;

		}

		return $_cache[$idx];
	}

	/**
	 * Retrieves a list of custom profiles from the site.
	 *
	 * @since	1.0
	 * @access	public
	 *
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('a.*');
		$sql->column('b.title', 'categoryTitle');

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('a.title', '%' . $search . '%', 'LIKE');
		}

		// Determines if we should load pending groups
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
		$category = $this->getState('category');

		if ($category && $category != -1) {
			$sql->where('a.category_id', $category);
		}

		$type = $this->getState('type');

		if ($type != 'all') {
			$sql->where('a.type', $type);
		}

		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction	= $this->getState('direction');

			$sql->order($ordering, $direction);
		}

		// Join with the category as we need to order by category
		$sql->join('#__social_clusters_categories', 'b');
		$sql->on('b.id', 'a.category_id');

		// This must always be checked
		$sql->where('a.cluster_type', SOCIAL_TYPE_GROUP);

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result		= $this->getData($sql);

		if (!$result) {
			return false;
		}

		$groups		= array();

		foreach ($result as $row) {
			$group 		= ES::group($row->id);
			$groups[]	= $group;
		}

		return $groups;
	}

	/**
	 * Retrieves a list of custom profiles from the site.
	 *
	 * @since	1.0
	 * @access	public
	 *
	 */
	public function getItems($options = array())
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_clusters');

		// Check for search
		$search 	= $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Determines if we should load pending groups
		$pending 	= isset($options[ 'pending' ]) ? $options[ 'pending' ] : false;

		if ($pending) {
			$sql->where('state', array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');
		} else {
			$sql->where('state',  array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'NOT IN');
		}

		// This must always be checked
		$sql->where('cluster_type', SOCIAL_TYPE_GROUP);

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result		= $this->getData($sql);

		if (!$result) {
			return false;
		}

		$groups		= array();

		foreach ($result as $row) {
			$group 		= ES::group($row->id);
			$groups[]	= $group;
		}

		return $groups;
	}

	/**
	 * Generates a unique alias for the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUniqueAlias($title, $exclude = null)
	{
		// Ensure that the permalink would be safe.
		$alias = ES::generatePermalink($title);

		$model = ES::model('Clusters');

		$i = 2;

		// Set this to a temporary alias
		$tmp = $alias;

		do {
			$exists = $model->clusterAliasExists($alias, $exclude, SOCIAL_TYPE_GROUP);

			if ($exists) {
				$alias	= $tmp . '-' . $i++;
			}

		} while ($exists);

		return $alias;
	}

	/**
	 * Retrieves the total number of groups a user has created.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalCreatedGroups($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('COUNT(1)');

		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.creator_uid', $userId);
		$sql->where('a.cluster_type', SOCIAL_TYPE_GROUP);

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of unpublished groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalUnpublishedGroups($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('COUNT(1)');

		$sql->where('a.state', SOCIAL_STATE_UNPUBLISHED);

		// For the site admin need to count all the unpublished group because site admin has permission to access it.
		if (!ES::user()->isSiteAdmin()) {
			$sql->where('a.creator_uid', $userId);
		}

		$sql->where('a.cluster_type', SOCIAL_TYPE_GROUP);

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of groups a user is participating in.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTotalParticipatedGroups($userId, $filter = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$types = isset($filter[ 'types' ]) ? $filter['types'] : '';

		$my = ES::user();

		$query = "select count(1) from `#__social_clusters_nodes` as a";
		$query .= " INNER JOIN `#__social_clusters` as b on b.`id` = a.`cluster_id`";

		if (ES::isBlockEnabled()) {

			$query .= ' LEFT JOIN `#__social_block_users` AS `bus`';
			$query .= ' ON (b.`creator_uid` = bus.`user_id`';
			$query .= ' AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ' OR b.`creator_uid` = bus.`target_id`';
			$query .= ' AND bus.`user_id` = ' . $db->Quote(JFactory::getUser()->id) . ')';
		}

		$query .= " where a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= " and b.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= " and b.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_GROUP);
		$query .= " and a.`uid` = " . $db->Quote($userId);
		$query .= " and a.`type` = " . $db->Quote(SOCIAL_TYPE_USER);

		if ($types) {
			if($types == 'invited') {
				$query .= " and b.`type` = " . $db->Quote(SOCIAL_GROUPS_INVITE_TYPE);
			} else if ($types == 'participated') {
				if ($my->id != $userId) {
					$query .= " AND (";
					$query .= " (b.`type` IN (" . $db->Quote(SOCIAL_GROUPS_PRIVATE_TYPE) . "," . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ")) OR ";
					$query .= "	(b.`type` > 1 and (select count(*) from `#__social_clusters_nodes` as aa where aa.`cluster_id` = b.`id` and aa.`uid` = $my->id) > 0)";
					$query .= " )";
				}
			} else {
				$query .= " and b.`type` IN (" . $db->Quote(SOCIAL_GROUPS_PRIVATE_TYPE) . "," . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ")";
			}
		}

		if (ES::isBlockEnabled()) {
			$query .= " and `bus`.`id` IS NULL";
		}

		// echo str_ireplace('#__', 'jos_', $query);
		// echo '<br><br>';
		// exit;

		$db->setQuery($query);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Get a list of groups from this user
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUserGroups($userId, $limitstart = null, $limit = null, $search = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.`cluster_id` from `#__social_clusters_nodes` as a';
		$query .= '	inner join `#__social_clusters` as b on a.`cluster_id` = b.`id`';
		$query .= '		and b.`cluster_type` = ' . $db->Quote(SOCIAL_TYPE_GROUP) . ' and b.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= ' where a.`uid` = ' . $db->Quote($userId);
		$query .= ' and a.`state` = ' . $db->Quote(SOCIAL_GROUPS_MEMBER_PUBLISHED);

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

		$groups = array();

		if ($ids) {
			$groups = ES::group()->loadGroups($ids);
		}

		return $groups;
	}



	/**
	 * Get a list of groups from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getGroups($filter = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		if (!empty($filter['location'])) {
			// If this is a location based search, then we want to include distance column
			$searchUnit = strtoupper($this->config->get('general.location.proximity.unit','mile'));

			$unit = constant('SOCIAL_LOCATION_UNIT_' . $searchUnit);
			$radius = constant('SOCIAL_LOCATION_RADIUS_' . $searchUnit);

			$lat = $filter['latitude'];
			$lng = $filter['longitude'];

			if (!$lat && !$lng) {
				// lets get the lat and lon from current logged in user address
				$my = ES::user();
				$address = $my->getFieldValue('ADDRESS');
				$lat = $address->value->latitude ? $address->value->latitude : 0;
				$lng = $address->value->longitude ? $address->value->longitude : 0;
			}

			// If there is a distance provided, then we need to put the distance column into a subquery in order to filter condition on it
			if (!empty($filter['distance'])) {
				$distance = $filter['distance'];

				$lat1 = $lat - ($distance / $unit);
				$lat2 = $lat + ($distance / $unit);

				$lng1 = $lng - ($distance / abs(cos(deg2rad($lat)) * $unit));
				$lng2 = $lng + ($distance / abs(cos(deg2rad($lat)) * $unit));

				$query[] = "SELECT DISTINCT `a`.`id`, `a`.`distance` FROM (
					SELECT `x`.*, ($radius * acos(cos(radians($lat)) * cos(radians(`x`.`latitude`)) * cos(radians(`x`.`longitude`) - radians($lng)) + sin(radians($lat)) * sin(radians(`x`.`latitude`)))) AS `distance` FROM `#__social_clusters` AS `x` WHERE `x`.`cluster_type` = " . $db->q(SOCIAL_TYPE_GROUP) . " AND (cast(`x`.`latitude` AS DECIMAL(10, 6)) BETWEEN $lat1 AND $lat2) AND (cast(`x`.`longitude` AS DECIMAL(10, 6)) BETWEEN $lng1 AND $lng2)
				) AS `a`";
			} else {
				$query[] = "SELECT DISTINCT `a`.`id`, ($radius * acos(cos(radians($lat)) * cos(radians(`a`.`latitude`)) * cos(radians(`a`.`longitude`) - radians($lng)) + sin(radians($lat)) * sin(radians(`a`.`latitude`)))) AS `distance` FROM `#__social_clusters` AS `a`";
			}
		} else {
			$query[] = "SELECT DISTINCT `a`.`id` AS `id` FROM `#__social_clusters` AS `a`";
		}

		if (ES::isBlockEnabled()) {
			$query[] = 'LEFT JOIN `#__social_block_users` AS `bus`';
			$query[] = 'ON (a.`creator_uid` = bus.`user_id`';
			$query[] = 'AND bus.`target_id` = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = 'OR a.`creator_uid` = bus.`target_id`';
			$query[] = 'AND bus.`user_id` = ' . $db->Quote(JFactory::getUser()->id) . ')';
		}

		$invited = $this->normalize($filter, 'invited', '');

		if ($invited) {
			$query[] = 'INNER JOIN `#__social_clusters_nodes` AS b';
			$query[] = 'ON b.`cluster_id` = a.`id`';
		}

		$types = $this->normalize($filter, 'types', '');

		if ($types != 'all') {
			$userid = $this->normalize($filter, 'userid', ES::user()->id);

			// currentuser type is currently used in groups module.
			if ($types === 'currentuser' && $userid) {
				$query[] = 'INNER JOIN `#__social_clusters_nodes` AS `nodes`';
				$query[] = 'ON a.`id` = nodes.`cluster_id`';
			} else if ($types === 'user' && $userid) {
				$query[] = 'LEFT JOIN `#__social_clusters_nodes` AS `nodes`';
				$query[] = 'ON a.`id` = nodes.`cluster_id`';
			} else if ($types == 'participated' && $userid) {
				$query[] = 'LEFT JOIN `#__social_clusters_nodes` AS `nodes`';
				$query[] = 'ON a.`id` = nodes.`cluster_id`';
			}
		}

		// Determines if there are ordering options supplied
		$ordering = $this->normalize($filter, 'ordering', 'latest');

		if ($ordering == 'members') {
			$query[] = 'INNER JOIN `#__social_clusters_nodes` AS f';
			$query[] = 'ON f.`cluster_id` = a.`id`';
			$query[] = 'AND f.`state`=' . $db->Quote(SOCIAL_GROUPS_MEMBER_PUBLISHED);
		}

		$query[] = 'WHERE a.`cluster_type`=' . $db->Quote(SOCIAL_TYPE_GROUP);

		$category = $this->normalize($filter, 'category', '');

		if ($category) {

			if (is_array($category)) {
				$category = implode(',', $db->Quote($category));
			} else {
				$category = $db->Quote($category);
			}

			$query[] = 'AND a.`category_id` IN (' . $category . ')';
		}

		// Test to filter by creator
		$uid = $this->normalize($filter, 'uid', '');

		if ($uid && $types != 'participated' && !$invited) {
			$query[] = 'AND a.`creator_uid` = ' . $db->Quote($uid);
			$query[] = 'AND a.`creator_type` = ' . $db->Quote(SOCIAL_TYPE_USER);
		}

		if ($invited) {
			$query[] = 'AND b.`state`= ' . $db->Quote(SOCIAL_GROUPS_MEMBER_INVITED);
			$query[] = 'AND b.`uid`=' . $db->Quote($invited);
		}

		// Test to filter featured items
		$featured = $this->normalize($filter, 'featured', '');

		if ($featured !== '') {
			$query[] = 'AND a.`featured`=' . $db->Quote($featured);
		}

		// Search by group title
		$search = $this->normalize($filter, 'search', null);

		if (!is_null($search)) {
			$query[] = 'AND a.`title` LIKE ' . $db->Quote('%' . $search . '%');
		}

		// Test if there is an inclusion
		$inclusion = $this->normalize($filter, 'inclusion', '');

		if ($inclusion !== '') {
			$inclusion = ES::makeArray($inclusion);

			$query[] = 'AND a.`id` IN (' . implode(',', $inclusion) . ')';
		}

		if ($types != 'all') {

			$userid = (int) isset($filter['userid']) ? $filter['userid'] : ES::user()->id;

			// currentuser type is currently used in groups module.
			if ($types === 'currentuser' && $userid) {
				$query[] = 'AND nodes.`uid`=' . $db->Quote($userid);
				$query[] = 'AND nodes.`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED);
			} else if ($types === 'user' && $userid) {
				$query[] = 'AND (';
				$query[] = '	(a.`type` IN (' . $db->Quote(SOCIAL_GROUPS_PRIVATE_TYPE) . ',' . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ',' . $db->Quote(SOCIAL_GROUPS_SEMI_PUBLIC_TYPE) . '))';
				$query[] = '	OR';
				$query[] = '	(';
				$query[] = '		a.`type`=' . $db->Quote(SOCIAL_GROUPS_INVITE_TYPE);
				$query[] = '		AND nodes.`uid`=' . $db->Quote($userid);
				$query[] = '	)';
				$query[] = ')';
			} else if ($types == 'participated' && $userid) {
				$query[] = 'AND nodes.`uid` = ' . $db->Quote($userid);
				$query[] = 'AND nodes.`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED);

				$my = ES::user();
				if ($my->id != $userid) {
					$query[] = " AND (";
					$query[] = " (a.`type` IN (" . $db->Quote(SOCIAL_GROUPS_PRIVATE_TYPE) . "," . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ")) OR ";
					$query[] = " (a.`type` > 1 and (select count(*) from `#__social_clusters_nodes` as aa where aa.`cluster_id` = a.`id` and aa.`uid` = $my->id) > 0)";
					$query[] = ")";
				} else {
					$query[] = 'AND nodes.`uid` = ' . $db->Quote($userid);
				}

			} else {
				$query[] = 'AND a.`type` IN (' . $db->Quote(SOCIAL_GROUPS_PRIVATE_TYPE) . ',' . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ',' . $db->Quote(SOCIAL_GROUPS_SEMI_PUBLIC_TYPE) . ')';
			}
		}

		// Test to filter published / unpublished groups
		$state = $this->normalize($filter, 'state', SOCIAL_STATE_PUBLISHED);
		$query[] = 'AND a.`state`= ' . $db->Quote($state);

		if (ES::isBlockEnabled()) {
			$query[] = "AND `bus`.`id` IS NULL";
		}


		// Determines if there are ordering options supplied
		$ordering = $this->normalize($filter, 'ordering', 'latest');
		$cntSQL = '';

		if ($ordering == 'members') {
			// lets get the sql without the order by condition.
			$cntSQL = $query;

			$query[] = 'GROUP BY a.`id`';
			$query[] = 'ORDER BY COUNT(f.`id`) DESC';
		} else {

			// lets get the sql without the order by condition.
			$cntSQL = $query;

			if ($ordering == 'popular') {
				$query[] = 'ORDER BY a.`hits` DESC';
			}

			if ($ordering == 'latest') {
				$query[] = 'ORDER BY a.`created` DESC';
			}

			if ($ordering == 'random') {
				$query[] = 'ORDER BY RAND() DESC';
			}

			if ($ordering == 'name') {
				$query[] = 'ORDER BY a.`title` ASC';
			}
		}

		$limit = $this->normalize($filter, 'limit', '');

		$cntSQL = $sql->raw($cntSQL);
		$sql = implode(' ', $query);

		// echo $sql;exit;

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->input->get('limitstart', 0, 'int');
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);
			$this->setTotal($cntSQL, true);
			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);

			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$groups = array();

		foreach ($result as $row) {
			$group = ES::group($row->id);

			// Manually assign the distance data
			if (!empty($filter['location'])) {
				$group->distance = round($row->distance, 1);
			}

			$groups[] = $group;
		}

		return $groups;
	}

	/**
	 * Retrieves the meta data of a list of groups
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMeta($ids = array())
	{
		static $loaded = array();

		// Store items that needs to be loaded
		$loadItems 	= array();

		foreach ($ids as $id) {
			$id 	= (int) $id;

			if (!isset($loaded[ $id ])) {
				$loadItems[]	= $id;

				// Initialize this with a false value first.
				$loaded[ $id ]	= false;
			}
		}

		// Determines if there is new items to be loaded
		if ($loadItems) {
			$db		= ES::db();
			$sql 	= $db->sql();

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
			}
			else
			{
				$sql->where('a.id', $loadItems[0]);
			}

			$sql->where('a.cluster_type', SOCIAL_TYPE_GROUP);

			// Debugging mode
			// echo $sql->debug();

			$db->setQuery($sql);

			$groups 	= $db->loadObjectList();

			if ($groups) {
				foreach ($groups as $group) {
					$loaded[ $group->id ]	= $group;
				}
			}
		}

		// Format the return result
		$data		= array();

		foreach ($ids as $id) {
			$data[] 	= $loaded[ $id ];
		}

		return $data;
	}

	/**
	 * Retrieves the total number of pending groups from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPendingCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('COUNT(1)', 'count');
		$sql->where('a.cluster_type', SOCIAL_TYPE_GROUP);
		$sql->where('a.state', array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');

		$db->setQuery($sql);

		$total 		= (int) $db->loadResult();

		return $total;
	}

	/**
	 * Returns the total number of clusters created by a given node
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 		The unique id of the creator.
	 * @param	string 		The unique type of the creator.
	 * @return
	 */
	public function getTotalCreated($uid, $type)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_clusters');
		$sql->column('count(1)');
		$sql->where('creator_uid', $uid);
		$sql->where('creator_type', $type);
		$sql->where('cluster_type', SOCIAL_TYPE_GROUP);

		$db->setQuery($sql);
		$total 	= $db->loadResult();

		if (!$total) {
			return 0;
		}

		return $total;
	}

	/**
	 * Determines if the user is an admin of the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isAdmin($userId, $groupId)
	{
		$db 	= ES::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $groupId);
		$sql->where('admin', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);

		$isAdmin 	= $db->loadResult() > 0;

		return $isAdmin;
	}

	/**
	 * Determines if the user is an owner of the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isOwner($userId, $groupId)
	{
		$db 	= ES::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $groupId);
		$sql->where('owner', SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);

		$isOwner 	= $db->loadResult() > 0;

		return $isOwner;
	}

	/**
	 * Determines if the user is a member of the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isInvited($userId, $groupId)
	{
		$db 	= ES::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $groupId);
		$sql->where('state', SOCIAL_GROUPS_MEMBER_INVITED);

		$db->setQuery($sql);

		$isMember 	= $db->loadResult() > 0;

		return $isMember;
	}

	/**
	 * Retrieves total number of friends in the group
	 *
	 * @since	1.4
	 * @access	public
	 * @param	int		The group id
	 * @param	Array	An array of options
	 * @return
	 */
	public function getTotalFriendsInGroup($groupId, $options = array())
	{
		$db 	= ES::db();
		$query	= array();

		$query[]	= 'SELECT COUNT(DISTINCT(a.uid)) FROM ' . $db->nameQuote('#__social_clusters_nodes') . ' AS ' . $db->nameQuote('a');
		$query[]	= 'INNER JOIN ' . $db->nameQuote('#__social_friends') . ' AS ' . $db->nameQuote('b');
		$query[]	= 'ON(';

		$query[]	= '(';
		$query[]	= 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('actor_id') . ' AND b.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($options[ 'userId' ]);
		$query[]	= ')';
		$query[]	= 'OR';
		$query[]	= '(';
		$query[]	= 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('target_id') . ' AND b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($options[ 'userId' ]);
		$query[]	= ')';

		$query[]	= ')';
		$query[]	= 'AND b.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[]	= 'WHERE a.' . $db->nameQuote('cluster_id') . '=' . $db->Quote($groupId);

		$publishedOnly 	= isset($options['published']) ? $options['published'] : false;

		if ($publishedOnly) {
			$query[]	= 'AND (';
			$query[]	= 'a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_GROUPS_MEMBER_PUBLISHED);
			$query[]	= ')';
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of friends from a particular group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFriendsInGroup($groupId, $options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT DISTINCT(a.uid) FROM ' . $db->nameQuote('#__social_clusters_nodes') . ' AS ' . $db->nameQuote('a');

		$showNonFriend = $this->config->get('groups.invite.nonfriends');

		if (!$showNonFriend) {
			$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_friends') . ' AS ' . $db->nameQuote('b');
			$query[] = 'ON(';

			$query[] = '(';
			$query[] = 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('actor_id') . ' AND b.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($options[ 'userId' ]);
			$query[] = ')';
			$query[] = 'OR';
			$query[] = '(';
			$query[] = 'a.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('target_id') . ' AND b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($options[ 'userId' ]);
			$query[] = ')';

			$query[] = ')';
			$query[] = 'AND b.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		}

		$query[] = 'WHERE a.' . $db->nameQuote('cluster_id') . '=' . $db->Quote($groupId);

		$publishedOnly = isset($options['published']) ? $options['published'] : false;
		$invited = isset($options['invited']) ? $options['invited'] : false;

		if ($publishedOnly) {
			$query[] = 'AND (';
			$query[] = 'a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_GROUPS_MEMBER_PUBLISHED);

			if ($invited) {
				$query[] = 'OR a.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_GROUPS_MEMBER_INVITED);
			}
			$query[] = ')';
		}

		$db->setQuery($query);
		$result	= $db->loadColumn();

		if (!$result) {
			return $result;
		}

		$users 	= array();
		foreach ($result as $id) {
			$user = ES::user($id);

			$users[] = $user;
		}

		return $users;
	}

	/**
	 * Determines if the user is a pending member of the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isPendingMember($userId, $groupId)
	{
		$db 	= ES::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $groupId);
		$sql->where('state', SOCIAL_GROUPS_MEMBER_PENDING);

		$db->setQuery($sql);

		$pending 	= $db->loadResult() > 0;

		return $pending;
	}

	/**
	 * Determines if the user is a member of the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isMember($userId, $groupId)
	{
		$db 	= ES::db();

		$sql	= $db->sql();

		$sql->select('#__social_clusters_nodes');
		$sql->column('COUNT(1)');
		$sql->where('uid', $userId);
		$sql->where('type', SOCIAL_TYPE_USER);
		$sql->where('cluster_id', $groupId);
		$sql->where('state', SOCIAL_GROUPS_MEMBER_PUBLISHED);

		$db->setQuery($sql);

		$isMember 	= $db->loadResult() > 0;

		return $isMember;
	}

	/**
	 * Determines if an email exists in this group
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isEmailExists($email, $groupId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');
		$sql->join('#__users', 'b');
		$sql->on('a.uid', 'b.id');
		$sql->where('b.email', $email);
		$sql->where('a.type', SOCIAL_TYPE_USER);
		$sql->where('a.cluster_id', $groupId);
		$sql->where('a.state', SOCIAL_GROUPS_MEMBER_PUBLISHED);

		$db->setQuery($sql);

		$exists = $db->loadResult() > 0;

		return $exists;
	}

	/**
	 * Create new group on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function createGroup(SocialTableStepSession &$session)
	{
		$config = ES::config();

		// Set the basic group details here.
		// Other group details should be fulfilled by the respective custom fields.
		ES::import('admin:/includes/group/group');

		$group = new SocialGroup();
		$group->creator_uid = ES::user()->id;
		$group->creator_type = SOCIAL_TYPE_USER;
		$group->category_id = $session->uid;
		$group->cluster_type = SOCIAL_TYPE_GROUP;
		$group->hits = 0;
		$group->created = ES::date()->toSql();

		// Load the group category
		$category = ES::table('GroupCategory');
		$category->load($session->uid);

		// Generate a unique key for this group which serves as a password
		$group->key = md5(JFactory::getDate()->toSql() . ES::user()->password . uniqid());

		// Load up the values which the user inputs
		$param = ES::get('Registry');

		// Bind the JSON values.
		$param->bind($session->values);

		// Convert the data into an array of result.
		$data = $param->toArray();

		$model = ES::model('Fields');

		// Get all published fields for the group.
		// $fields = $model->getCustomFieldsForNode($session->uid, SOCIAL_TYPE_CLUSTERS);
		$fields = $model->getCustomFields(array('workflow_id' => $category->getWorkflow()->id, 'group' => SOCIAL_TYPE_GROUP, 'visible' => SOCIAL_GROUPS_VIEW_REGISTRATION));

		// Pass in data and new user object by reference for fields to manipulate
		$args = array(&$data, &$group);

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		// @trigger onRegisterBeforeSave
		$lib = ES::getInstance('Fields');

		// Get the trigger handler
		$handler = $lib->getHandler();

		// Trigger onRegisterBeforeSave
		$errors = $lib->trigger('onRegisterBeforeSave', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args, array($handler, 'beforeSave'));

		// If there are any errors, throw them on screen.
		if (is_array($errors)) {

			if (in_array(false, $errors, true)) {
				$this->setError($errors);
				return false;
			}
		}

		// If there is still no alias generated, we need to automatically build one for the group
		if (!$group->alias) {
			$group->alias = $this->getUniqueAlias($group->getName());
		}

		// If groups required to be moderated, unpublish it first.
		$my = ES::user();
		$group->state = $my->getAccess()->get('groups.moderate') ? SOCIAL_CLUSTER_PENDING : SOCIAL_CLUSTER_PUBLISHED;

		// If the creator is a super admin, they should not need to be moderated
		if ($my->isSiteAdmin()) {
			$group->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$group, &$my, true);

		// @trigger: onGroupBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onGroupBeforeSave', $triggerArgs);

		// Let's try to save the user now.
		$state = $group->save();

		// If there's a problem saving the user object, set error message.
		if (!$state) {
			$this->setError($group->getError());
			return false;
		}

		// Send e-mail notification to site admin to approve / reject the group.
		if ($my->getAccess()->get('groups.moderate') && !$my->isSiteAdmin()) {
			$this->notifyAdminsModeration($group);
		} else {

			// If the creator is a site admin, we don't need to notify the admins
			if (!$my->isSiteAdmin()) {
				$this->notifyAdmins($group);
			}
		}

		// Once the group is stored, we just re-load it with the proper data
		$group = ES::group($group->id);

		// After the group is created, assign the current user as the node item
		$group->createOwner($my->id);

		// Reform the args with the binded custom field data in the user object
		$args = array(&$data, &$group);

		// Allow fields app to make necessary changes if necessary. At this point, we wouldn't want to allow
		// the field to stop the registration process already.
		// @trigger onRegisterAfterSave
		$lib->trigger('onRegisterAfterSave', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args);

		// Bind custom fields for this user.
		$group->bindCustomFields($data);

		// @trigger onRegisterAfterSaveFields
		$lib->trigger('onRegisterAfterSaveFields', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args);

		// @trigger: onGroupAfterSave
		$triggerArgs = [&$group, &$my, true];
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onGroupAfterSave', $triggerArgs);

		// We need to set the "data" back to the registration table
		$newData = json_encode($data);
		$session->values = $newData;

		return $group;
	}

	/**
	 * Notify site admins that a group is created
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function notifyAdmins(SocialGroup $group)
	{
		if (!$this->config->get('notifications.email.enabled')) {
			return;
		}

		// Push arguments to template variables so users can use these arguments
		$params 	= array(
								'title'			=> $group->getName(),
								'creatorName'	=> $group->getCreator()->getName(),
								'creatorLink'	=> $group->getCreator()->getPermalink(false, true),
								'categoryTitle'	=> $group->getCategory()->get('title'),
								'avatar'		=> $group->getAvatar(SOCIAL_AVATAR_LARGE),
								'permalink'		=> $group->getPermalink(true, true),
								'alerts'		=> false
						);

		// Set the e-mail title
		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_GROUP_CREATED_MODERATOR_EMAIL_TITLE', $group->getName());

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		foreach ($admins as $admin) {

			// Immediately send out emails
			$mailer = ES::mailer();

			// Set the admin's name.
			$params[ 'adminName' ] = $admin->name;

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($admin->name, $admin->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the template
			$mailTemplate->setTemplate('site/group/created', $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Searches for groups
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function search($keyword, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('cluster_type', SOCIAL_TYPE_GROUP);
		$sql->where('title', '%' . $keyword . '%', 'LIKE');

		// Determines if we should search for unpublished groups as well
		$unpublished = isset($options['unpublished']) && $options['unpublished'] ? true : false;

		if (!$unpublished) {
			$sql->where('state', SOCIAL_STATE_PUBLISHED);
		}

		// Determines if we should exclude specific group ids
		$exclusion = isset($options['exclusion']) && $options['exclusion'] ? $options['exclusion'] : false;

		if ($exclusion) {
			$exclusion = ES::makeArray($exclusion);

			$sql->where('id', $exclusion, 'NOT IN');
		}

		$db->setQuery($sql);
		$result = $db->loadObjectList();
		$groups = array();

		if (!$result) {
			return $groups;
		}

		foreach ($result as $row) {
			$group = ES::group();
			$group->bind($row);

			$groups[] = $group;
		}

		return $groups;
	}

	/**
	 * Notify site admins that a group is created and it is pending moderation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function notifyAdminsModeration(SocialGroup $group, $edited = false)
	{
		if (!$this->config->get('notifications.email.enabled')) {
			return;
		}

		// Push arguments to template variables so users can use these arguments
		$params = array(
							'title' => $group->getName(),
							'creatorName' => $group->getCreator()->getName(),
							'categoryTitle' => $group->getCategory()->get('title'),
							'avatar' => $group->getAvatar(SOCIAL_AVATAR_LARGE),
							'permalink' => JURI::root() . 'administrator/index.php?option=com_easysocial&view=groups&layout=pending',
							'reject' => ESR::controller('groups', array('external' => true, 'task' => 'rejectGroup', 'id' => $group->id, 'key' => $group->key)),
							'approve' => ESR::controller('groups', array('external' => true, 'task' => 'approveGroup', 'id' => $group->id, 'key' => $group->key)),
							'alerts' => false
						);

		$params['type'] = $edited ? 'EDITED' : 'CREATED';

		// Set the e-mail title
		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_GROUP_' . $params['type'] . '_MODERATOR_EMAIL_TITLE', $group->getName());

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
			$mailTemplate->setTemplate('site/group/moderate', $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	public function getGroupsGDPR($userId, $excludeId, $limit = 20)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.`cluster_id` FROM ' . $db->nameQuote('#__social_clusters_nodes') . ' AS a';
		$query[] = 'LEFT JOIN ' . $db->nameQuote('#__social_clusters') . ' AS b';
		$query[] = 'ON a.' . $db->quoteName('cluster_id') . ' = b.`id`';
		$query[] = 'WHERE a.' . $db->quoteName('uid') . ' = ' . $db->Quote($userId);
		$query[] = 'AND a.' . $db->quoteName('admin') . ' = ' . $db->Quote('1');
		$query[] = 'AND b.' . $db->quoteName('cluster_type') . ' = ' . $db->Quote('group');

		if ($excludeId) {
			$query[] = 'AND a.' . $db->quoteName('cluster_id') . ' NOT IN (' . implode(',', $excludeId) . ')';
		}

		$query[] = 'ORDER BY a.`id` DESC';
		$query[] = 'LIMIT ' . $limit;

		$query = implode(' ', $query);

		$db->setQuery($query);
		$data = $db->loadColumn();

		if (!$data) {
			return false;
		}

		$result = ES::group($data);

		return $result;
	}
}
