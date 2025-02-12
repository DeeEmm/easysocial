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

class EasySocialModel extends JModelLegacy
{
	/**
	 * Total number of records.
	 * @var	int
	 */
	protected $total = null;

	/**
	 * The database layer from Joomla.
	 * @var	JDatabase
	 */
	protected $db = null;

	/**
	 * The pagination object.
	 * @var SocialPagination
	 */
	protected $pagination = null;

	/**
	 * The element name.
	 * @var string
	 */
	protected $element = null;
	protected $key = null;

	// Implemented by child
	public $searchables = array();

	public function __construct($element = null, $config = [])
	{
		$this->db = ES::db();
		$this->input = ES::request();
		$this->element = $element;

		// Set the key element for this model.
		$index = 'com_easysocial';

		if (isset($config['namespace'])) {
			$index .= '.' . $config['namespace'];
		}

		$index .= '.' . $element;

		$this->key = $index;

		// We don't want to load any of the tables path because we use our own ES::table method.
		$options = array('table_path' => JPATH_ROOT . '/libraries/joomla/database/table');

		parent::__construct($options);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function initStates()
	{
		$app = JFactory::getApplication();
		$config = ES::config();
		$jConfig = ES::jConfig();

		// Get the system defined limit
		$systemLimit = $jConfig->getValue('list_limit');
		$systemLimit = $config->get($this->element . '.limit', $systemLimit);

		// Get the limit.
		$limit = $this->getUserStateFromRequest('limit', $systemLimit, 'int');

		// Get the limitstart.
		$limitstart = $this->getUserStateFromRequest('limitstart', 0);

		// Ensure that the value is always integer. #5218
		$limitstart = (int) $limitstart;
		$limit = (int) $limit;

		// dump($limitstart, $limit);exit;
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		// Get the search
		$search = $this->getUserStateFromRequest('search', '');

		// Get the ordering
		$ordering = $this->getUserStateFromRequest('ordering', 'id');

		// Get the direction
		$direction = $this->getUserStateFromRequest('direction', 'DESC');

		$this->setState('direction', $direction);
		$this->setState('ordering', $ordering);
		$this->setState('search', $search);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Get list of user id who block the user or being block by the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBlockedUserIds($userId)
	{
		static $_cache = [];

		if (!isset($_cache[$userId])) {

			$db = ES::db();

			$q = [];
			$q[] = "select if(bus.`user_id` = " . $db->Quote($userId) . ", bus.`target_id`, bus.`user_id`) as `userid`";
			$q[] = "from `#__social_block_users` AS bus";
			$q[] = "where (bus.`target_id` = " . $db->Quote($userId) . " OR bus.`user_id` = " . $db->Quote($userId) . ")";

			// glue the pieces
			$query = implode(" ", $q);

			$db->setQuery($query);
			$_cache[$userId] = $db->loadColumn();
		}

		return $_cache[$userId];
	}

	/**
	 * Get list of user id who block the user or being block by the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCommunityProfileTypeIds($ignoreESAD = false, $excludeUserListing = true)
	{
		static $_cache = [];

		$idx = (int) $ignoreESAD . '.' . (int) $excludeUserListing;

		if (!isset($_cache[$idx])) {

			$db = ES::db();

			$q = [];
			$cond = [];

			$q[] = "select up.`id` from `#__social_profiles` as up";

			if (!$ignoreESAD) {
				$cond[] = "up.`community_access` = 1";
			}
			
			if ($excludeUserListing) {
				$cond[] = "up.`exclude_userlisting` = 0"; 
			}

			// glue the condition.
			if ($cond) {
				$q[] = "WHERE " . implode(" AND ", $cond);
			}

			// glue the pieces
			$query = implode(" ", $q);

			$db->setQuery($query);
			$_cache[$idx] = $db->loadColumn();
		}

		return $_cache[$idx];
	}

	/**
	 * Get searchable columns
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getSearchableItems($query)
	{
		$query = explode(':', $query);

		if (count($query) == 1 || !$this->searchables) {
			return false;
		}

		$column = $query[0];

		if (!in_array(strtoupper($column), $this->searchables) && !in_array(strtolower($column), $this->searchables)) {
			return false;
		}

		$data = new stdClass();
		$data->column = $column;
		$data->query = $query[1];

		return $data;
	}

	/**
	 * Generates the table joining SQL query for user blocking features
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUserBlockingJoinQuery(SocialUser $viewer, $tableAlias = 'bus', $idColumn = 'user_id', $targetUserId = 0, $options = array())
	{
		$config = ES::config();

		// Check if this feature is enabled and this feature is not applicable to guests
		if (!$config->get('users.blocking.enabled') || $viewer->guest) {
			return '';
		}

		$clusterColumnAlias = isset($options['clusterColumnAlias']) ? $options['clusterColumnAlias'] : '';
		$respectClusterColumnType = isset($options['respectClusterColumnType']) ? $options['respectClusterColumnType'] : false;
		$idColumnTableAlias = isset($options['idColumnTableAlias']) ? $options['idColumnTableAlias'] : 'a';

		$targetId = $targetUserId ? $targetUserId : $viewer->id;

		$db = $this->db;
		$query = array();

		$query[] = 'LEFT JOIN `#__social_block_users` AS ' . $tableAlias;
		$query[] = 'ON (';

		$query[] = '(' . $idColumnTableAlias . '.' . $db->nameQuote($idColumn) . ' = ' . $tableAlias . '.`user_id`';
		$query[] = 'AND '. $tableAlias . '.`target_id` = ' . $db->Quote($targetId);

		if ($clusterColumnAlias) {
			$query[] = 'AND '. $tableAlias . '.`target_id` NOT IN (';
			$query[] = '  select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = ' . $idColumnTableAlias . '.' . $db->nameQuote($clusterColumnAlias);
			if ($respectClusterColumnType) {
				$query[] = '  and ' . $idColumnTableAlias . '.`type` != ' . $db->Quote(SOCIAL_TYPE_USER);
			}
			$query[] = '  and clsnd.`admin` = 1)';
		}

		$query[] = ') OR (';
		$query[] = $idColumnTableAlias . '.' . $db->nameQuote($idColumn) . ' = '. $tableAlias . '.`target_id`';
		$query[] = 'AND ' . $tableAlias . '.`user_id` = ' . $db->Quote($targetId);

		if ($clusterColumnAlias) {
			$query[] = 'AND '. $tableAlias . '.`user_id` NOT IN (';
			$query[] = '  select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where clsnd.`cluster_id` = ' . $idColumnTableAlias . '.' . $db->nameQuote($clusterColumnAlias);
			if ($respectClusterColumnType) {
				$query[] = '  and ' . $idColumnTableAlias . '.`type` != ' . $db->Quote(SOCIAL_TYPE_USER);
			}
			$query[] = '  and clsnd.`admin` = 1)';

		}
		$query[] = ')';

		// end.
		$query[] = ')';

		$query = implode(' ', $query);

		return $query;
	}

	/**
	 * Generates the table joining SQL query for user blocking features
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUserBlockingClauseQuery(SocialUser $viewer, $tableAlias = 'bus', $withAnd = true)
	{
		$config = ES::config();

		// Check if this feature is enabled and this feature is not applicable to guests
		if (!$config->get('users.blocking.enabled') || $viewer->guest) {
			return '';
		}

		$q = [];

		if ($withAnd) {
			$q[] = ' AND';
		}

		$q[] = $tableAlias . '.`id` IS NULL';

		$query = implode(' ', $q);
		return $query;
	}

	/**
	 * Generates SQL query for the clause of clusters
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getClusterPrivacyJoinQuery()
	{
		$query = array();

		$query[] = 'LEFT JOIN `#__social_clusters` AS `cls`';
		$query[] = 'ON a.`uid` = cls.`id`';
		$query[] = 'AND a.`type` = cls.`cluster_type`';

		$query = implode(' ', $query);

		return $query;
	}

	/**
	 * Generates SQL query for the clause of clusters
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getClusterPrivacyClauseQuery($viewerId, $mainTableAlias = 'a', $clusterTableAlias = 'cls', $joiner = 'AND')
	{
		$viewerId = (int) $viewerId;
		$viewer = ES::user($viewerId);
		$db = $this->db;
		$query = array();

		if ($viewer->isSiteAdmin()) {
			return '';
		}

		if ($joiner) {
			$query[] = $joiner;
		}

		$query[] = '(';

		$query[] = '(' . $mainTableAlias . '.`type` = ' . $db->Quote('user') . ') OR';

		// Public clusters
		$query[] = '(' . $clusterTableAlias . '.`type` IN(1, 4)) OR';

		// Viewer is a member of the cluster
		$query[] = '(';
		$query[] = '(' . $clusterTableAlias . '.`type` > 1) AND';
		$query[] = $db->Quote($viewerId) . ' IN(';
		$query[] = 'SELECT scn.`uid` FROM `#__social_clusters_nodes` AS `scn`';
		$query[] = 'WHERE scn.`cluster_id` = a.`uid`';
		$query[] = 'AND scn.`type` = ' . $db->Quote(SOCIAL_TYPE_USER);
		$query[] = 'AND scn.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = ')';
		$query[] = ')';


		$query[] = ')';

		$query = implode(' ', $query);

		return $query;
	}

	/**
	 * Generates SQL query for friends detection
	 *
	 * @since	3.2.0
	 * @access	public
	 */

	public function getIsFriendsQuery($source, $target)
	{
		$db = $this->db;

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM `#__social_friends`';
		$query[] = 'WHERE';
		$query[] = '(';
		$query[] = '(';
		$query[] = '`actor_id` = ' . $source . ' AND `target_id` = ' . $db->Quote($target);
		$query[] = 'OR';
		$query[] = '`target_id` = ' . $source . ' AND `actor_id` = ' . $db->Quote($target);
		$query[] = ')';
		$query[] = 'AND';
		$query[] = '`state`=1';
		$query[] = ')';

		$query = implode(' ', $query);

		return $query;
	}

	/**
	 * Generates SQL query for standard privacy checks
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getPrivacyQuery($viewerId, $customFieldPrivacyType, $tableAlias = 'a', $joiner = 'AND')
	{
		$config = ES::config();
		$db = $this->db;
		$query = array();


		$query[] = $joiner;
		$query[] = '(';

		// Audios that are public
		$query[] = '(' . $tableAlias . '.`access` =' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ')';
		$query[] = 'OR';

		// Member access
		$query[] = '((' . $tableAlias . '.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewerId . ' > 0))';
		$query[] = 'OR';

		// Friends access
		$friendsEnabled = $config->get('friends.enabled');


		if ($friendsEnabled) {
			$userIdColumn = $db->nameQuote($tableAlias . '.user_id');
			$query[] = '((' . $tableAlias . '.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->getIsFriendsQuery($userIdColumn, $viewerId) . ') > 0))';
			$query[] = 'OR';
		}

		// If friends is not enabled, fall back to member checks
		if (!$friendsEnabled) {
			$query[] = '((' . $tableAlias . '.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewerId . ' > 0))';
			$query[] = 'OR';
		}

		// Privacy of "Only Me"
		$query[] = '((' . $tableAlias . '.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (' . $tableAlias . '.`user_id`=' . $db->Quote($viewerId) . '))';
		$query[] = 'OR';

		// Custom privacy
		$query[] = '((' . $tableAlias . '.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (' . $tableAlias . '.`custom_access` LIKE ' . $db->Quote('%' . $viewerId . '%') . '))';
		$query[] = 'OR';

		// Privacy by custom field
		if ($config->get('users.privacy.field')) {
			// field
			$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
			$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
			$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
			$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
			$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
			$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote($customFieldPrivacyType);
			$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewerId);
			$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
			$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

			$query[] = '((' . $tableAlias . '.`access`=' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $tableAlias . '.`field_access` <=' . $fieldPrivacyQuery . ')) OR';
		} else {
			$query[] = '((' . $tableAlias . '.`access` =' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $db->Quote($viewerId) . ' > 0)) OR';
		}

		// Own audio items
		$query[] = '(' . $tableAlias . '.`user_id` = ' . $db->Quote($viewerId) . ')';

		// privacy checking end here.
		$query[] = ')';

		$query = implode(' ', $query);

		return $query;
	}

	/**
	 * Get user's state from request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUserStateFromRequest($key, $default = '', $type = 'none')
	{
		$app = JFactory::getApplication();
		$namespace = $this->key . '.' . $key;
		$value = $app->input->get($key, null, $type);

		// // Try to get the key first. If the key is not in the request, then the userstate won't go back to empty
		// if ($value == null) {
		// 	$app->setUserState($namespace, null);
		// }

		$value = $app->getUserStateFromRequest($namespace, $key, $default, $type);

		return $value;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 * 
	 * Deprecated. Call ES::normalize() directly.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function normalize($data, $key, $default = null)
	{
		return ES::normalize($data, $key, $default);
	}

	public function setUserState($key, $value)
	{
		$app = JFactory::getApplication();

		return $app->setUserState($this->key . '.' . $key, $value);
	}

	public function getUserState($key, $default = null)
	{
		$app = JFactory::getApplication();

		return $app->getUserState($this->key . '.' . $key, $default);
	}

	/**
	 * Overrides parent's setState
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setState($key, $value = null)
	{
		$namespace 	= $this->key . '.' . $key;

		parent::setState($namespace, $value);
	}

	/**
	 * Retrieve a list of state items
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getState($keyItem = null, $default = '')
	{
		$key = $this->key . '.' . $keyItem;
		$value = parent::getState($key, $default);

		return $value;
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function getTotal()
	{
		return $this->total;
	}

	/**
	 * Sets the limit state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setLimit($limit = null)
	{
		if (is_null($limit)) {
			$jConfig = ES::jconfig();
			$config = ES::config();

			$systemLimit = $jConfig->getValue('list_length');
			$app = JFactory::getApplication();
			$limit = $app->getUserStateFromRequest('com_easysocial.' . $this->element . '.limit', 'limit', $config->get($this->element . '.limit', $systemLimit), 'int');
		}

		$this->setState('limit', $limit);

		return $this;
	}


	/**
	 * Returns the pagination object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPagination($reload = null)
	{
		if ($this->pagination && !$reload) {
			return $this->pagination;
		}

		$limitstart = (int) $this->getState('limitstart');
		$limit = (int) $this->getState('limit');
		$total = (int) $this->getState('total');

		$this->pagination = ES::pagination($total, $limitstart, $limit);

		return $this->pagination;
	}

	/**
	 * Determines the total number of items based on the query given.
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function setTotal($query, $wrapTemporary = false)
	{
		if ($wrapTemporary) {
			$query 	= 'SELECT COUNT(1) FROM (' . $query . ') AS zcount';
		}

		$this->db->setQuery($query);

		$total = (int) $this->db->loadResult();

		// Set the total items here.
		$this->setState('total', $total);

		$this->total = $total;

		return $total;
	}

	/**
	 * Determines the total number of items based on the query given.
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function setTotalCount($total)
	{
		// Set the total items here.
		$this->setState('total', $total);
		$this->total = $total;
		return true;
	}

	/**
	 * If using the pagination query, child needs to use this method.
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function getData($query, $debug = false)
	{
		// If enforced to use limit, we get the limitstart values from properties.
		$limit = $this->getState('limit', null);
		$limitstart = $this->getState('limitstart', null);

		if (is_null($limit)) {
			$limit = 0;
		}

		if (is_null($limitstart)) {
			$limitstart = 0;
		}

		// Check if there's anything wrong with the limitstart because
		// User might be viewing on page 7 but switches a different view and it does not contain 7 pages.
		$total = $this->getTotal();

		if ($limitstart > $total) {
			$limitstart = 0;
			$this->setState('limitstart', 0);
		}

		if ($query instanceof SocialSql) {

			if ($limit) {
				$query->limit($limitstart, $limit);
			}

			$query = $query->getSql();

			$this->db->setQuery($query);
		} else {

			$this->db->setQuery($query, $limitstart, $limit);
		}

		return $this->db->loadObjectList();
	}

	protected function getDataColumn($query, $useLimit = true)
	{
		// If enforced to use limit, we get the limitstart values from properties.
		$limitstart = $useLimit ? $this->getState('limitstart') : 0;
		$limit 		= $useLimit ? $this->getState('limit') : 0;

		if ($query instanceof SocialSql) {

			if ($limit) {
				$query->limit($limitstart, $limit);
			}

			$query = $query->getSql();

			$this->db->setQuery($query);
		} else {
			$this->db->setQuery($query, $limitstart, $limit);
		}


		// $this->db->setQuery($query, $limitstart, $limit);

		return $this->db->loadColumn();
	}

	protected function bindTable($tableName, $result)
	{
		$binded = array();

		foreach ($result as $row) {
			$table = ES::table($tableName);
			$table->bind($row);

			$binded[] = $table;
		}

		return $binded;
	}
}
