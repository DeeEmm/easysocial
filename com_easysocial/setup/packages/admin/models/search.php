<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
ES::import('admin:/includes/privacy/option');

class EasySocialModelSearch extends EasySocialModel
{
	private $data			= null;
	private $types     		= null;
	private $next_limit    	= null;
	protected $total 			= null;

	public function __construct()
	{
		parent::__construct('search');
	}

	public function getTypes()
	{
		$db = ES::db();

		if (! $this->types) {
			// get utypes from queries
			$typeQuery = 'select distinct ' . $db->nameQuote('utype') . ' FROM ' . $db->nameQuote('#__social_indexer');
			$db->setQuery($typeQuery);
			$types = $db->loadObjectList();

			$this->types = $types;
		}

		return $this->types;
	}

	public function verifyFieldsData($keywords, $userId)
	{
		// return variable
		$content = '';

		// get customfields.
		$fieldsLib = ES::fields();
		$fieldModel = ES::model('Fields');
		$fieldsResult = array();

		$options = array();
		$options['data'] = true;
		$options['dataId'] = $userId;
		$options['dataType'] = SOCIAL_TYPE_USER;
		$options['searchable'] = 1;

		//todo: get customfields.
		$fields = $fieldModel->getCustomFields($options);

		if (count($fields) > 0) {
			//foreach($fields as $item)
			foreach ($fields as $field) {
				$userFieldData = isset($field->data) ? $field->data : '';

				$args = array($userId, $keywords, $userFieldData);
				$f = array(&$field);

				$dataResult = $fieldsLib->trigger('onIndexerSearch', SOCIAL_FIELDS_GROUP_USER, $f, $args);

				if ($dataResult !== false && count($dataResult) > 0) {
					$fieldsResult[] = $dataResult[0];
				}
			}

			$contentSnapshot = array();

			$totalReturnFields = count($fieldsResult);
			$invalidCnt        = 0;

			if ($fieldsResult) {
				// we need to go through each one to see if any of the result returned is a false or not.
				// false mean, the user canot view the fields.
				// this also mean, the user canot view the searched item.

				foreach ($fieldsResult as $fr) {
					if ($fr == -1) {
						$invalidCnt++;
					} else if (!empty($fr)) {
						$contentSnapshot[] = $fr;
					}
				}

				if ($invalidCnt == $totalReturnFields) {
					return -1;
				}
			}

			if ($contentSnapshot) {
				$content = implode('<br />', $contentSnapshot);
			}

		}

		return $content;
	}

	/**
	 * Retrieves a list of custom search filters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFilters($element = SOCIAL_TYPE_USER, $userId = null, $includeSiteWide = true)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.* from `#__social_search_filter` as a';

		if ($element != 'all') {
			$query .= ' where a.`element` = ' . $db->Quote($element);
		}

		if (!is_null($userId)) {
			$query .= ' AND a.`created_by`=' . $db->Quote($userId);
		}

		if (!$includeSiteWide) {
			$query .= ' AND a.`sitewide`=' . $db->Quote(0);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		if (!$results) {
			return array();
		}

		$filters = array();

		foreach ($results as $row) {
			$filter = ES::table('SearchFilter');
			$filter->bind($row);

			$filters[] = $filter;
		}

		return $filters;
	}

	/**
	 * Retrieves a list of site wide filters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSiteWideFilters($element = SOCIAL_TYPE_USER)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.* from `#__social_search_filter` as a';
		$query .= ' where a.`element` = ' . $db->Quote($element);
		$query .= ' and a.`sitewide` = 1';
		$query .= ' order by a.`title` ASC';

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		$filters = array();
		if ($results) {
			foreach ($results as $row) {
				$tbl = ES::table('SearchFilter');
				$tbl->bind($row);

				$filters[] = $tbl;
			}
		}

		return $filters;
	}


	public function getFieldOptionList($uniqueKey, $element)
	{
		static $_cache = array();

		$idx = $uniqueKey . '-' . $element;

		if (!isset($_cache[$idx])) {

			$db = ES::db();
			$sql = $db->sql();

			$query = "select distinct c.`title`, c.`value`";
			$query .= " from `#__social_fields` as a";
			$query .= " inner join `#__social_fields_options` as c";
			$query .= " on a.`id` = c.`parent_id`";
			$query .= " where a.`unique_key` = '$uniqueKey'";
			$query .= " and c.`key` = 'items'";
			$query .= " and c.`value` is not null";
			$query .= " order by c.`parent_id`, c.`ordering`";

			$sql->raw($query);
			$db->setQuery($sql);

			$_cache[$idx] = $db->loadObjectList();
		}


		return $_cache[$idx];
	}

	/**
	 * Allows caller to perform advanced search
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAdvSearchItems($options, $next_limit = null, $limit = 0)
	{
		$db = ES::db();
		$sql = $db->sql();

		$my = ES::user();
		$config = ES::config();
		$privacy = ES::privacy($my->id);


		//process item limit
		$defaultLimit = $limit;

		if (!$options) {
			return null;
		}

		$match = isset($options['match']) ? $options['match'] : 'all';
		$sort = isset($options['sort']) ? $options['sort'] : 'default';
		$query = $this->buildAdvSearch($match, $options);

		// echo str_ireplace('#__', 'jos_', $query);
		// exit;

		if (!$query) {
			return array();
		}

		// this is for testing
		// $query = $this->buildAdvSearchTEST($match, $options);
		// $cntQuery = str_replace('select distinct a.`id`', 'select count(1) as `CNT`', $query);


		// this is the ori one.
		$cntQuery = str_replace('select u.`id`', 'select count(1) as `CNT`', $query);

		$sql->raw($cntQuery);

		// echo str_ireplace('#__', 'jos_', $sql);
		// exit;

		$db->setQuery($sql);
		$this->total = $db->loadResult();

		if (! $this->total) {
			// no need further processing
			return array();
		}

		// query sorting
		if ($sort && $sort != 'default') {
			// Set always order alphabetical to the name.
			if ($sort == 'alphabetical') {
				$query .= ' ORDER BY ' . $db->nameQuote('u.name') . ' ASC';
			} else {
				$query .= ' ORDER BY ' . $db->nameQuote('u.' . $sort) . ' DESC';
			}
		}

		// this mainQuery shouldnt contain the limit for later use in data filling.
		$mainQuery = $query;

		if (is_null($next_limit)) {
			$query .= ' LIMIT ' . $limit;
			$next_limit = $limit;
		} else {
			$query .= ' LIMIT ' . $next_limit . ',' . $limit;
			$next_limit = $next_limit + $limit;
		}

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadColumn();

		$users = array();
		if ($results) {
			$filtered = array();

			for ($i = 0; $i < count($results); $i++) {
				$userId = $results[ $i ];

				$privacykeys = 'profiles.search';
				$addItem = false;

				if ($config->get('users.indexer.privacy')) {
					$addItem = $privacy->validate($privacykeys, $userId);
				} else {
					$addItem = true;
				}

				if ($addItem) {
					$filtered[] = $userId;
				}
			}

			if (count($filtered) < $defaultLimit && $next_limit > 0) {
				$this->fillAdvSearchData($defaultLimit, $next_limit, $filtered, $mainQuery);
			}


			$users = array();

			foreach ($filtered as $userId) {
				$users[] = ES::user($userId);
			}

			// lets cache the user's customfield data.
			$userModel 	= ES::model('Users');
			$userModel->setUserFieldsData($filtered);

			if ($next_limit >= $this->total) {
				$next_limit = '-1';
			}
		} else {
			$next_limit = '-1';
		}

		//setting next limit for loadmore
		$this->next_limit = $next_limit;

		return $users;
	}


	public function fillAdvSearchData($defaultLimit, $next_limit, &$filtered, $query)
	{
		$db = ES::db();

		$my = ES::user();
		$privacy = ES::privacy($my->id);
		$config = ES::config();
		$cnt = 0;

		$tryLimit = 2;

		do {
			$startLimit = $next_limit;

			if ($next_limit == '-1') {
				return;
			}

			$queryLimit = ' LIMIT ' . $next_limit . ',' . $defaultLimit;
			$nextQuery = $query . $queryLimit;

			$db->setQuery($nextQuery);

			$results = $db->loadColumn();

			if (count($results) > 0) {
				for ($i = 0; $i < count($results); $i++) {
					$userId 		= $results[$i];
					$privacykeys 	= 'profiles.search';

					$addItem = false;
					if ($config->get('users.indexer.privacy')) {
						$addItem = $privacy->validate($privacykeys, $userId);
					} else {
						$addItem = true;
					}

					if ($addItem) {
						$filtered[] = $userId;
						$next_limit = $next_limit + 1;

						if (count($filtered) == $defaultLimit) {
							break;
						}
					}
				}

				$cnt = count($filtered);
			} else {
				$next_limit = '-1';
			}

			$tryLimit--;

		} while (($cnt < $defaultLimit && $next_limit != '-1') && $tryLimit > 0);


	}


	// for debug purposes
	private function buildAdvSearchTEST($match, $options)
	{
		$query = 'select distinct a.`id`';
		$query .= ' from `#__users` as a';

		return $query;
	}

	public function buildAdvSearch($match, $options)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();
		$my = ES::user();

		$avatarOnly = $options['avatarOnly'];
		$onlineOnly = isset($options['onlineOnly']) ? $options['onlineOnly'] : false;
		$useProfileId = isset($options['profile']) ? $options['profile'] : '';

		// Determines if we should exclude this profile type user on the user listing page.
		$excludeUserListing = isset($options['excludeUserListing']) ? $options['excludeUserListing'] : false;

		if (is_string($options['criterias'])) {
			$options['criterias'] = array($options['criterias']);
			$options['conditions'] = array($options['conditions']);
			$options['datakeys'] = array($options['datakeys']);
			$options['operators'] = array($options['operators']);
		}

		$tmps = array();

		// data clean up.
		for ($i = 0; $i < count($options['criterias']); $i++) {

			$criteria = $options['criterias'][$i];
			$condition = isset($options['conditions'][$i]) ? $options['conditions'][$i] : '';
			$datakey = isset($options['datakeys'][$i]) ? $options['datakeys'][$i] : '';
			$operator = $options['operators'][$i];

			$condition = trim($condition);

			if (($condition !== '' && $condition != '|')
				|| (($condition === '' || $condition == '|') && in_array($operator, array('blank', 'notblank')))) {
				$tmps['criterias'][] = $criteria;
				$tmps['datakeys'][] = $datakey;
				$tmps['operators'][] = $operator;
				$tmps['conditions'][] = $condition;
			}
		}

		$options['criterias'] = isset($tmps['criterias']) ? $tmps['criterias'] : '';
		$options['conditions'] = isset($tmps['conditions']) ? $tmps['conditions'] : '';
		$options['datakeys'] = isset($tmps['datakeys']) ? $tmps['datakeys'] : '';
		$options['operators'] = isset($tmps['operators']) ? $tmps['operators'] : '';

		if ($match == 'all') {
			$fieldTable = $this->buildAndConditionTable($options);
		} else {
			$fieldTable = $this->buildORConditionTable($options);
		}

		if (!$fieldTable) {
			return '';
		}

		$viewerId = JFactory::getUser()->id;

		// TODO:
		$nonFriendOnly = isset($options['nonFriendOnly']) ? $options['nonFriendOnly'] : false;
		$ignoreUserIds = isset($options['ignoreUserIds']) ? $options['ignoreUserIds'] : array();

		$query = 'select u.`id`';
		$query .= ' from `#__users` as u';
		$query .= ' inner join ' . $fieldTable . ' ON xf.uid = u.id';

		// exclude esad users
		$query .= ' INNER JOIN `#__social_profiles_maps` as upm on u.`id` = upm.`user_id`';
		$query .= ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		// Skip this if the current logged in user is site admin
		if ($excludeUserListing && !$my->isSiteAdmin()) {
			$query .= ' and up.' . $db->nameQuote('exclude_userlisting') . '=' . $db->Quote(0);
		}

		// only non-friends
		if ($nonFriendOnly && !JFactory::getUser()->guest) {
			$query .= ' LEFT JOIN `#__social_friends` as f ON u.`id` = if(f.`target_id` = ' . $db->Quote($viewerId) . ', f.`actor_id`, f.`target_id`)';
			$query .= '    AND ((f.`target_id`=' . $db->Quote($viewerId) . ' AND f.`state` = 1) OR (f.`actor_id`=' . $db->Quote($viewerId) . ' AND f.`state` = 1))';
		}

		if ($avatarOnly) {
			$query .= ' inner join `#__social_albums` as c on u.id = c.uid and c.`type` = ' . $db->Quote(SOCIAL_TYPE_USER) . ' and c.`core` = ' . $db->Quote('1') ;
		}

		if (ES::isBlockEnabled()) {
			// user block
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query .= ' ON (';
			$query .= ' u.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($viewerId);
			$query .= ') OR (';
			$query .= ' u.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewerId) ;
			$query .= ')';
		}

		$query .= ' WHERE u.`block` = 0';

		if ($nonFriendOnly && !JFactory::getUser()->guest) {
			$query .= ' and f.`id` IS NULL';
		}

		if (ES::isBlockEnabled()) {
			// user block continue here
			$query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		if ($useProfileId) {

			if (!is_array($useProfileId)) {
				$useProfileId = ES::makeArray($useProfileId);
			}

			$query .= ' and upm.`profile_id` IN (' . implode(',', $useProfileId) . ')';
		}

		if ($onlineOnly) {
			$query .= ' and EXISTS(';
			$query .= 'SELECT ' . $db->nameQuote('userid') . ' FROM ' . $db->nameQuote('#__session') . ' AS us WHERE ' . $db->nameQuote('userid') . ' = u.' . $db->nameQuote('id');
			$query .= ' AND `client_id` = ' . $db->Quote(0);
			$query .= ')';
		}


		if ($ignoreUserIds) {
			if (!is_array($ignoreUserIds)) {
				$ignoreUserIds = ES::makeArray($ignoreUserIds);
			}

			$query .= ' and u.`id` NOT IN (' . implode(',', $ignoreUserIds) . ')';
		}

		return $query;
	}

	private function buildAndConditionTable($options)
	{
		$db = ES::db();
		$config = ES::config();

		$queries = array();
		$oQueries = array();

		if (!$options['criterias']) {
			return '';
		}

		$filterCount = $options['criterias'] ? count($options['criterias']) : 0;

		$streamLib = ES::stream();

		// current viewing user.
		$viewer = ES::user()->id;

		$lib = ES::advancedsearch(SOCIAL_FIELDS_GROUP_USER);

		for ($i = 0; $i < $filterCount; $i++) {
			$criteria 	= is_string($options['criterias']) ? $options['criterias'] : $options['criterias'][$i];

			if (empty($criteria)) {
				continue;
			}

			$datakey 	= '';
			if (is_string($options['datakeys'])) {
				$datakey = $options['datakeys'];
			} else if (isset($options['datakeys'][$i])) {
				$datakey = $options['datakeys'][$i];
			}

			$operator = is_string($options['operators']) ? $options['operators'] : $options['operators'][$i];
			$condition = is_string($options['conditions']) ? $options['conditions'] : $options['conditions'][$i];

			$field = explode('|', $criteria);

			$fieldCode = $field[0];
			$fieldType = $field[1];

			$field = $lib->prepField(SOCIAL_FIELDS_GROUP_USER, $fieldCode, $fieldType, $datakey);
			$arguments = array($field, 'and', &$queries, &$oQueries, $criteria, $operator, $condition, $datakey);
			$state = $lib->trigger('onPrepareAdvancedSearch', $field, $arguments);

			if ($state) {
				continue;
			}

			if ($fieldType == 'address' && $datakey == 'distance') {
				$query = $this->buildAddressDistanceSQL($criteria, $operator, $condition, $datakey);
				if ($query) {
					$queries[] = $query;
				}

			} else if ($fieldType == 'joomla_username' || $fieldType == 'joomla_email') {

				$query = $this->buildJoomlaUserSQL($criteria, $operator, $condition, $datakey);

				if ($query) {
					$queries[] = $query;
				}

			} else if ($fieldType == 'joomla_lastlogin' || $fieldType == 'joomla_joindate') {

				$query = $this->buildJoomlaDatesSQL($criteria, $operator, $condition, $datakey);

				if ($query) {
					$queries[] = $query;
				}

			} else {

				// need to check here if this is a birthday age or not.
				if ($fieldType == 'birthday' && $datakey == 'age') {
					$ages = explode('|', $condition);
					$ageInDates = $this->convertAgeToDate($ages, $operator);

					$condition = implode('|', $ageInDates);

					// if operator == equal, we need to make it to between.
					if ($operator == 'equal') {
						$operator = 'between';
					}

				}

				if ($fieldType == 'birthday' && $datakey == 'date') {
					$dates = explode('|', $condition);

					if (count($dates) == 2) {
						$dstart = $dates[0];
						$dend = $dates[1];

						if ($dstart && $dend) {

							$dstart = explode(' ', $dstart);
							$dend = explode(' ', $dend);

							$dstart = $this->convertToMySQLDate($dstart[0]) . ' 00:00:00';
							$dend = $this->convertToMySQLDate($dend[0]) . ' 23:59:59';

							$condition = $dstart . '|' . $dend;

						} else if ($dstart && !$dend) {
							// this is coming from dating search module to support single 'TO' #1163
							$operator = 'greaterequal';
							$condition = $dstart;
						} else if (!$dstart && $dend) {
							// this is coming from dating search module to support single 'FROM' #1163
							$operator = 'lessequal';
							$condition = $dend;
						}
					}

					if (count($dates) == 1) {
						$dstart = $dates[0];

						$dstart = explode(' ', $dstart);
						$dstart = $this->convertToMySQLDate($dstart[0]) . ' 00:00:00';

						$condition = $dstart;
					}

				}

				// // Since relationship raw data contain user id, we need to make the operator as contain.
				// if ($fieldType == 'relationship') {
				// 	$relationOperator = 'contain';

				// 	if ($operator == 'notequal') {
				// 		$relationOperator = 'notcontain';
				// 	}

				// 	$operator = $relationOperator;
				// }

				//now we need to reset the datakey as birthday field always search based on 'date' datakey
				if ($fieldType == 'birthday') {
					$datakey = 'date';
				}

				$string = $this->buildConditionString($criteria, $operator, $condition, $datakey);

				$query = 'select distinct a.`uid`';
				$query .= ' from `#__social_fields_data` as a';
				$query .= ' inner join `#__social_fields` as b on a.`field_id` = b.`id`';

				if ($config->get('privacy.enabled')) {
					$query .= ' left join `#__social_privacy_items` as pi on a.`field_id` = pi.`uid`';
					$query .= '		and a.`uid` = pi.`user_id` and pi.`type` = ' . $db->Quote(SOCIAL_TYPE_FIELD);
				}

				$query .= ' where a.`type` = ' . $db->Quote(SOCIAL_TYPE_USER);

				if ($config->get('privacy.enabled')) {

					// privacy here.
					$query .= ' AND (';

					//public
					$query .= ' (pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR (pi.`value` IS NULL) OR ';

					//member
					$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

					if ($config->get('friends.enabled')) {
						//friends of friends
						$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateMutualFriendSQL($viewer, 'pi.`user_id`') . ') > 0)) OR ';

						//friends of friends
						$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('pi.`user_id`', $viewer) . ') > 0)) OR ';

						//friends
						$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('pi.`user_id`', $viewer) . ') > 0)) OR ';
					} else {
						// fall back to member
						$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
						$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';

					}

					//only me
					$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (pi.`user_id` = ' . $viewer . ')) OR ';

					//viewer items
					$query .= ' (pi.`user_id` = ' . $viewer . ')';

					// privacy ended here
					$query .= ')';

				}

				$query .= ' AND ';
				$query .= $string;

				$queries[] = $query;
			}

		}

		if (!$queries) {
			return '';
		}

		$union = (count($queries) > 1) ? implode(') UNION ALL (', $queries) : $queries[0];
		$union = '(' . $union . ')';

		$groupCnt = $filterCount - 1;

		// here is the key to filter users (by using group by) which 'meet' all the conditions.
		$query = '(select * from (' . $union . ') as x group by x.`uid` having (count(x.`uid`)  > ' . $groupCnt . ')) as xf';

		return $query;
	}

	private function convertToMySQLDate($string)
	{
		$segments = explode('-', $string);

		if (strlen($segments[0]) >= 4) {
			// we knwo the date already in yyyy-mm-dd, nothing to process.
			return $string;
		} else {
			// this means the date format in dd-mm-yyyy. lets format it into yyyy-mm-dd
			$date = ES::date($string);
			return $date->toFormat('Y-m-d');
		}
	}

	private function convertAgeToDate($ages, $operator)
	{
		if (! isset($ages[1])) {
			// this happen when start has value and end has no value
			$ages[1] = $ages[0];
		}

		if ($ages[1] && !$ages[0]) {
			//this happen when start is empty and end has value
			$ages[0] = $ages[1];
		}

		$startdate = '';
		$enddate = '';

		$currentTimeStamp = ES::date()->toUnix();

		if ($ages[0] == $ages[1]) {
			$start = strtotime('-' . ($ages[0] - 1) . ' years', $currentTimeStamp);

			$year = ES::date($start)->toFormat('Y');
			$startdate = $year . '-01-01 00:00:01';
			$enddate = $year . '-12-31 23:59:59';

		} else {

			if ($ages[0]) {
				$start = strtotime('-' . ($ages[0] - 1) . ' years', $currentTimeStamp);

				$year = ES::date($start)->toFormat('Y');
				$enddate = $year . '-12-31 23:59:59';
			}

			if ($ages[1]) {
				$end = strtotime('-' . ($ages[1] - 1) . ' years', $currentTimeStamp);

				$year = ES::date($end)->toFormat('Y');
				$startdate = $year . '-01-01 00:00:01';
			}
		}

		// var_dump($startdate, $enddate, $operator);

		$dates = array();

		// year 24
		// startdate : 1990-01-01 00:00:01
		// enddate : 1990-08-21 23:59:59

		switch($operator) {
			case 'equal':
			case 'between':
				$dates[0] = $startdate;
				$dates[1] = $enddate;
				break;
			case 'greater':
				$dates[0] = $startdate; // greater mean younger, in this case, younger than 24, so we use startdate. raw > 1990-01-01
				break;
			case 'greaterequal':
				$dates[0] = $enddate; // same as greater but it include the 24 year it itself. so we use the enddate raw >= 1990-08-21
				break;
			case 'less':
				$dates[0] = $enddate; // less mean older, in this case, older than 24, so we use enddate. raw < 1990-08-21
				break;
			case 'lessequal':
				$dates[0] = $startdate; // same is less but it include the 24 year as well. so we use the start date. raw <= 1990-01-01
				break;

		}


		return $dates;
	}

	private function buildORConditionTable($options)
	{
		$db = ES::db();
		$config = ES::config();

		$streamLib = ES::stream();

		$viewer = ES::user()->id;

		$query = 'select a.`uid`';
		$query .= ' from `#__social_fields_data` as a';
		$query .= ' inner join `#__social_fields` as b on a.`field_id` = b.`id`';

		if ($config->get('privacy.enabled')) {
			$query .= ' left join `#__social_privacy_items` as pi on a.`field_id` = pi.`uid`';
			$query .= '		and a.`uid` = pi.`user_id` and pi.`type` = ' . $db->Quote(SOCIAL_TYPE_FIELD);
		}

		$query .= ' where a.`type` = ' . $db->Quote(SOCIAL_TYPE_USER);

		if ($config->get('privacy.enabled')) {

			// privacy here.
			$query .= ' AND (';

			//public
			$query .= ' (pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR (pi.`value` IS NULL) OR ';

			//member
			$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends of friends
				$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateMutualFriendSQL($viewer, 'pi.`user_id`') . ') > 0)) OR ';

				//friends of friends
				$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('pi.`user_id`', $viewer) . ') > 0)) OR ';

				//friends
				$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('pi.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
				$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query .= ' ((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (pi.`user_id` = ' . $viewer . ')) OR ';

			//viewer items
			$query .= ' (pi.`user_id` = ' . $viewer . ')';

			// privacy ended here
			$query .= ')';
		}

		$query .= ' and (';

		$queries = array();
		$oQueries = array();

		$filterCount = $options['criterias'] ? count($options['criterias']) : 0;

		$lib = ES::advancedsearch(SOCIAL_FIELDS_GROUP_USER);

		for ($i = 0; $i < $filterCount; $i++) {
			$criteria 	= is_string($options['criterias']) ? $options['criterias'] : $options['criterias'][$i];

			if (empty($criteria)) {
				continue;
			}

			$datakey 	= '';
			if (is_string($options['datakeys'])) {
				$datakey = $options['datakeys'];
			} else if (isset($options['datakeys'][$i])) {
				$datakey = $options['datakeys'][$i];
			}
			$operator 	= is_string($options['operators']) ? $options['operators'] : $options['operators'][$i];
			$condition 	= is_string($options['conditions']) ? $options['conditions'] : $options['conditions'][$i];

			$field  	= explode('|', $criteria);

			$fieldCode 	= $field[0];
			$fieldType 	= $field[1];

			$field = $lib->prepField(SOCIAL_FIELDS_GROUP_USER, $fieldCode, $fieldType, $datakey);
			$arguments = array($field, 'or', &$queries, &$oQueries, $criteria, $operator, $condition, $datakey);
			$state = $lib->trigger('onPrepareAdvancedSearch', $field, $arguments);

			if ($state) {
				continue;
			}


			if ($fieldType == 'address' && $datakey == 'distance') {
				$aQuery = $this->buildAddressDistanceSQL($criteria, $operator, $condition, $datakey);

				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else if ($fieldType == 'joomla_username' || $fieldType == 'joomla_email') {

				$aQuery = $this->buildJoomlaUserSQL($criteria, $operator, $condition, $datakey);
				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else if ($fieldType == 'joomla_lastlogin' || $fieldType == 'joomla_joindate') {

				$aQuery = $this->buildJoomlaDatesSQL($criteria, $operator, $condition, $datakey);
				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else {

				// need to check here if this is a birthday age or not.
				if ($fieldType == 'birthday' && $datakey == 'age') {
					$ages = explode('|', $condition);
					$ageInDates = $this->convertAgeToDate($ages, $operator);
					$condition = implode('|', $ageInDates);

					// if operator == equal, we need to make it to between.
					if ($operator == 'equal') {
						$operator = 'between';
					}
				}

				if ($fieldType == 'birthday' && $datakey == 'date') {
					$dates = explode('|', $condition);

					if (count($dates) == 2) {
						$dstart = $dates[0];
						$dend = $dates[1];

						if ($dstart && $dend) {

							$dstart = explode(' ', $dstart);
							$dend = explode(' ', $dend);

							$dstart = $this->convertToMySQLDate($dstart[0]) . ' 00:00:00';
							$dend = $this->convertToMySQLDate($dend[0]) . ' 23:59:59';

							$condition = $dstart . '|' . $dend;

						} else if ($dstart && !$dend) {
							// this is coming from dating search module to support single 'TO' #1163
							$operator = 'greaterequal';
							$condition = $dstart;
						} else if (!$dstart && $dend) {
							// this is coming from dating search module to support single 'FROM' #1163
							$operator = 'lessequal';
							$condition = $dend;
						}
					}

					if (count($dates) == 1) {
						$dstart = $dates[0];

						$dstart = explode(' ', $dstart);
						$dstart = $dstart[0] . ' 00:00:00';

						$condition = $dstart;
					}
				}

				//now we need to reset the datakey as birthday field always search based on 'date' datakey
				if ($fieldType == 'birthday') {
					$datakey = 'date';
				}

				$string = $this->buildConditionString($criteria, $operator, $condition, $datakey);

				// echo $string;
				$oQueries[] = $string;
			}
		}

		$or = '';
		if ($oQueries) {
			$or = (count($oQueries) > 1) ? implode(' OR ', $oQueries) : $oQueries[0];
		}

		$query .= $or;
		$query .= ')';

		if ($queries || $oQueries) {
			$union = '';

			if ($queries) {
				$union = (count($queries) > 1) ? implode(' UNION ', $queries) : $queries[0];
			}

			if (count($oQueries) == 0 && $union) {
				// this mean the search only has one condition and this condition is based on the address distance
				$query = $union;
			} else if($oQueries && $union) {
				$query .= ' UNION ' . $union;
			}
		} else {
			return '';
		}

		$result = '(select distinct * from (' . $query . ') as x) as xf';
		return $result;
	}

	/**
	 * General query for #__users table
	 *
	 * @since	2.0
	 * @access	private
	 */
	private function buildJoomlaUserSQL($criteria, $operator, $condition, $datakey)
	{
		$db = ES::db();

		if (!empty($criteria)) {
			$field = explode('|', $criteria);

			$fieldCode = $field[0];
			$fieldType = $field[1];
		}

		$query = 'select a.`id` as `uid`';
		$query .= ' FROM `#__users` as a';

		$queryWhere = ' where a.`username` = ' . $db->Quote($condition);

		if ($fieldType == 'joomla_email') {
			$queryWhere = ' where a.`email` = ' . $db->Quote($condition);
		}

		$query .= $queryWhere;
		$query .= ' and a.`block` = 0';

		return $query;
	}

	private function buildJoomlaDatesSQL($criteria, $operator, $condition, $datakey)
	{
		$db = ES::db();

		$field = explode('|', $criteria);

		$fieldCode 	= $field[0];
		$fieldType 	= $field[1];

		$column = ($fieldType == 'joomla_lastlogin') ? 'a.lastvisitDate': 'a.registerDate';


		$query = 'select a.`id` as `uid`';
		$query .= ' FROM `#__users` as a';
		$query .= ' where a.`block` = 0';


		$tzOffset = ES::date()->getOffSetObject();

		if ($operator != 'between') {
			// we need to use jdate or else the offset will be messed up.
			$inputDate = new JDate($condition, $tzOffset);
			$condition = $inputDate->toSql();
		}

		$cond = '';
		switch ($operator) {

			case 'blank':
				$cond .= ' and (' . $db->nameQuote($column) . ' = ' . $db->Quote('') . ' OR ' . $db->nameQuote($column) . ' = ' . $db->Quote('0000-00-00 00:00:00') . ')';
				break;

			case 'notblank':
				$cond .= ' and (' . $db->nameQuote($column) . ' != ' . $db->Quote('') . ' AND ' . $db->nameQuote($column) . ' != ' . $db->Quote('0000-00-00 00:00:00') . ')';
				break;

			case 'notequal':
				$cond .= ' and ' . $db->nameQuote($column) . ' != ' . $db->Quote($condition);
				break;

			case 'greater':
				$cond .= ' and ' . $db->nameQuote($column) . ' > ' . $db->Quote($condition);
				break;

			case 'greaterequal':
				$cond .= ' and ' . $db->nameQuote($column) . ' >= ' . $db->Quote($condition);
				break;

			case 'less':
				$cond .= ' and ' . $db->nameQuote($column) . ' < ' . $db->Quote($condition);
				break;

			case 'lessequal':
				$cond .= ' and ' . $db->nameQuote($column) . ' <= ' . $db->Quote($condition);
				break;

			case 'between':
				$dates = explode('|', $condition);

				$inputDate1 = new JDate($dates[0], $tzOffset);
				$dates[0] = $inputDate1->toSql();

				$inputDate2 = new JDate($dates[1], $tzOffset);
				$dates[1] = $inputDate2->toSql();

				$cond .= ' and (' . $db->nameQuote($column) . ' >= ' . $db->Quote($dates[0]) . ' and ' . $db->nameQuote($column) . ' <= ' . $db->Quote($dates[1]) . ')';
				break;

			case 'equal':
			default:
				$cond .= ' and ' . $db->nameQuote($column) . ' = ' . $db->Quote($condition);
				break;
		}

		$query .= $cond;

		return $query;
	}

	private function buildAddressDistanceSQL($criteria, $operator, $condition, $datakey)
	{
		$db = ES::db();
		$config = ES::config();
		$searchUnit = $config->get('general.location.proximity.unit','mile');

		$streamLib = ES::stream();

		$unit['mile'] = 69;
		$unit['km'] = 111;
		$radius['mile'] = 3959;
		$radius['km'] = 6371;

		$query = '';
		$fieldCode 	= '';
		$fieldType 	= '';
		$viewer = ES::user()->id;

		$conditions = explode('|', $condition);
		$distance = isset($conditions[0]) && $conditions[0] ? $conditions[0] : '';
		$distance = (int) $distance;

		// check if distance withing the maximum allowed value or not.
		$maxRadius = $radius[$searchUnit];
		if ($distance > $maxRadius) {
			$distance = $maxRadius;
		}

		$mylat = isset($conditions[1]) && $conditions[1] ? $conditions[1] : '';
		$mylon = isset($conditions[2]) && $conditions[2] ? $conditions[2] : '';

		if (!$mylat && !$mylon) {
			// lets get the lat and lon from current logged in user address
			$my = ES::user();
			$address = $my->getFieldValue('ADDRESS');
			$mylat = $address->value->latitude;
			$mylon = $address->value->longitude;

			// var_dump($address->value->latitude, $address->value->longitude);
		}

		// $mylat = '3.2287897';
		// $mylon = '101.6402272';

		// var_dump($mylat, $mylon);


		if ($distance && $mylat && $mylon) {
			// $mylat = $address->data['latitude'];
			// $mylon = $address->data['longitude'];

			$dist = (int) $distance; // 5 miles
			$lon1 = $mylon - $dist / abs(cos(deg2rad($mylat)) * $unit[$searchUnit]);
			$lon2 = $mylon + $dist / abs(cos(deg2rad($mylat)) * $unit[$searchUnit]);
			$lat1 = $mylat - ($dist / $unit[$searchUnit]);
			$lat2 = $mylat + ($dist / $unit[$searchUnit]);

			$query = " select distinct geo.`uid` from (";
			$query .= " SELECT uid, field_id, ($radius[$searchUnit] * acos(cos(radians($mylat)) * cos(radians(lat)) * cos(radians(lng) - radians($mylon)) + sin(radians($mylat)) * sin(radians(lat)))) AS distance";
			$query .= " FROM (select a.`uid`, a.field_id, a.`lat`, b.`lng` from";
			$query .= "		(select `uid`, `field_id`, `raw` as `lat` from `#__social_fields_data` where `type` = '".SOCIAL_TYPE_USER."' and `datakey` = 'latitude'";
			$query .= "			and cast(`raw` as decimal(10, 6)) between '$lat1' and '$lat2') as a";
			$query .= "			inner join (select `uid`, `field_id`, `raw` as `lng` from `#__social_fields_data` where `type` = '" . SOCIAL_TYPE_USER . "' and `datakey` = 'longitude'";
			$query .= " 			and cast(`raw` as decimal(10, 6)) between '$lon1' and '$lon2') as b on a.`uid` = b.`uid`) as x";
			$query .= ") as geo";

			if ($config->get('privacy.enabled')) {
				$query .= " left join `#__social_privacy_items` as pi on geo.`field_id` = pi.`uid`";
				$query .= "		and geo.`uid` = pi.`user_id` and pi.`type` = '" . SOCIAL_TYPE_FIELD . "'";
			}

			if ($operator == 'greater') {
				$query .= " where geo.`distance` > $dist";
			} else {
				$query .= " where geo.`distance` <= $dist";
			}

			if ($config->get('privacy.enabled')) {
				// privacy here.
				$query .= " AND (";

				//public
				$query .= " (pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ") OR (pi.`value` IS NULL) OR ";

				//member
				$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ") AND ($viewer > 0)) OR ";

				if ($config->get('friends.enabled')) {
					//friends of friends
					$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ") AND ((" . $streamLib->generateMutualFriendSQL($viewer, 'pi.`user_id`') . ") > 0)) OR ";

					//friends of friends
					$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ") AND ((" . $streamLib->generateIsFriendSQL('pi.`user_id`', $viewer) . ") > 0)) OR ";

					//friends
					$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ((" . $streamLib->generateIsFriendSQL('pi.`user_id`', $viewer) . ") > 0)) OR ";
				} else {
					// fall back to member
					$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ") AND ($viewer > 0)) OR ";
					$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ") AND ($viewer > 0)) OR ";
				}

				//only me
				$query .= " ((pi.`value` = " . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ") AND (pi.`user_id` = '$viewer')) OR ";

				//viewer items
				$query .= " (pi.`user_id` = '$viewer')";

				// privacy ended here
				$query .= ")";
			}

		}

		return $query;
	}


	private function buildConditionString($criteria, $operator, $condition, $datakey = '')
	{
		$db = ES::db();

		$multiOptionType = array('checkbox', 'multilist', 'multidropdown');

		$fieldCode = '';
		$fieldType = '';

		if (!empty($criteria)) {
			$field = explode('|', $criteria);

			$fieldCode = $field[0];
			$fieldType = $field[1];
		}

		// special handling on gender to allow multi value search
		if (($fieldType == 'gender') && strpos($condition, '|') !== false) {
			$condition = explode('|', $condition);
			$operator = 'in';
		}

		// special handling on checkbox to allow multi value search
		if (($fieldType == 'checkbox') && strpos($condition, '|') !== false) {
			$condition = explode('|', $condition);
			$operator = 'like-checkbox';
		}

		// special treatment for country fields due to issue where in different listing style, the system stored differnt value.
		// #851
		if (($fieldType == 'country') && strpos($condition, '|') !== false) {
			$condition = explode('|', $condition);
		}

		// Since relationship raw data contain user id, we need to make the operator as contain.
		if ($fieldType == 'relationship') {
			$relationOperator = 'contain';

			if ($operator == 'notequal') {
				$relationOperator = 'notcontain';
			}

			$operator = $relationOperator;
		}


		$cond = '(b.`unique_key` = ' . $db->Quote($fieldCode);

		if ($datakey) {
			$cond .= ' and a.`datakey` = ' . $db->Quote($datakey);
		}

		switch ($operator) {
			case 'in':

				if (is_array($condition)) {
					$tmp = '';
					foreach($condition as $c) {
						$tmp .= ($tmp) ? ',' . $db->Quote($c) : $db->Quote($c);
					}

					$condition = $tmp;
				} else {
					$condition = $db->Quote($condition);
				}

				$cond .= ' and a.`raw` IN (' . $condition . ')';
				break;

			case 'like-checkbox':

				if (is_array($condition)) {

					$tmp = '';
					foreach($condition as $c) {
						$tmp .= ($tmp) ? ' OR a.`raw` LIKE ' . $db->Quote('%'.$c.'%') : ' a.`raw` LIKE ' . $db->Quote('%'.$c.'%');
					}
					$cond .= ' and (' . $tmp . ')';
				} else {
					// $condition = $db->Quote($condition);
					$cond .= ' and a.`raw` LIKE (' . $condition . ')';

				}

				break;

			case 'notequal':


				if (is_array($condition)) {
					$tmp = '';
					foreach ($condition as $c) {
						$tmp .= ($tmp) ? ',' . $db->Quote($c) : $db->Quote($c);
					}
					$cond .= ' and a.`raw` NOT IN (' . $tmp . ')';
				} else {
					$cond .= ' and a.`raw` != ' . $db->Quote($condition);
				}
				break;

			case 'contain':

				$wrapperChar = '';
				$column = $db->nameQuote('a.raw');

				if (in_array($fieldType, $multiOptionType)) {
					$column = "concat(" . $db->nameQuote('a.raw') . ", ' ')";
					$wrapperChar = ' ';
				}

				$condition = str_replace(' ', '%', $condition);
				$condition = $condition . $wrapperChar;

				$cond .= ' and ' . $column . ' LIKE ' . $db->Quote('%' . $condition . '%');
				break;

			case 'notcontain':

				$wrapperChar = '';
				$column = $db->nameQuote('a.raw');

				if (in_array($fieldType, $multiOptionType)) {
					$column = "concat(" . $db->nameQuote('a.raw') . ", ' ')";
					$wrapperChar = ' ';
				}

				$condition = str_replace(' ', '%', $condition);
				$condition = $condition . $wrapperChar;

				$cond .= ' and ' . $column . ' NOT LIKE ' . $db->Quote('%' . $condition . '%');
				break;

			case 'startwith':
				$cond .= ' and a.`raw` LIKE ' . $db->Quote($condition . '%');
				break;

			case 'endwith':
				$cond .= ' and a.`raw` LIKE ' . $db->Quote('%' . $condition);
				break;

			case 'blank':

				$tmpCond = ' and (a.`raw` = ' . $db->Quote('') . ' OR a.`raw` IS NULL)';

				// date fields process will be different
				if (in_array($fieldType, array('datetime', 'joomla_lastlogin', 'joomla_joindate', 'startend'))) {
					$tmpCond = ' and (a.`raw` = ' . $db->Quote('') . ' OR a.`raw` = ' . $db->Quote('0000-00-00 00:00:00') . ')';
				}

				$cond .= $tmpCond;
				break;

			case 'notblank':
				$tmpCond = ' and a.`raw` != ' . $db->Quote('') . ' and a.`raw` IS NOT NULL';

				// date fields process will be different
				if (in_array($fieldType, array('datetime', 'joomla_lastlogin', 'joomla_joindate', 'startend'))) {
					$tmpCond = ' and (a.`raw` != ' . $db->Quote('') . ' AND a.`raw` != ' . $db->Quote('0000-00-00 00:00:00') . ')';
				}

				$cond .= $tmpCond;

				break;

			case 'greater':
				$cond .= ' and a.`raw` > ' . $db->Quote($condition);
				break;

			case 'greaterequal':
				$cond .= ' and a.`raw` >= ' . $db->Quote($condition);
				break;

			case 'less':
				$cond .= ' and a.`raw` < ' . $db->Quote($condition);
				break;

			case 'lessequal':
				$cond .= ' and a.`raw` <= ' . $db->Quote($condition);
				break;

			case 'between':
				$dates = explode('|', $condition);
				$tmpQuery = ' and a.`raw` >= ' . $db->Quote($dates[0]) . ' and a.`raw` <= ' . $db->Quote($dates[1]);

				if ($fieldType == 'numeric') {
					$from = (int) $dates[0];
					$to = (int) $dates[1];

					$tmpQuery = ' and a.`raw` >= ' . $from . ' and a.`raw` <= ' . $to;
				}

				$cond .= $tmpQuery;

				break;

			case 'equal':
			default:

				if (is_array($condition)) {
					$tmp = '';
					foreach ($condition as $c) {
						$tmp .= ($tmp) ? ',' . $db->Quote($c) : $db->Quote($c);
					}
					$cond .= ' and a.`raw` IN (' . $tmp . ')';
				} else {
					$cond .= ' and a.`raw` = ' . $db->Quote($condition);
				}
				break;

		}

		$cond .= ')';

		// echo $cond;

		return $cond;
	}


	public function getItems($keywords, $type = '', $next_limit = null, $limit = 0)
	{
		$db = ES::db();
		$sql = $db->sql();
		$my = ES::user();
		$config = ES::config();

		$coreType = array(SOCIAL_INDEXER_TYPE_USERS, SOCIAL_INDEXER_TYPE_PHOTOS, SOCIAL_INDEXER_TYPE_LISTS,  SOCIAL_INDEXER_TYPE_GROUPS, SOCIAL_INDEXER_TYPE_EVENTS);

		if (empty($keywords)) {
			return;
		}

		$where		= array();
		$wheres		= array();
		$words		= explode(' ', $keywords);

		if (count($words) > 1) {
			$tmp = array();
			$cnt = count($words) - 1;
			for ($i = 0; $i < $cnt; $i++) {
				$tmp[] = $words[ $i ] . ' ' . $words[ $i + 1 ];
			}

			$words	= $tmp;
		}

		foreach ($words as $word) {
			$word		= $db->Quote('%'.$db->escape($word, true).'%', false);

			// $where[]	= 'a.`title` LIKE ' . $word;
			$where[]	= 'a.`content` LIKE ' . $word;
			$wheres[] 	= implode(' OR ', $where);
		}

		$where	= ' (' . implode(') OR (', $wheres) . ')';

		$mainQuery = array();

		//process item limit
		$defaultLimit = $limit;

		$queryLimit = '';
		if ($next_limit) {
			$queryLimit = ' LIMIT ' . $next_limit . ', ' . $defaultLimit;
			$next_limit = $next_limit + $defaultLimit;
		} else {
			$queryLimit = ' LIMIT ' . $defaultLimit;
			$next_limit = $defaultLimit;
		}


		// users
		$query = 'select a.* FROM `#__social_indexer` as a';
		$query .= ' inner join `#__users` as u ON a.`uid` = u.`id` and u.`block` = ' . $db->Quote('0');
		$query .= ' where `utype` = ' . $db->Quote(SOCIAL_INDEXER_TYPE_USERS);
		$query .= ' and (' . $where . ')';
		if ($type == '' || $type == SOCIAL_INDEXER_TYPE_USERS) {
			$mainQuery[] = $query;
		}


		if ($my->id) {
			// own photos
			$query = 'select a.* FROM `#__social_indexer` as a';
			$query .= ' where `utype` = ' . $db->Quote(SOCIAL_INDEXER_TYPE_PHOTOS);
			$query .= ' and `ucreator` = ' . $db->Quote($my->id);
			$query .= ' and (' . $where . ')';

			if ($type == '' || $type == SOCIAL_INDEXER_TYPE_PHOTOS) {
				$mainQuery[] = $query;
			}

			// own friend list
			$query = 'select a.* FROM `#__social_indexer` as a';
			$query .= ' where `utype` = ' . $db->Quote(SOCIAL_INDEXER_TYPE_LISTS);
			$query .= ' and `ucreator` = ' . $db->Quote($my->id);
			$query .= ' and (' . $where . ')';
			if ($type == '' || $type == SOCIAL_INDEXER_TYPE_LISTS) {
				$mainQuery[] = $query;
			}
		}

		// groups
		$query = 'select a.* FROM `#__social_indexer` as a';
		$query .= ' where `utype` = ' . $db->Quote(SOCIAL_INDEXER_TYPE_GROUPS);
		$query .= ' and (' . $where . ')';
		if ($type == '' || $type == SOCIAL_INDEXER_TYPE_GROUPS) {
			$mainQuery[] = $query;
		}

		// events
		$query = 'select a.* FROM `#__social_indexer` as a';
		$query .= ' where `utype` = ' . $db->Quote(SOCIAL_INDEXER_TYPE_EVENTS);
		$query .= ' and (' . $where . ')';
		if ($type == '' || $type == SOCIAL_INDEXER_TYPE_EVENTS) {
			$mainQuery[] = $query;
		}

		// others
		$query = 'select a.* FROM `#__social_indexer` as a';
		if ($type && !in_array($type, $coreType)) {
			$query .= ' where `utype` = ' . $db->Quote($type);
		} else {
			$query .= ' where `utype` NOT IN (' . $db->Quote(SOCIAL_INDEXER_TYPE_USERS) . ',' . $db->Quote(SOCIAL_INDEXER_TYPE_PHOTOS) . ',' . $db->Quote(SOCIAL_INDEXER_TYPE_LISTS) . ',' . $db->Quote(SOCIAL_INDEXER_TYPE_GROUPS)  . ')';
		}
		$query .= ' and (' . $where . ')';
		if ($type == '' || ($type != SOCIAL_INDEXER_TYPE_USERS && $type != SOCIAL_INDEXER_TYPE_PHOTOS && $type != SOCIAL_INDEXER_TYPE_LISTS && $type != SOCIAL_INDEXER_TYPE_GROUPS)) {
			$mainQuery[] = $query;
		}

		if (! $mainQuery) {
			// this mean the user is a guest and trying to click on the photos / friend list filtering.
			$this->next_limit = '-1';
			return array();
		}


		$mainQuery = '(' . implode(') UNION (', $mainQuery) . ')';
		$mainQuery = 'select * FROM (' . $mainQuery . ') as x';

		// query for total count.
		$cntQuery = 'select COUNT(1) FROM (' . $mainQuery . ') as x';


		// continue
		$mainQuery .= ' order by x.`utype` desc, x.`last_update` desc';

		// limit
		$query = $mainQuery . $queryLimit;

		// getting items count.
		$sql->clear();
		$sql->raw($cntQuery);

		$db->setQuery($sql);

		$this->total = $db->loadResult();

		$sql->clear();
		$sql->raw($query);

		// echo $sql;exit;

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$filtered 	= array();
		$privacy 	= ES::privacy($my->id);

		if (count($result) > 0) {
			if (count($result) < $defaultLimit) {
				//this mean the resultset is the last batch
				$next_limit = '-1';
			}

			//foreach($result as $item)
			for ($i = 0; $i < count($result); $i++) {
				$item =& $result[ $i ];

				$privacy_key = ($item->utype == SOCIAL_INDEXER_TYPE_USERS) ? 'profiles' : $item->utype;
				$privacy_rule = ($item->utype == SOCIAL_INDEXER_TYPE_USERS) ? 'search' : 'view';

				$keys = $privacy_key . '.' . $privacy_rule;

				$addItem = true;

				if ($keys == 'profiles.search') {
					if ($config->get('users.indexer.privacy')) {
						$addItem = $privacy->validate($keys, $item->ucreator);
					} else {
						$addItem = true;
					}
				} else if($item->utype != SOCIAL_INDEXER_TYPE_GROUPS) {
					$addItem = $privacy->validate($keys, $item->uid, $item->utype, $item->ucreator);
				}

				// if this item is a user type, the content might be from fields. let check the fields privacy.
				if ($addItem && $item->utype == SOCIAL_INDEXER_TYPE_USERS && $config->get('users.indexer.privacy')) {
					$smallText  = $this->verifyFieldsData($keywords, $item->uid);

					if ($smallText === false) {
						// when this is false, meean the user canot view the result which is returned by the fields.
						$addItem = false;
					} else {
						$item->description = $smallText;
					}
				}

				if ($addItem) {
					$filtered[] = $item;
				}
			}

			if (count($filtered) < $defaultLimit && $next_limit > 0) {
				$this->fillData($defaultLimit, $next_limit, $filtered, $mainQuery);
			}

		} else {
			$next_limit = '-1';
		}


		//setting next limit for loadmore
		$this->next_limit = $next_limit;

		// we need to adjust the total item count here due to privacy checking
		if ($this->total > count($filtered) && !$filtered) {
			$this->total = 0;
		}

		// var_dump($groups);
		// exit;

		// echo $query;

		$groups 	= array();
		if (count($filtered) > 0) {
			foreach ($filtered as $item) {
				$groups[$item->utype][] = $item;
			}
		}

		return $groups;
	}

	public function fillData($defaultLimit, $next_limit, &$filtered, $query)
	{
		$db = ES::db();

		$my = ES::user();
		$privacy = ES::privacy($my->id);
		$config = ES::config();
		$cnt = 0;

		$tryLimit = 2;

		do {
			$startLimit = $next_limit;

			if ($next_limit == '-1') {
				return;
			}

			$queryLimit = ' LIMIT ' . $next_limit . ',' . $defaultLimit;
			$nextQuery = $query . $queryLimit;

			$db->setQuery($nextQuery);

			$result = $db->loadObjectList();

			if (count($result) > 0) {
				foreach ($result as $item) {
					$privacy_key 	= ($item->utype == SOCIAL_INDEXER_TYPE_USERS) ? 'profiles' : $item->utype;
					$privacy_rule 	= ($item->utype == SOCIAL_INDEXER_TYPE_USERS) ? 'search' : 'view';

					$keys = $privacy_key . '.' . $privacy_rule;

					$addItem = false;

					if ($keys == 'profiles.search') {
						if ($config->get('users.indexer.privacy')) {
							$addItem = $privacy->validate($keys, $item->ucreator);
						} else {
							$addItem = true;
						}
					} else {
						$addItem = $privacy->validate($keys, $item->uid, $item->utype, $item->ucreator);
					}

					if ($addItem) {
						$filtered[] = $item;
						$next_limit = $next_limit + 1;

						if (count($filtered) == $defaultLimit) {
							break;
						}

					}
				}

				$cnt = count($filtered);
			} else {
				$next_limit = '-1';
			}

			$tryLimit--;

		} while (($cnt < $defaultLimit && $next_limit != '-1') && $tryLimit > 0);


	}

	public function getCount()
	{
		return empty ($this->total) ? '0' : $this->total ;
	}




	public function getNextLimit()
	{
		return $this->next_limit;
	}

	public function getPagination($reload = null)
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	/**
	 * Check existings url records from smart search based on alias
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSmartSearchRecords($alias)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Check for existings records
		$query = array();
		$query[] = 'SELECT ' . $db->qn('link_id') . ' FROM ' . $db->qn('#__finder_links');
		$query[] = 'WHERE ' . $db->qn('url') . ' LIKE ' . $db->Quote('%' . $alias . '%');

		$query = implode(' ', $query);
		$db->setQuery($query);

		$items = $db->loadColumn();

		return $items;
	}
}
