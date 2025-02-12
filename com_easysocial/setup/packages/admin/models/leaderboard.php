<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include parent model
ES::import('admin:/includes/model');

class EasySocialModelLeaderboard extends EasySocialModel
{
	private $data			= null;

	function __construct()
	{
		parent::__construct('leaderboard');
	}

	/**
	 * Retrieves the user's point and position
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getUserRank($userId, $options = array())
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$defaultData = new stdClass();
		$defaultData->pos = 0;
		$defaultData->points = 0;
		$defaultData->user_id = $userId;

		$query = array();
		$query[] = 'SELECT ' . $db->nameQuote('user_id') . ', sum(' . $db->nameQuote('points') . ') as points';
		$query[] = 'FROM ' . $db->nameQuote('#__social_points_history');
		$query[] = 'WHERE ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
		$query[] = ' group by ' . $db->nameQuote('user_id');

		$query = implode(' ', $query);
		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result) {
			return $defaultData;
		}


		$excludeUserIds = [];

		if (ES::isBlockEnabled()) {
			$excludeUserIds = $this->getBlockedUserIds(JFactory::getUser()->id);
		}

		// Determine if the caller wants to excludeAdmin
		if (!$config->get('leaderboard.listings.admin')) {
		
			// Get a list of site administrators from the site.
			$model = ES::model('Users');
			$admins = $model->getSiteAdmins(true);

			if ($admins) {
				$excludeUserIds = array_merge($excludeUserIds, $admins);
				// remove duplicates
				$excludeUserIds = array_unique($excludeUserIds);
			}
		}


		$query = 'SET @row_number = 0';
		$db->setQuery($query);
		$db->execute();

		$query = array();
		$query[] = 'select y.`num` as `pos`, y.`totalpoints` as `points`, y.`user_id` from (';
		$query[] = 'select (@row_number:=@row_number + 1) AS num, x.totalpoints, x.user_id from (';
		$query[] = 'select ' . $db->nameQuote('user_id') . ', sum(' . $db->nameQuote('points') . ') as totalpoints';
		$query[] = 'from ' . $db->nameQuote('#__users') . ' as u';
		$query[] = 'left join ' . $db->nameQuote('#__social_points_history') . ' as h on h.user_id = u.id';
		$query[] = 'WHERE 1';

		if ($excludeUserIds) {
			$query[] = 'AND u.' . $db->nameQuote('id') . ' NOT IN (' . implode(',' , $excludeUserIds) . ')';
		}

		$query[] = 'group by u.' . $db->nameQuote('id') . ' having (sum(' . $db->nameQuote('points') . ') >= ' . $db->Quote($result->points) . ')';
		$query[] = 'ORDER BY ' . $db->nameQuote('totalpoints') . ' DESC, u.id asc';
		$query[] = ') as x';
		$query[] = ') as y';
		$query[] = 'ORDER BY y.`num` DESC limit 1';


		$query = implode(' ', $query);

		$db->setQuery($query);
		$position = $db->loadObject();

		if (!$position) {
			return $defaultData;
		}

		return $position;
	}

	/**
	 * Retrieves the ladder board
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array 	$ids	An array of ids.
	 * @return
	 */
	public function getLadder($options = array() , $loadUsers = true)
	{
		$config = ES::config();
		$db = ES::db();

		$validateESAD = isset($options['validateESAD']) ? $options['validateESAD'] : true;
		$isPaginate = isset($options['isPaginate']) ? $options['isPaginate'] : true;
		$recentdays = (int) isset($options['recentdays']) ? $options['recentdays'] : 0;
		$excludeAdmin = isset($options['excludeAdmin']) ? $options['excludeAdmin'] : '';

		$excludeUserIds = [];

		if (ES::isBlockEnabled()) {
			$excludeUserIds = $this->getBlockedUserIds(JFactory::getUser()->id);
		}

		// Determine if the caller wants to excludeAdmin
		if ($excludeAdmin) {
			// Get a list of site administrators from the site.
			$model = ES::model('Users');
			$admins = $model->getSiteAdmins(true);

			if ($admins) {
				$excludeUserIds = array_merge($excludeUserIds, $admins);
				// remove duplicates
				$excludeUserIds = array_unique($excludeUserIds);
			}
		}

		$q = [];

		$q[] = "select d.`user_id` as `id`, d.`points`";
		$q[] = "FROM (";
		$q[] = "    SELECT ph.`user_id`, SUM(ph.`points`) as `points`";
		$q[] = "        FROM `#__social_points_history` as ph";

		if ($recentdays) {
			$now = ES::date()->toSql();
			$q[] = "WHERE (ph.`created` <= " . $db->Quote($now) . " AND ph.`created` >= date_sub(" . $db->Quote($now) . ", INTERVAL " . $recentdays . " DAY))";
		}
		$q[] = "    GROUP BY ph.`user_id`";
		$q[] = ") as d";

		$q[] = "INNER JOIN `#__users` AS a on a.`id` = d.`user_id`";

		if ($validateESAD) {

			$profileTypeIds = $this->getCommunityProfileTypeIds(false, false);
			$q[] = "INNER JOIN `#__social_profiles_maps` AS upm on a.`id` = upm.`user_id` and upm.`profile_id` IN (" . implode(',', $profileTypeIds) . ")";
		}

		if (isset($options['state']) && $options['state'] != -1) {
			// join only when we need
			$q[] = "LEFT JOIN `#__social_users` AS b ON a.`id` = b.`user_id`";
		}

		// If group is supplied, we only want to fetch users from a particular group
		if (isset($options['group']) && !empty($options['group']) && $options['group'] != -1) {
			$groupId = $options['group'];

			$q[] = "INNER JOIN `#__user_usergroup_map` AS c ON a.`id` = c.`user_id` AND c.`group_id` = " . $db->Quote($groupId);
		}


		$q[] = "WHERE a.`block` = 0";

		// exclude users;
		if ($excludeUserIds) {
			$q[] = "AND a.id NOT IN (" . implode(',', $excludeUserIds) . ")";
		}

		// If user id filters is provided, filter the users based on the id.
		if (isset($options['ids']) && !empty($options['ids'])) {
			$ids = $options['ids'];
			$ids = ES::makeArray($ids);

			$total = count($ids);
			$idQuery = '';

			for ($i = 0; $i < $total; $i++) {
				$idQuery .= $db->Quote($ids[$i]);

				if (next($ids) !== false) {
					$idQuery .= ',';
				}
			}

			$q[] = "AND (a.`id` IN (" . $idQuery . "))";
		}

		// If state is passed in, we need to determine the user's state.
		if (isset($options['state']) && $options['state'] != -1) {
			$state = $options['state'];
			$q[] = "AND b.`state` = " . $db->Quote($state);
		}

		// If login state is provided we need to filter the query.
		if (isset($options['login']) && $options['login'] != -1) {
			$loginState = $options['login'];

			if ($loginState) {
				$q[] = 'AND EXISTS(';
				$q[] = ' SELECT ' . $db->nameQuote('userid') .  ' FROM ' . $db->nameQuote('#__session') . ' AS f';
				$q[] = ' WHERE ' . $db->nameQuote('userid') . ' = a.' . $db->nameQuote('id');
				$q[] = ')';
			} else {
				$q[] = 'AND NOT EXISTS(';
				$q[] = ' SELECT ' . $db->nameQuote('userid') .  ' FROM ' . $db->nameQuote('#__session') . ' AS f';
				$q[] = ' WHERE ' . $db->nameQuote('userid') . ' = a.' . $db->nameQuote('id');
				$q[] = ')';
			}
		}

		// If there's an exclusion list, we need to respect that too.
		// what is this exclusion for ?? 
		if (isset($options['exclusion'])) {
			$exclusions	= $options['exclusion'];

			foreach ($exclusions as $column => $values) {
				if (!$values) {
					continue;
				}

				$tmpQ = ' AND ' . $db->nameQuote($column);
				if (is_array($values)) {
					$tmpQ .= ' NOT IN (';
					$total = count($values);

					for ($i = 0; $i < $total; $i++) {
						$tmpQ .= $db->Quote($values[$i]);

						if (next($values) !== false) {
							$tmpQ .= ',';
						}
					}
					$tmpQ .= ')';

				} else {
					$tmpQ .= ' != ' . $db->Quote($values);
				}

				$q[] = $tmpQ;
			}
		}


		if ($isPaginate) {
			// @task: Process the count here.
			$count = implode(' ' , $q);
			$this->setTotal($count, true);
		}

		$q[] = "ORDER BY d.`points` DESC";

		// Merge the query array back.
		$query = implode(' ' , $q);

		// echo $query;
		// echo '<br><br>';
		// exit;

		$limit = isset($options['limit']) ? $options['limit'] : '';
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : 0;

		// make sure the value is in integer
		$limitstart = (int) $limitstart;

		if ($limit) {
			$query .= ' LIMIT ' . $limitstart . ',' . $limit;
		}

		// echo $query;exit;

		//now with the limit
		$result = $this->getData($query);

		if (!$result) {
			return $result;
		}

		$ids = array();
		foreach ($result as $item) {
			$ids[] = $item->id;
		}

		// preload users
		ES::user($ids);

		$users 	= array();
		foreach ($result as $item) {

			$user = ES::user($item->id);

			if ($recentdays) {
				// this mean it based on recent x number of days. we need to reset the points.
				$user->points = $item->points;
			}

			$users[] = $user;
		}

		return $users;
	}

	/**
	 * Retrieves a list of user data based on the given ids.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array 	$ids	An array of ids.
	 * @return
	 */
	public function getUsers($options = array() , $loadUsers = true)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query		= array();
		$query[]	= 'SELECT DISTINCT(a.`id`) FROM ' . $db->nameQuote('#__users') . ' AS a';

		// exclude esad users
		$query[] = 'INNER JOIN `#__social_profiles_maps` as upm on a.`id` = upm.`user_id`';
		$query[] = 'INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';


		$query[]	= 'LEFT JOIN ' . $db->nameQuote('#__social_users') . ' AS b';
		$query[]	= 'ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('user_id');

		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' a.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query .= ') OR (';
			$query .= ' a.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query .= ')';

		}

		$ordering 	= isset($options['ordering']) ? $options['ordering'] : null;

		// If group is supplied, we only want to fetch users from a particular group
		if(isset($options['group']) && !empty($options['group']) && $options['group'] != -1)
		{
			$groupId 	= $options['group'];

			$query[]	= 'INNER JOIN ' . $db->nameQuote('#__user_usergroup_map') . ' AS c';
			$query[]	= 'ON a.' . $db->nameQuote('id') . ' = c.' . $db->nameQuote('user_id');
			$query[]	= 'AND c.' . $db->nameQuote('group_id') . ' = ' . $db->Quote($groupId);
		}

		if(!is_null($ordering))
		{
			$query[]	= 'LEFT JOIN ' . $db->nameQuote('#__social_points_history') . ' AS d';
			$query[]	= 'ON d.' . $db->nameQuote('user_id') . ' = a.' . $db->nameQuote('id');
		}

		// filter out user which is blocked.
		$query[]	= 'WHERE a.' . $db->nameQuote('block') . ' = ' . $db->Quote('0');

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query[] = ' WHERE bus.' . $db->nameQuote('id') . ' IS NULL';
		}


		// If user id filters is provided, filter the users based on the id.
		if(isset($options['ids']) && !empty($options['ids']))
		{
			$ids 	= $options['ids'];
			$ids	= ES::makeArray($ids);

			$total		= count($ids);
			$idQuery	= '';

			for($i = 0; $i < $total; $i++)
			{
				$idQuery	.= $db->Quote($ids[$i]);

				if(next($ids) !== false)
				{
					$idQuery	.= ',';
				}
			}

			$query[]	= 'AND (a.' . $db->nameQuote('id') . ' IN(' . $idQuery . '))';
		}


		// If state is passed in, we need to determine the user's state.
		if(isset($options['state']) && $options['state'] != -1)
		{
			$state 		= $options['state'];
			$query[]	= 'AND b.' . $db->nameQuote('state') . '=' . $db->Quote($state);
		}

		// If login state is provided we need to filter the query.
		if(isset($options['login']) && $options['login'] != -1)
		{
			$loginState	= $options['login'];

			if($loginState)
			{
				$query[]	= 'AND EXISTS(';
				$query[]	= ' SELECT ' . $db->nameQuote('userid') .  ' FROM ' . $db->nameQuote('#__session') . ' AS f';
				$query[]	= ' WHERE ' . $db->nameQuote('userid') . ' = a.' . $db->nameQuote('id');
				$query[]	= ')';
			}
			else
			{
				$query[]	= 'AND NOT EXISTS(';
				$query[]	= ' SELECT ' . $db->nameQuote('userid') .  ' FROM ' . $db->nameQuote('#__session') . ' AS f';
				$query[]	= ' WHERE ' . $db->nameQuote('userid') . ' = a.' . $db->nameQuote('id');
				$query[]	= ')';
			}
		}

		// If there's an exclusion list, we need to respect that too.
		if(isset($options['exclusion']))
		{
			$exclusions	= $options['exclusion'];

			foreach($exclusions as $column => $values)
			{
				if(!$values)
				{
					continue;
				}

				$query[]	= ' AND ' . $db->nameQuote($column);

				if(is_array($values))
				{
					$query[]	= ' NOT IN(';
					$total  = count($values);

					for($i = 0; $i < $total; $i++)
					{
						$query[] = $db->Quote($values[$i]);

						if(next($values) !== false)
						{
							$query[]  = ',';
						}
					}
					$query[]	= ')';
				}
				else
				{
					$query[]	= '!=' . $db->Quote($values);
				}
			}
		}


		$query[]	= 'ORDER BY d.' . $db->nameQuote('points') . ' DESC';

		// Merge the query array back.
		$query		= implode(' ' , $query);
		// echo $query;exit;
		// @task: Process the count here.
		$count	= str_ireplace('SELECT a.* FROM' , 'SELECT COUNT(1) FROM' , $query);
		$this->setTotal($count);

		$limit 	= isset($options['limit']) ? $options['limit'] : '';

		if($limit)
		{
			$this->setLimit($limit);
		}

		//now with the limit
		$sql->raw($query);
		$result = $this->getDataColumn($sql);

		// Pre-load the users.
		ES::user($result);

		return $result;
	}
}
