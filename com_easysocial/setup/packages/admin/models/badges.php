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

class EasySocialModelBadges extends EasySocialModel
{
	private $_nextlimit = 0;

	/**
	 * Class constructor
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct($config = array())
	{
		parent::__construct('badges', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		$callback = $this->input->get('jscallback', '', 'default');
		$defaultFilter = $callback ? SOCIAL_STATE_PUBLISHED : 'all';

		$filter = $this->getUserStateFromRequest('state', $defaultFilter);
		$extension = $this->getUserStateFromRequest('extension', 'all');


		$this->setState('state', $filter);
		$this->setState('extension', $extension);

		parent::initStates();
	}

	/**
	 * Scans through the given path and see if there are any *.points file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan($path)
	{
		jimport('joomla.filesystem.folder');

		$files = array();

		if ($path == 'admin' || $path == 'components') {
			$directory = JPATH_ROOT . '/administrator/components';
		}

		if ($path == 'site') {
			$directory = JPATH_ROOT . '/components';
		}

		if ($path == 'apps') {
			$directory = SOCIAL_APPS;
		}

		if ($path == 'fields') {
			$directory = SOCIAL_FIELDS;
		}

		if ($path == 'plugins') {
			$directory = JPATH_ROOT . '/plugins';
		}

		if ($path == 'modules') {
			$directory = JPATH_ROOT . '/modules';
		}

		$files = JFolder::files($directory, '.badge$', true, true);

		return $files;
	}

	/**
	 * Determines if a badge exists for the user.
	 *
	 * @since	1.4.9
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function exists($userId, $badgeId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_badges_maps');
		$sql->where('user_id', $userId);
		$sql->where('badge_id', $badgeId);

		$db->setQuery($sql);

		$exists = $db->loadResult() > 0 ? true : false;

		return $exists;
	}

	/**
	 * Delete associations of a badge from a user.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The badge id
	 * @return	bool	True on success, false otherwise.
	 */
	public function deleteAssociations($badgeId, $userId = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		// @TODO: Trigger before deleting badge associations

		$sql->delete('#__social_badges_maps');
		$sql->where('badge_id', $badgeId);

		if (!empty($userId)) {
			$sql->where('user_id', $userId);
		}

		$db->setQuery($sql);

		$db->Query();

		// @TODO: Trigger after deleting badge associations

		return true;
	}

	/**
	 * Retrieve a number of users who achieved this badge.
	 *
	 * @since	1.0
	 * @access	public
	 * @return	int		The total number of users who achieved this badge.
	 */
	public function getTotalAchievers($badgeId)
	{
		$db = ES::db();
		$viewer = JFactory::getUser()->id;


		$q = [];
		$q[] = "select count(1) from `#__social_badges` as a";
		$q[] = "inner join `#__social_badges_maps` as b on a.id = b.badge_id";
		$q[] = "inner join `#__users` as uu on b.user_id = uu.id";
		// exclude esad users
		$q[] = "inner join `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`";
		$q[] = "inner join `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";
		if (ES::isBlockEnabled()) {
			$q[] = "LEFT JOIN `#__social_block_users` AS bus ON (b.`user_id` = bus.`user_id` AND bus.`target_id` = $viewer) OR (b.`user_id` = bus.`target_id` AND bus.`user_id` = $viewer)";
		}
		$q[] = "WHERE a.`id` = " . $db->Quote($badgeId);
		$q[] = "AND uu.`block` = 0";

		if (ES::isBlockEnabled()) {
			$q[] = "AND bus.`id` IS NULL";
		}

		// glue the pieces
		$query = implode(' ', $q);

		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of badges a user has
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The user's id.
	 * @return	int		The total number of users who achieved this badge.
	 */
	public function getTotalBadges($userId)
	{
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('badge_id') . ')) FROM ' . $db->quoteName('#__social_badges_maps') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->quoteName('#__social_badges') . ' AS b';
		$query[] = 'ON a.' . $db->quoteName('badge_id') . ' = b.' . $db->quoteName('id');
		$query[] = 'WHERE a.' . $db->quoteName('user_id') . '=' . $db->Quote($userId);
		$query[] = 'AND b.' . $db->quoteName('state') . '=' . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$total = $db->loadResult();
		return $total;
	}

	/**
	 * Retrieves the achievers of the provided badge id
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The badge id
	 * @return	Array	An array of SocialUser objects
	 */
	public function getAchievers($badgeId, $options = array())
	{
		$db = ES::db();

		$hasPageLimit = false;
		$currrentStart = false;
		$q = [];

		$config = ES::config();
		$my = ES::user();
		$streamLib = ES::stream();

		$viewer = JFactory::getUser()->id;

		$privacy = ES::table('Privacy');
		$privacy->load(array('type' => 'achievements', 'rule' => 'view'));

		$privId = $privacy->id;
		$privValue = $privacy->value;

		$respectPrivacy = !$my->isSiteAdmin() && $config->get('privacy.enabled') ? true : false;

		$q[] = "SELECT uu.`id` FROM (";
		$q[] = "  SELECT a.`user_id`";

		if ($respectPrivacy) {
			$q[] = ", ifnull(pri.`value`, $privValue) as `access`, concat(',', GROUP_CONCAT(pric.`user_id`), ',') as `custom_access`";
		}

		$q[] = "    FROM `#__social_badges_maps` AS a";
		$q[] = "    INNER JOIN `#__social_badges` AS b ON a.`badge_id` = b.`id`";
		$q[] = "    INNER JOIN `#__social_profiles_maps` AS upm ON a.`user_id` = upm.`user_id`";
		$q[] = "    INNER JOIN `#__social_profiles` AS up ON upm.`profile_id` = up.`id` AND up.`community_access` = 1";
		if (ES::isBlockEnabled()) {
			$q[] = "    LEFT JOIN `#__social_block_users` AS bus ON (a.`user_id` = bus.`user_id` AND bus.`target_id` = $viewer) OR (a.`user_id` = bus.`target_id` AND bus.`user_id` = $viewer)";
		}
		if ($respectPrivacy) {
			$q[] = "    LEFT JOIN `#__social_privacy_map` AS pri ON a.`user_id` = pri.`uid` AND pri.`utype` = 'user' AND pri.`privacy_id` = $privId";
			$q[] = "    LEFT JOIN `#__social_privacy_customize` AS pric ON pri.`id` = pric.`uid` AND pric.`utype` = 'user'";
		}
		$q[] = "    WHERE a.`badge_id` = " . $db->Quote($badgeId);
		if (ES::isBlockEnabled()) {
			$q[] = "    AND bus.`id` IS NULL";
		}

		if ($respectPrivacy) {
			$q[] = "    GROUP BY a.`user_id`";
		}
		$q[] = ") AS x";
		$q[] = "INNER JOIN `#__users` AS uu ON x.`user_id` = uu.`id`";
		$q[] = "WHERE uu.`block` = 0";

		if ($respectPrivacy) {
			// privacy here.
			$q[] = 'AND (';

			//public
			$q[] = '(x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends of friends
				$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateMutualFriendSQL($viewer, 'x.`user_id`') . ') > 0)) OR ';

				//friends
				$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('x.`user_id`', $viewer) . ') > 0)) OR ';

				//friends
				$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('x.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to 'member'
				$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
				$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (x.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$q[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (x.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '   )) OR ';

			// my own items.
			$q[] = '(x.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$q[] = ')';
		}

		if (isset($options['limit']) && isset($options['start'])) {
			$hasPageLimit = $options['limit'];
			$currrentStart = $options['start'];

			$limitstart = $options['start'];
			$limit = $options['limit'] + 1;

			$q[] = "LIMIT $limitstart, $limit";
		}

		// $q[] = "Select a.`user_id` from `#__social_badges_maps` as a";
		// $q[] = "INNER JOIN `#__social_badges` as b on a.`badge_id` = b.`id`";
		// $q[] = "INNER JOIN `#__users` as uu on a.`user_id` = uu.`id`";
		// $q[] = "INNER JOIN `#__social_profiles_maps` as upm on uu.`id` = upm.`user_id`";
		// $q[] = "INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";
		// if (ES::isBlockEnabled()) {
		// 	$q[] = "LEFT JOIN `#__social_block_users` as bus on";
		// 	$q[] = " (uu.id = bus.user_id and bus.target_id = $userId)";
		// 	$q[] = " OR (uu.id = bus.target_id and bus.user_id = $userId)";
		// }

		// $q[] = "where a.badge_id = " . $db->Quote($badgeId);
		// $q[] = "and uu.block = 0";
		// if (ES::isBlockEnabled()) {
		// 	$q[] = "and bus.id IS NULL";
		// }

		// if (isset($options['limit']) && isset($options['start'])) {
		// 	$hasPageLimit = $options['limit'];
		// 	$currrentStart = $options['start'];

		// 	$limitstart = $options['start'];
		// 	$limit = $options['limit'] + 1;

		// 	$q[] = "LIMIT $limitstart, $limit";
		// }

		// glue the q pieces
		$query = implode(" ", $q);

		$db->setQuery($query);
		$rows = $db->loadColumn();

		if (!$rows) {
			$this->_nextlimit = 0;
			return $rows;
		}

		if ($hasPageLimit !== false && count($rows) > $hasPageLimit) {
			// remove the last elements;
			array_pop($rows);
			$this->_nextlimit = $currrentStart + $hasPageLimit;
		}

		$users 	= ES::user($rows);
		return $users;
	}

	public function getNextLimit()
	{
		return $this->_nextlimit;
	}

	/**
	 * Retrieve a list of badges from the site
	 *
	 * @since	1.0
	 * @access	public
	 * @return	bool	True if user had already achieved this badge.
	 */
	public function getExtensions()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_badges');
		$sql->column('DISTINCT `extension`');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$extension 	= array();

		foreach ($result as $row) {
			$extensions[] = $row->extension;
		}

		return $extensions;
	}

	/**
	 * Retrieve a list of badges from the site
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of options
	 * @return	Array	An array of SocialBadgeTable objects.
	 */
	public function getItemsWithState($options = array())
	{
		$db 		= ES::db();
		$sql 		= $db->sql();

		$sql->select('#__social_badges');

		$extension 	= $this->getState('extension');

		if ($extension != 'all' && !is_null($extension)) {
			$sql->where('extension', $extension);
		}

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		// Check for state
		$state = $this->getState('state');

		if ($state != 'all' && !is_null($state)) {
			$sql->where('state', $state);
		}

		$limit 	= $this->getState('limit');
		// $limit 	= isset($options[ 'limit' ]) ? $options[ 'limit' ] : 0;

		if ($limit != 0) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart 	= $this->getUserStateFromRequest('limitstart', 0);
			$limitstart 	= ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$badges = array();

		foreach ($result as $row) {
			$badge 	= ES::table('Badge');
			$badge->bind($row);

			$badges[] = $badge;
		}

		return $badges;
	}

	/**
	 * Retrieve a list of badges from the site
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of options
	 * @return	Array	An array of SocialBadgeTable objects.
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$pointsRuleId = isset($options['pointsRuleId']) ? $options['pointsRuleId'] : null;
		$achieveType = isset($options['achieveType']) ? $options['achieveType'] : 'frequency';

		$sql->select('#__social_badges');

		$extension 	= $this->getState('extension');

		if ($extension != 'all' && !is_null($extension) && $extension != '') {
			$sql->where('extension', $extension);
		}

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Achieve type
		if ($achieveType != 'all') {
			$sql->where('achieve_type', $achieveType);
		}

		// Specific points rule
		if ($pointsRuleId) {
			$sql->where('(', '', '', 'AND');
			$sql->where('points_increase_rule', $pointsRuleId, '=', 'OR');
			$sql->where('points_decrease_rule', $pointsRuleId, '=', 'OR');
			$sql->where(')');
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		// Check for state
		$state = isset($options[ 'state' ]) ? $options[ 'state' ] : null;

		if (!is_null($state)) {
			$sql->where('state', $state);
		}

		$limit = isset($options[ 'limit' ]) ? $options[ 'limit' ] : 0;

		if ($limit != 0) {

			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$result = $this->getData($sql);

		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$badges 	= array();

		foreach ($result as $row) {
			$badge 	= ES::table('Badge');
			$badge->bind($row);

			$badge->loadLanguage();

			$badges[] = $badge;
		}

		return $badges;
	}

	/**
	 * Retrieves a list of badges earned by a specific user.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getBadges($userId)
	{
		$db = ES::db();

		$q = [];
		$q[] = "SELECT a.*, b.`custom_message`, b.`created` AS achieved_date";
		$q[] = "FROM `#__social_badges` AS a";
		$q[] = "INNER JOIN `#__social_badges_maps` AS b ON a.`id` = b.`badge_id`";
		$q[] = "WHERE b.`user_id` = " . $db->Quote($userId);
		$q[] = "AND a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		// clue the query
		$query = implode(" ", $q);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$badges = array();
		$loadedLanguage = array();

		foreach ($result as $row) {
			$badge = ES::table('Badge');
			$badge->bind($row);
			$badge->achieved_date = $row->achieved_date;
			$badge->custom_message = $row->custom_message;
			$badges[] = $badge;
		}

		return $badges;
	}

	/**
	 * Determines if the user has achieved the badge before.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique badge id.
	 * @param	int		The user's id.
	 * @return	bool	True if user had already achieved this badge.
	 */
	public function hasAchieved($badgeId, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Build the column selection
		$sql->select('#__social_badges_maps');

		// Build the where
		$sql->where('user_id', $userId);
		$sql->where('badge_id', $badgeId);

		// Execute this
		$db->setQuery($sql->getTotalSql());

		$achieved = $db->loadResult() > 0;

		return $achieved;
	}

	/**
	 * Delete history of a badge from a user.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The badge id
	 * @return	bool	True on success, false otherwise.
	 */
	public function deleteHistory($badgeId, $userId = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		// @TODO: Trigger before deleting badge history

		$sql->delete('#__social_badges_history');
		$sql->where('badge_id', $badgeId);

		if (!empty($userId)) {
			$sql->where('user_id', $userId);
		}

		$db->setQuery($sql);

		$db->Query();

		// @TODO: Trigger after deleting badge history

		return true;
	}

	/**
	 * Determines if the user has reached the frequency of the badge threshold.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique badge id.
	 * @param	int		The user's id.
	 * @param	bool	Determines if caller wants to increment by one to determine if the frequency threshold is reached.
	 * @return
	 */
	public function hasReachedFrequency($badgeId, $userId, $incrementByOne = true)
	{
		$db		= ES::db();
		$sql	= $db->sql();

		// Build the column selection
		$sql->select('#__social_badges', 'a');
		$sql->column('COUNT(1)', 'total');
		$sql->column('a.frequency', 'frequency');

		// Build join query.
		//$sql->innerjoin('#__social_badges_maps', 'b');
		$sql->innerjoin('#__social_badges_history', 'b');
		$sql->on('b.badge_id', 'a.id');

		// Build where conditions
		$sql->where('a.id', $badgeId);
		$sql->where('b.user_id', $userId);

		// Group results
		$sql->group('a.id');

		$db->setQuery($sql);

		$data 	= $db->loadObject();

		if (!$data) {
			return false;
		}

		if ($incrementByOne) {
			$data->total 	+= 1;
		}

		return $data->total >= $data->frequency;
	}


	/**
	 * Given a path to the file, install the badge rule file.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string		The path to the .points file.
	 * @return	bool		True if success false otherwise.
	 */
	public function install($path)
	{
		jimport('joomla.filesystem.file');

		// Read the contents
		$contents = file_get_contents($path);

		// If contents is empty, throw an error.
		if (!$contents) {
			$this->setError(JText::_('COM_EASYSOCIAL_BADGES_UNABLE_TO_READ_BADGE_FILE'));
			return false;
		}

		// Restore the data into it's appropriate format
		$data = json_decode($contents);

		// Ensure that it's in an array form.
		if (!is_array($data)) {
			$data = array($data);
		}

		// Let's test if there's data.
		if (!$data) {
			$this->setError(JText::_('COM_EASYSOCIAL_BADGES_UNABLE_TO_READ_BADGE_FILE'));
			return false;
		}

		$result = array();

		foreach ($data as $row) {
			$badge = ES::table('Badge');

			// If this already exists, we need to skip this.
			$state = $badge->load(array('extension' => $row->extension, 'command' => $row->command));

			if ($state) {
				continue;
			}

			// Set to published by default.
			$badge->state = SOCIAL_STATE_PUBLISHED;

			// Bind the badge data.
			$badge->bind($row);

			if (!$badge->created) {
				$badge->created = ES::date()->toSql();
			}

			if (!$badge->achieve_type) {
				$badge->achieve_type = 'frequency';
			}

			if (!$badge->points_increase_rule) {
				$badge->points_increase_rule = 0;
			}

			if (!$badge->points_decrease_rule) {
				$badge->points_decrease_rule = 0;
			}

			if (!$badge->points_threshold) {
				$badge->points_threshold = 0;
			}

			// Store it now.
			$badge->store();

			// Load language file.
			JFactory::getLanguage()->load($row->extension, JPATH_ROOT . '/administrator');

			$actionlog = ES::actionlog();
			$actionlog->log('COM_ES_ACTION_LOG_BADGE_INSTALLED', 'badges', [
				'name' => $badge->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=badges&layout=form&id=' . $badge->id
			]);

			$result[] = $badge->getTitle();
		}

		return $result;
	}
}
