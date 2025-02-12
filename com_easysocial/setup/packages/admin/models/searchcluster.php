<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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

class EasySocialModelSearchCluster extends EasySocialModel
{
	private $data = null;
	private $types = null;
	private $next_limit = null;
	protected $total = null;

	function __construct()
	{
		parent::__construct('searchcluster');
	}

	public function getTypes()
	{
		$db = ES::db();

		if (!$this->types) {
			// get utypes from queries
			$typeQuery = 'select distinct ' . $db->nameQuote('utype') . ' FROM ' . $db->nameQuote('#__social_indexer');
			$db->setQuery($typeQuery);
			$types = $db->loadObjectList();

			$this->types = $types;
		}

		return $this->types;
	}

	public function getAdvSearchItems($options, $next_limit = null, $limit = 0)
	{
		$db = ES::db();
		$sql = $db->sql();

		$my = ES::user();
		$config = ES::config();

		//process item limit
		$defaultLimit = $limit;

		if (!$options) {
			return null;
		}

		$match = isset($options['match']) ? $options['match'] : 'all';
		$clusterType = $options['clusterType'];
		$query = $this->buildAdvSearch($match, $options);

		if (!$query) {
			return array();
		}

		// this is the ori one.
		$cntQuery = str_replace('select distinct u.`id`', 'select count(distinct u.`id`) as `CNT`', $query);

		$sql->raw($cntQuery);
		$db->setQuery($sql);
		$this->total = $db->loadResult();

		if (!$this->total) {
			return array();
		}

		// query sorting
		$query .= ' ORDER BY ' . $db->nameQuote('u.id') . ' DESC';

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

		$items = array();

		if ($results) {
			// We load the cluster based on their type
			foreach ($results as $result) {
				$items[] = ES::cluster($clusterType, $result);
			}

			if ($next_limit >= $this->total) {
				$next_limit = '-1';
			}
		} else {
			$next_limit = '-1';
		}

		//setting next limit for loadmore
		$this->next_limit = $next_limit;

		return $items;
	}

	public function buildAdvSearch($match, $options)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$userId = JFactory::getUser()->id;

		$useProfileId = isset($options['profile']) ? $options['profile'] : '';
		$ignoreInvite = isset($options['ignoreInvite']) ? $options['ignoreInvite'] : false;
		$clusterCategory = isset($options['clusterCategoryIds']) ? $options['clusterCategoryIds'] : '';

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

		$query = 'select distinct u.`id`';
		$query .= ' from ' . $db->nameQuote('#__social_clusters') . ' as u';
		$query .= ' inner join ' . $fieldTable . ' ON xf.uid = u.id';

		if (!ES::user()->isSiteAdmin() && $userId && !$ignoreInvite) {
			$query .= ' LEFT JOIN ' . $db->nameQuote('#__social_clusters_nodes') .' AS nodes ON ' . $db->nameQuote('u.id') . ' = ' . $db->nameQuote('nodes.cluster_id');
		}

		$query .= ' WHERE u.`state` = 1';
		$query .= ' AND u.`cluster_type` = ' . $db->Quote($options['clusterType']);

		// filter by category ids
		if ($clusterCategory) {
			if (!is_array($clusterCategory)) {
				$clusterCategory = ES::makeArray($clusterCategory);
			}

			$query .= ' AND u.`category_id` IN (' . implode(',', $clusterCategory) . ')';
		}

		if (isset($options['clusterAuthorIds']) && $options['clusterAuthorIds']) {
			$query .= ' AND u.`creator_uid` IN (' . implode(',', $options['clusterAuthorIds']) . ')';
			$query .= ' AND u.`creator_type` = ' . $db->Quote('user');
		}

		if (!ES::user()->isSiteAdmin()) {
			if ($ignoreInvite) {
				$query .= $this->genClusterPrivacy('', $options['clusterType']);

			} else {
				//cluster privacy (cluster.type - open, closed or invite)
				$query .= $this->genClusterPrivacy($userId, $options['clusterType']);
			}
		}

		// echo $query;

		return $query;
	}

	private function genClusterPrivacy($userId, $type)
	{
		$db = ES::db();
		$query = '';

		$public = 0;
		$private = 0;
		$invited = 0;
		$semiPublic = 0;

		switch ($type) {
			case SOCIAL_TYPE_GROUP:
				$public = SOCIAL_GROUPS_PUBLIC_TYPE;
				$private = SOCIAL_GROUPS_PRIVATE_TYPE;
				$invited = SOCIAL_GROUPS_INVITE_TYPE;
				$semiPublic = SOCIAL_GROUPS_SEMI_PUBLIC_TYPE;
				break;
			case SOCIAL_TYPE_PAGE:
				$public = SOCIAL_PAGES_PUBLIC_TYPE;
				$private = SOCIAL_PAGES_PRIVATE_TYPE;
				$invited = SOCIAL_PAGES_INVITE_TYPE;
				break;
			case SOCIAL_TYPE_EVENT:
				$public = SOCIAL_EVENT_TYPE_PUBLIC;
				$private = SOCIAL_EVENT_TYPE_PRIVATE;
				$invited = SOCIAL_EVENT_TYPE_INVITE;
				break;
		}

		if ($userId) {
			$query .= ' and (u.`type` IN (' . $private . ', ' . $public . ', ' . $semiPublic . ')';
			$query .= ' OR (u.`type` = ' . $invited . ' AND nodes.`uid` = ' . $db->Quote($userId) . '))';
		} else {
			$query .= ' and u.`type` IN (' . $private . ', ' . $public . ', ' . $semiPublic . ')';
		}

		return $query;

	}

	private function buildAndConditionTable($options)
	{
		$db = ES::db();

		$queries = array();
		$oQueries = array();

		if (!$options['criterias']) {
			return '';
		}

		$filterCount = $options['criterias'] ? count($options['criterias']) : 0;
		$clusterType = $options['clusterType'];

		// current viewing user.
		$viewer = ES::user()->id;

		$lib = ES::advancedsearch($clusterType);

		for ($i = 0; $i < $filterCount; $i++) {
			$criteria = is_string($options['criterias']) ? $options['criterias'] : $options['criterias'][$i];

			if (empty($criteria)) {
				continue;
			}

			$datakey = '';

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

			$field = $lib->prepField($clusterType, $fieldCode, $fieldType, $datakey);
			$arguments = array($field, 'and', &$queries, &$oQueries, $criteria, $operator, $condition, $datakey);
			$state = $lib->trigger('onPrepareAdvancedSearch', $field, $arguments);

			if ($state) {
				continue;
			}

			if ($fieldType == 'address' && $datakey == 'distance') {
				$query = $this->buildAddressDistanceSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($query) {
					$queries[] = $query;
				}

			} else if ($fieldType == 'title') {
				$query = $this->buildTitleSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($query) {
					$queries[] = $query;
				}

			} else if ($fieldType == 'description') {
				$query = $this->buildDescriptionSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($query) {
					$queries[] = $query;
				}

			} else if ($fieldType == 'allday') {
				$query = $this->buildAllDaySQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($query) {
					$queries[] = $query;
				}
			} else if ($fieldType == 'startend') {
				$query = $this->buildStartEndSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($query) {
					$queries[] = $query;
				}
			} else {

				$string = $this->buildConditionString($criteria, $operator, $condition, $datakey);

				$query = 'select distinct a.`uid`';
				$query .= ' from `#__social_fields_data` as a';
				$query .= ' inner join `#__social_fields` as b on a.`field_id` = b.`id`';
				$query .= ' where a.`type` = ' . $db->Quote($options['clusterType']);
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

	private function buildORConditionTable($options)
	{
		$db = ES::db();

		$viewer = ES::user()->id;

		$query = 'select a.`uid`';
		$query .= ' from `#__social_fields_data` as a';
		$query .= ' inner join `#__social_fields` as b on a.`field_id` = b.`id`';
		$query .= ' where a.`type` = ' . $db->Quote($options['clusterType']);
		$query .= ' and (';

		$queries = array();
		$oQueries = array();
		$filterCount = $options['criterias'] ? count($options['criterias']) : 0;
		$clusterType = $options['clusterType'];

		$lib = ES::advancedsearch($clusterType);


		for ($i = 0; $i < $filterCount; $i++) {
			$criteria = is_string($options['criterias']) ? $options['criterias'] : $options['criterias'][$i];

			if (empty($criteria)) {
				continue;
			}

			$datakey = '';
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


			$field = $lib->prepField($clusterType, $fieldCode, $fieldType, $datakey);
			$arguments = array($field, 'or', &$queries, &$oQueries, $criteria, $operator, $condition, $datakey);
			$state = $lib->trigger('onPrepareAdvancedSearch', $field, $arguments);

			if ($state) {
				continue;
			}

			if ($fieldType == 'address' && $datakey == 'distance') {
				$aQuery = $this->buildAddressDistanceSQL($criteria, $operator, $condition, $datakey, $clusterType);

				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else if ($fieldType == 'title') {
				$aQuery = $this->buildTitleSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else if ($fieldType == 'description') {
				$aQuery = $this->buildDescriptionSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else if ($fieldType == 'allday') {
				$aQuery = $this->buildAllDaySQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($aQuery) {
					$queries[] = $aQuery;
				}

			} else if ($fieldType == 'startend') {
				$aQuery = $this->buildStartEndSQL($criteria, $operator, $condition, $datakey, $clusterType);
				if ($query) {
					$queries[] = $aQuery;
				}

			} else {

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

	private function buildTitleSQL($criteria, $operator, $condition, $datakey, $clusterType)
	{
		$db = ES::db();

		$query = 'select a.`id` as `uid`';
		$query .= ' FROM `#__social_clusters` as a';
		$query .= " where a.`cluster_type` = " . $db->Quote($clusterType);
		$query .= ' and a.`state` = 1';
		// $query .= ' and a.`title` LIKE ' . $db->Quote('%' . $condition . '%');


		switch ($operator) {
			case 'blank':
				$query .= ' and a.`title` = ' . $db->Quote('');
				break;

			case 'notblank':
				$query .= ' and a.`title` != ' . $db->Quote('');
				break;

			case 'notequal':
			case 'notcontain':
				$condition = str_replace(' ', '%', $condition);
				$query .= ' and a.`title` NOT LIKE ' . $db->Quote('%' . $condition . '%');
				break;

			case 'startwith':
				$query .= ' and a.`title` LIKE ' . $db->Quote($condition . '%');
				break;

			case 'endwith':
				$query .= ' and a.`title` LIKE ' . $db->Quote('%' . $condition);
				break;

			case 'contain':
			case 'equal':
			default:
				$query .= ' and a.`title` LIKE ' . $db->Quote('%' . $condition . '%');
				break;
		}



		return $query;
	}

	/**
	 * Performs the search on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function buildDescriptionSQL($criteria, $operator, $condition, $datakey, $clusterType)
	{
		$db = ES::db();

		$query = 'select a.`id` as `uid`';
		$query .= ' FROM `#__social_clusters` as a';
		$query .= " where a.`cluster_type` = " . $db->Quote($clusterType);
		$query .= ' and a.`state` = 1';

		switch ($operator) {
			case 'blank':
				$query .= ' and a.`description` = ' . $db->Quote('');
				break;

			case 'notblank':
				$query .= ' and a.`description` != ' . $db->Quote('');
				break;

			case 'notequal':
			case 'notcontain':
				$condition = str_replace(' ', '%', $condition);
				$query .= ' and a.`description` NOT LIKE ' . $db->Quote('%' . $condition . '%');
				break;

			case 'startwith':
				$query .= ' and a.`description` LIKE ' . $db->Quote($condition . '%');
				break;

			case 'endwith':
				$query .= ' and a.`description` LIKE ' . $db->Quote('%' . $condition);
				break;

			case 'contain':
			case 'equal':
			default:
				$query .= ' and a.`description` LIKE ' . $db->Quote('%' . $condition . '%');
				break;
		}

		return $query;
	}


	private function buildAllDaySQL($criteria, $operator, $condition, $datakey, $clusterType)
	{
		// only event type has this all day fields.
		$db = ES::db();

		$query = 'select a.`id` as `uid`';
		$query .= ' FROM `#__social_clusters` as a';
		$query .= ' INNER JOIN `#__social_events_meta` as b ON a.`id` = b.`cluster_id`';
		$query .= " where  a.`state` = 1";

		$column = 'b.all_day';
		$cond = '';
		switch ($operator) {
			case 'notequal':
				$cond .= ' and ' . $db->nameQuote($column) . ' != ' . $db->Quote($condition);
				break;

			case 'equal':
			default:
				$cond .= ' and ' . $db->nameQuote($column) . ' = ' . $db->Quote($condition);
				break;
		}

		$query .= $cond;

		return $query;
	}

	private function buildStartEndSQL($criteria, $operator, $condition, $datakey, $clusterType)
	{
		// only event type has this all day fields.
		$db = ES::db();

		$query = 'select a.`id` as `uid`';
		$query .= ' FROM `#__social_clusters` as a';
		$query .= ' INNER JOIN `#__social_events_meta` as b ON a.`id` = b.`cluster_id`';
		$query .= " where  a.`state` = 1";

		// var_dump($condition);exit;
		$dateSegments = explode('-', $condition);
		$dateString = '';

		if ($dateSegments && isset($dateSegments[0]) && $dateSegments[0]) {
			$dateString = $dateSegments[2] . '-'  . $dateSegments[1] . '-' . $dateSegments[0] . ' 00:00:00';
		}

		$cond = '';
		switch ($operator) {

			case 'blank':
				$cond .= ' and (b.`start` = ' . $db->Quote('') . ' OR b.`start` = ' . $db->Quote('0000-00-00 00:00:00') . ') and (b.`end` = ' . $db->Quote('') . ' OR b.`end` = ' . $db->Quote('0000-00-00 00:00:00') . ')' ;
				break;

			case 'notblank':
				$cond .= ' and (b.`start` != ' . $db->Quote('') . ' AND b.`start` != ' . $db->Quote('0000-00-00 00:00:00') . ') and (b.`end` != ' . $db->Quote('') . ' AND b.`end` != ' . $db->Quote('0000-00-00 00:00:00') . ')' ;
				break;

			case 'notequal':
				$cond .= ' and b.`start` < ' . $db->Quote($dateString) . ' and b.`end` > ' . $db->Quote($dateString);
				break;

			case 'greater':
				$cond .= ' and b.`end` > ' . $db->Quote($dateString);
				break;

			case 'greaterequal':
				$cond .= ' and b.`end` >= ' . $db->Quote($dateString);
				break;

			case 'less':
				$cond .= ' and b.`start` < ' . $db->Quote($dateString);
				break;

			case 'lessequal':
				$cond .= ' and b.`start` <= ' . $db->Quote($dateString);
				break;

			case 'between':
				$dates = explode('|', $condition);

				if (count($dates) == 1) {
					// we need to make sure there are two dates
					$dates[1] = $dates[0];
				}

				if ($dates[0] && !$dates[1]) {
					$dates[1] = $dates[0];
				}

				if (!$dates[0] && $dates[1]) {
					$dates[0] = $dates[1];
				}

				$startSegments = explode('-', $dates[0]);
				$startString = $startSegments[2] . '-'  . $startSegments[1] . '-' . $startSegments[0] . ' 00:00:00';

				$endSegments = explode('-', $dates[1]);
				$endString = $endSegments[2] . '-'  . $endSegments[1] . '-' . $endSegments[0] . ' 23:59:59';

				$cond .= ' and b.`start` between ' . $db->Quote($startString) . ' and ' . $db->Quote($endString);
				break;

			case 'equal':
			default:
				$startString = $dateString;
				$endString = str_replace('00:00:00', '23:59:59', $dateString);

				$cond .= ' and b.`start` >= ' . $db->Quote($startString) . ' and b.`start` <= ' . $db->Quote($endString);
				break;

		}

		$query .= $cond;

		return $query;
	}

	private function buildAddressDistanceSQL($criteria, $operator, $condition, $datakey, $clusterType)
	{
		$db = ES::db();
		$config = ES::config();
		$searchUnit = $config->get('general.location.proximity.unit','mile');

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
		}

		if ($distance && $mylat && $mylon) {

			$dist = (int) $distance; // 5 miles
			$lon1 = $mylon - $dist / abs(cos(deg2rad($mylat)) * $unit[$searchUnit]);
			$lon2 = $mylon + $dist / abs(cos(deg2rad($mylat)) * $unit[$searchUnit]);
			$lat1 = $mylat - ($dist / $unit[$searchUnit]);
			$lat2 = $mylat + ($dist / $unit[$searchUnit]);

			$query = " select distinct geo.`uid` from (";
			$query .= " SELECT uid, field_id, ($radius[$searchUnit] * acos(cos(radians($mylat)) * cos(radians(lat)) * cos(radians(lng) - radians($mylon)) + sin(radians($mylat)) * sin(radians(lat)))) AS distance";
			$query .= " FROM (select a.`uid`, a.field_id, a.`lat`, b.`lng` from";
			$query .= "		(select `uid`, `field_id`, `raw` as `lat` from `#__social_fields_data` where `type` = '". $clusterType ."' and `datakey` = 'latitude'";
			$query .= "			and cast(`raw` as decimal(10, 6)) between '$lat1' and '$lat2') as a";
			$query .= "			inner join (select `uid`, `field_id`, `raw` as `lng` from `#__social_fields_data` where `type` = '" . $clusterType . "' and `datakey` = 'longitude'";
			$query .= " 			and cast(`raw` as decimal(10, 6)) between '$lon1' and '$lon2') as b on a.`uid` = b.`uid`) as x";
			$query .= ") as geo";

			if ($operator == 'greater') {
				$query .= " where geo.`distance` > $dist";
			} else {
				$query .= " where geo.`distance` <= $dist";
			}

		}

		return $query;
	}


	private function buildConditionString($criteria, $operator, $condition, $datakey = '')
	{
		$db = ES::db();

		$fieldCode = '';
		$fieldType = '';

		if (!empty($criteria))  {
			$field = explode('|', $criteria);

			$fieldCode = $field[0];
			$fieldType = $field[1];
		}

		// special handling on checkbox to allow multi value search
		if (($fieldType == 'checkbox') && strpos($condition, '|') !== false) {
			$condition = explode('|', $condition);
			$operator = 'like-checkbox';
		}

		$cond = '(b.`unique_key` = ' . $db->Quote($fieldCode);

		if ($datakey) {
			$cond .= ' and a.`datakey` = ' . $db->Quote($datakey);
		}

		switch($operator)
		{
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
				$cond .= ' and a.`raw` != ' . $db->Quote($condition);
				break;

			case 'contain':
				$condition = str_replace(' ', '%', $condition);
				$cond .= ' and a.`raw` LIKE ' . $db->Quote('%' . $condition . '%');
				break;

			case 'notcontain':
				$condition = str_replace(' ', '%', $condition);
				$cond .= ' and a.`raw` NOT LIKE ' . $db->Quote('%' . $condition . '%');
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
				if (in_array($fieldType, array('datetime', 'startend'))) {
					$tmpCond = ' and (a.`raw` = ' . $db->Quote('') . ' OR a.`raw` = ' . $db->Quote('0000-00-00 00:00:00') . ')';
				}

				$cond .= $tmpCond;
				break;

			case 'notblank':
				$tmpCond = ' and a.`raw` != ' . $db->Quote('') . ' and a.`raw` IS NOT NULL';

				// date fields process will be different
				if (in_array($fieldType, array('datetime', 'startend'))) {
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
				$cond .= ' and a.`raw` >= ' . $db->Quote($dates[0]) . ' and a.`raw` <= ' . $db->Quote($dates[1]);
				break;

			case 'equal':
			default:
				$cond .= ' and a.`raw` = ' . $db->Quote($condition);
				break;

		}

		$cond .= ')';

		return $cond;
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
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}
}
