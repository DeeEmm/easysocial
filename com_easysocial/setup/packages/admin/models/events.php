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

class EasySocialModelEvents extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('events', $config);
	}

	public function initStates()
	{
		// Direction, search, limit, limitstart is handled by parent::initStates();
		parent::initStates();

		// Override ordering default value
		$ordering = $this->getUserStateFromRequest('ordering', 'a.id');
		$type = $this->getUserStateFromRequest('type', 'all');
		$state = $this->getUserStateFromRequest('state', 'all');
		$category = $this->getUserStateFromRequest('category', -1);

		$this->setState('ordering', $ordering);
		$this->setState('type', $type);
		$this->setState('state', $state);
		$this->setState('category', $category);
	}

	/**
	 * Return parent total value
	 *
	 * @since  1.3
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Returns array of SocialEvent object for backend listing.
	 *
	 * @since  1.3
	 */
	public function getItems()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters', 'a');
		$sql->column('a.id');

		$search = $this->getState('search');

		if (!empty($search)) {
			$sql->where('a.title', '%' . $search . '%', 'LIKE');
		}

		$state = $this->getState('state');
		if ($state !== 'all') {
			if ($state == SOCIAL_CLUSTER_PENDING) {
				$sql->where('a.state',  array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');
			} else {
				$sql->where('a.state', $state);
			}
		} else {
			$sql->where('a.state',  array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'NOT IN');
		}

		$type = $this->getState('type');
		if ($type !== 'all') {
			$sql->where('a.type', $type);
		}

		$category = $this->getState('category');

		if ($category && $category != -1) {
			$sql->where('a.category_id', $category);
		}

		$sql->order($this->getState('ordering'), $this->getState('direction'));

		$sql->leftjoin('#__social_clusters_categories', 'b');
		$sql->on('a.category_id', 'b.id');

		$sql->where('a.cluster_type', SOCIAL_TYPE_EVENT);

		// echo $sql;

		$this->setTotal($sql->getTotalSql());

		$result = $this->getDataColumn($sql);

		if (empty($result)) {
			return array();
		}

		// Result is an array of ids, we directly use this instead of looping through the result to bind to SocialEvent object since ES::event() is array-ids-ready
		$events = ES::event($result);

		return $events;
	}

	/**
	 * Returns array of SocialEvent object with non repititive events.
	 *
	 * @since  3.1
	 * @access public
	 */
	public function getNonRepetitiveEvents($options = array(), $debug = false)
	{
		$db = ES::db();
		$now = ES::date()->toSql(true);


		$q = [];

		$my = ES::user();

		// parents events only
		$q[] = "SELECT distinct `a`.`id`, a.`created`, b.`start` FROM `#__social_clusters` AS `a`";
		$q[] = "INNER JOIN `#__social_events_meta` AS `b` ON `a`.`id` = `b`.`cluster_id`";

		if (ES::isBlockEnabled()) {
			// $q[] = "LEFT JOIN `#__social_block_users` AS `bus`";
			// $q[] = "ON (`a`.`creator_uid` = `bus`.`user_id`";
			// $q[] = "AND `bus`.`target_id` = " . $db->Quote($my->id);

			// $q[] = "OR `a`.`creator_uid` = `bus`.`target_id`";
			// $q[] = "AND `bus`.`user_id` = " . $db->Quote($my->id) . ')';

			$q[] = $this->getUserBlockingJoinQuery($my, 'bus', 'creator_uid', 0, array('clusterColumnAlias' => 'id'));
		}

		if (isset($options['type']) && $options['type'] === 'user') {
			$q[] = "LEFT JOIN `#__social_clusters_nodes` AS `nodes`";
			$q[] = "ON `a`.`id` = `nodes`.`cluster_id`";
		}

		$q[] = "WHERE `a`.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_EVENT);
		$q[] = "AND `a`.`parent_id` = " . $db->Quote('0');

		// Filter by state
		if (isset($options['state'])) {
			$q[] = "AND `a`.`state` = " . $db->q($options['state']);
		}

		// Filter by event type
		if (isset($options['type']) && $options['type'] !== 'all') {

			// We do this is because other than getting Open and Closed, we also need to get Invite but only if it is participated by the user
			if ($options['type'] === 'user') {
				$userid = isset($options['userid']) ? $options['userid'] : ES::user()->id;

				$q[] = "AND (`a`.`type` IN (" . implode(',', $db->Quote(array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_SEMI_PUBLIC))) . ")";
				$q[] = "OR (`a`.`type` = " . $db->Quote(SOCIAL_EVENT_TYPE_INVITE);
				$q[] = "AND `nodes`.`uid` = " . $db->Quote($userid) . "))";
			} else {

				if (is_array($options['type'])) {
					if (count($options['type']) === 1) {
						$q[] = "AND `a`.`type` = " . $db->Quote($options['type'][0]);
					} else {
						$q[] = "AND `a`.`type` IN (" . implode(',', $db->Quote($options['type'])) . ")";
					}
				} else {
					$q[] = "AND `a`.`type` = " . $db->Quote($options['type']);
				}
			}
		}

		$q[] = "AND (b.`group_id` = 0";
		$q[] = " OR b.`group_id` = (";
		$q[] = "	select distinct(g.`id`) from `#__social_clusters` as g";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as gn on g.`id` = gn.`cluster_id`";
		}
		$q[] = "	where g.`id` = b.`group_id` and g.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_GROUP);
		if ($my->id) {
			$q[] = " and (gn.`uid` = " . $db->Quote($my->id) . " OR g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . "))";
		} else {
			$q[] = " and g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . ")";
		}
		$q[] = "	)";
		$q[] = ")";

		// page
		$q[] = "AND (b.`page_id` = 0";
		$q[] = " OR b.page_id = (";
		$q[] = "	select distinct(pp.`id`) from `#__social_clusters` as pp";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as ppn on pp.`id` = ppn.`cluster_id`";
		}
		$q[] = "	where pp.`id` = b.`page_id` and pp.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_PAGE);
		if ($my->id) {
			$q[] = " and (ppn.`uid` = " . $db->Quote($my->id) . " OR pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE) . ")";
		} else {
			$q[] = " and pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE);
		}
		$q[] = "	)";
		$q[] = ")";


		// Filter by featured
		if (isset($options['featured']) && $options['featured'] !== '' && $options['featured'] !== 'all') {
			$q[] = "AND `a`.`featured` = " . $db->Quote((int) $options['featured']);
		}

		// Time filter
		// Filter by past, ongoing, or upcoming
		if (!empty($options['past'])) {

			$q[] = "AND (";
			$q[] = "(`b`.`end` != '0000-00-00 00:00:00' AND `b`.`end` < " . $db->Quote($now) . ")";
			$q[] = "OR (`b`.`end` = '0000-00-00 00:00:00' AND `b`.`start` < " . $db->Quote($now) . ")";
			$q[] = ")";
		}

		if (!empty($options['ongoing']) && empty($options['upcoming'])) {
			$q[] = "AND `b`.`start` <= " . $db->q($now);
			$q[] = "AND `b`.`end` >= " . $db->q($now);
		}

		if (!empty($options['upcoming']) && empty($options['ongoing'])) {
			$q[] = "AND `b`.`start` >= " . $db->q($now);
		}

		if (!empty($options['ongoing']) && !empty($options['upcoming'])) {
			// Upcoming
			$q[] = "AND (`b`.`start` >= " . $db->q($now);

			// Ongoing with both startdate and enddate
			$q[] = "OR (`b`.`start` <= " . $db->q($now);
			$q[] = "AND `b`.`end` >= " . $db->q($now) . "))";
		}

		// UNION ALL with...
		$q[] = "  UNION ALL  ";


		// childs events
		$q[] = "SELECT `a1`.`id`, a1.`created`, b1.`start` FROM `#__social_clusters` AS `a1`";
		$q[] = "LEFT JOIN `#__social_events_meta` AS `b1` ON `a1`.`id` = `b1`.`cluster_id`";
		$q[] = "LEFT JOIN `#__social_clusters` as p2 on a1.`parent_id` = p2.`id`";

		if (isset($options['state'])) {
			$q[] = "AND `p2`.`state` = " . $db->q($options['state']);
		}

		$q[] = "LEFT JOIN `#__social_events_meta` as pm on `p2`.`id` = `pm`.`cluster_id`";

		if (!empty($options['past'])) {
			$q[] = "AND (";
			$q[] = "(`pm`.`end` != '0000-00-00 00:00:00' AND `pm`.`end` < " . $db->Quote($now) . ")";
			$q[] = "OR (`pm`.`end` = '0000-00-00 00:00:00' AND `pm`.`start` < " . $db->Quote($now) . ")";
			$q[] = ")";
		}

		if (!empty($options['ongoing']) && empty($options['upcoming'])) {
			$q[] = "AND `pm`.`start` <= " . $db->q($now);
			$q[] = "AND `pm`.`end` >= " . $db->q($now);
		}

		if (!empty($options['upcoming']) && empty($options['ongoing'])) {
			$q[] = "AND `pm`.`start` >= " . $db->q($now);
		}

		if (!empty($options['ongoing']) && !empty($options['upcoming'])) {
			// Upcoming
			$q[] = "AND (`pm`.`start` >= " . $db->q($now);

			// Ongoing with both startdate and enddate
			$q[] = "OR (`pm`.`start` <= " . $db->q($now);
			$q[] = "AND `pm`.`end` >= " . $db->q($now) . "))";
		}


		if (ES::isBlockEnabled()) {
			// $q[] = "LEFT JOIN `#__social_block_users` AS `bus1`";
			// $q[] = "ON (`a1`.`creator_uid` = `bus1`.`user_id`";
			// $q[] = "AND `bus1`.`target_id` = " . $db->Quote(JFactory::getUser()->id);

			// $q[] = "OR `a1`.`creator_uid` = `bus1`.`target_id`";
			// $q[] = "AND `bus1`.`user_id` = " . $db->Quote(JFactory::getUser()->id) . ')';

			$q[] = $this->getUserBlockingJoinQuery($my, 'bus1', 'creator_uid', 0, array('clusterColumnAlias' => 'id', 'idColumnTableAlias' => 'a1'));
		}

		if (isset($options['type']) && $options['type'] === 'user') {
			$q[] = "LEFT JOIN `#__social_clusters_nodes` AS `nodes1`";
			$q[] = "ON `a1`.`id` = `nodes1`.`cluster_id`";
		}

		$q[] = "WHERE `a1`.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_EVENT);
		$q[] = "AND `a1`.`parent_id` > " . $db->Quote('0');

		// Filter by state
		if (isset($options['state'])) {
			$q[] = "AND `a1`.`state` = " . $db->q($options['state']);
		}

		// Filter by event type
		if (isset($options['type']) && $options['type'] !== 'all') {

			// We do this is because other than getting Open and Closed, we also need to get Invite but only if it is participated by the user
			if ($options['type'] === 'user') {
				$userid = isset($options['userid']) ? $options['userid'] : ES::user()->id;

				$q[] = "AND (`a1`.`type` IN (" . implode(',', $db->Quote(array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_SEMI_PUBLIC))) . ")";
				$q[] = "OR (`a1`.`type` = " . $db->Quote(SOCIAL_EVENT_TYPE_INVITE);
				$q[] = "AND `nodes1`.`uid` = " . $db->Quote($userid) . "))";
			} else {

				if (is_array($options['type'])) {
					if (count($options['type']) === 1) {
						$q[] = "AND `a1`.`type` = " . $db->Quote($options['type'][0]);
					} else {
						$q[] = "AND `a1`.`type` IN (" . implode(',', $db->Quote($options['type'])) . ")";
					}
				} else {
					$q[] = "AND `a1`.`type` = " . $db->Quote($options['type']);
				}
			}
		}

		$q[] = "AND (b1.`group_id` = 0";
		$q[] = " OR b1.`group_id` = (";
		$q[] = "	select distinct(g.`id`) from `#__social_clusters` as g";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as gn on g.`id` = gn.`cluster_id`";
		}
		$q[] = "	where g.`id` = b1.`group_id` and g.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_GROUP);
		if ($my->id) {
			$q[] = " and (gn.`uid` = " . $db->Quote($my->id) . " OR g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . "))";
		} else {
			$q[] = " and g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . ")";
		}
		$q[] = "	)";
		$q[] = ")";

		// page
		$q[] = "AND (b1.`page_id` = 0";
		$q[] = " OR b1.page_id = (";
		$q[] = "	select distinct(pp.`id`) from `#__social_clusters` as pp";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as ppn on pp.`id` = ppn.`cluster_id`";
		}
		$q[] = "	where pp.`id` = b1.`page_id` and pp.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_PAGE);
		if ($my->id) {
			$q[] = " and (ppn.`uid` = " . $db->Quote($my->id) . " OR pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE) . ")";
		} else {
			$q[] = " and pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE);
		}
		$q[] = "	)";
		$q[] = ")";

		// Filter by featured
		if (isset($options['featured']) && $options['featured'] !== '' && $options['featured'] !== 'all') {
			$q[] = "AND `a1`.`featured` = " . $db->Quote((int) $options['featured']);
		}

		// Time filter
		// Filter by past, ongoing, or upcoming
		if (!empty($options['past'])) {
			$q[] = "AND (";
			$q[] = "(`b1`.`end` != '0000-00-00 00:00:00' AND `b1`.`end` < " . $db->Quote($now) . ")";
			$q[] = "OR (`b1`.`end` = '0000-00-00 00:00:00' AND `b1`.`start` < " . $db->Quote($now) . ")";
			$q[] = ")";
		}

		if (!empty($options['ongoing']) && empty($options['upcoming'])) {
			$q[] = "AND `b1`.`start` <= " . $db->q($now);
			$q[] = "AND `b1`.`end` >= " . $db->q($now);
		}

		if (!empty($options['upcoming']) && empty($options['ongoing'])) {
			$q[] = "AND `b1`.`start` >= " . $db->q($now);
		}

		if (!empty($options['ongoing']) && !empty($options['upcoming'])) {
			// Upcoming
			$q[] = "AND (`b1`.`start` >= " . $db->q($now);

			// Ongoing with both startdate and enddate
			$q[] = "OR (`b1`.`start` <= " . $db->q($now);
			$q[] = "AND `b1`.`end` >= " . $db->q($now) . "))";
		}

		$q[] = "AND pm.`id` IS NULL";
		$q[] = "GROUP BY a1.`parent_id`";


		// Conditions ends here
		// We set the total here first before going into order and limit block
		$sql = $db->sql();
		$sql->raw(implode(' ', $q));
		$totalSQL = $sql->getSql();

		// echo $totalSQL;

		// Ordering
		if (isset($options['ordering'])) {
			$direction = isset($options['direction']) ? $options['direction'] : 'asc';

			switch ($options['ordering']) {
				case 'recent':
				case 'created':
					$q[] = "ORDER BY `created` $direction";
				break;

				default:
				case 'start':
					$q[] = "ORDER BY `start` $direction";
				break;
			}
		}

		// Limit
		if (isset($options['limit']) && $options['limit']) {
			$limit = $options['limit'];

			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->input->get('limitstart', 0, 'int');

			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);

			$this->setTotal($totalSQL, true);

			$q[] = "LIMIT $limitstart, $limit";
		}

		$query = implode(' ', $q);

		$sql = $db->sql();
		$sql->raw($query);

		// echo $sql;
		// echo '<br><br>';

		$db->setQuery($sql);

		$result = $db->loadObjectList('id');

		if (empty($result)) {
			return array();
		}

		$ids = array_keys($result);

		// Support for lightweight mode where we only want the ids
		if (isset($options['idonly']) && $options['idonly'] === true) {
			return $ids;
		}

		// ES::event() is array-ids-ready
		$events = ES::event($ids);

		return $events;
	}

	/**
	 * Returns array of SocialEvent object for frontend listing.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getEvents($options = array(), $debug = false)
	{
		$db = ES::db();
		$q = array();
		$my = ES::user();

		// this option only available in 'all events' page.
		if (isset($options['nonrepetitive']) && $options['nonrepetitive']) {
			$events = $this->getNonRepetitiveEvents($options);
			return $events;
		}

		if (!empty($options['location'])) {
			// If this is a location based search, then we want to include distance column
			$searchUnit = strtoupper(ES::config()->get('general.location.proximity.unit','mile'));

			$unit = constant('SOCIAL_LOCATION_UNIT_' . $searchUnit);
			$radius = constant('SOCIAL_LOCATION_RADIUS_' . $searchUnit);

			$lat = $options['latitude'];
			$lng = $options['longitude'];

			if (!$lat && !$lng) {
				// lets get the lat and lon from current logged in user address
				$address = $my->getFieldValue('ADDRESS');
				$lat = $address->value->latitude ? $address->value->latitude : 0;
				$lng = $address->value->longitude ? $address->value->longitude : 0;
			}

			// ($radius * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude)))) as distance

			// If there is a distance provided, then we need to put the distance column into a subquery in order to filter condition on it
			if (!empty($options['distance'])) {
				$distance = $options['distance'];

				$lat1 = $lat - ($distance / $unit);
				$lat2 = $lat + ($distance / $unit);

				$lng1 = $lng - ($distance / abs(cos(deg2rad($lat)) * $unit));
				$lng2 = $lng + ($distance / abs(cos(deg2rad($lat)) * $unit));

				$q[] = "SELECT DISTINCT `a`.`id`, `a`.`distance` FROM (
					SELECT `x`.*, ($radius * acos(cos(radians($lat)) * cos(radians(`x`.`latitude`)) * cos(radians(`x`.`longitude`) - radians($lng)) + sin(radians($lat)) * sin(radians(`x`.`latitude`)))) AS `distance` FROM `#__social_clusters` AS `x` WHERE `x`.`cluster_type` = " . $db->q(SOCIAL_TYPE_EVENT) . " AND (cast(`x`.`latitude` AS DECIMAL(10, 6)) BETWEEN $lat1 AND $lat2) AND (cast(`x`.`longitude` AS DECIMAL(10, 6)) BETWEEN $lng1 AND $lng2)
				) AS `a`";
			} else {
				$q[] = "SELECT DISTINCT `a`.`id`, ($radius * acos(cos(radians($lat)) * cos(radians(`a`.`latitude`)) * cos(radians(`a`.`longitude`) - radians($lng)) + sin(radians($lat)) * sin(radians(`a`.`latitude`)))) AS `distance` FROM `#__social_clusters` AS `a`";
			}
		} else {
			$q[] = "SELECT DISTINCT `a`.`id` AS `id` FROM `#__social_clusters` AS `a`";
		}

		$q[] = "INNER JOIN `#__social_events_meta` AS `b` ON `a`.`id` = `b`.`cluster_id`";

		if (ES::isBlockEnabled()) {
			// $q[] = "LEFT JOIN `#__social_block_users` AS `bus`";
			// $q[] = "ON (`a`.`creator_uid` = `bus`.`user_id`";
			// $q[] = "AND `bus`.`target_id` = " . $db->Quote(JFactory::getUser()->id);

			// $q[] = "OR `a`.`creator_uid` = `bus`.`target_id`";
			// $q[] = "AND `bus`.`user_id` = " . $db->Quote(JFactory::getUser()->id) . ')';

			$q[] = $this->getUserBlockingJoinQuery($my, 'bus', 'creator_uid', 0, array('clusterColumnAlias' => 'id'));
		}


		if (isset($options['type']) && $options['type'] === 'user') {
			$q[] = "LEFT JOIN `#__social_clusters_nodes` AS `nodes`";
			$q[] = "ON `a`.`id` = `nodes`.`cluster_id`";
		}

		if (isset($options['guestuid']) || isset($options['creator_join'])) {
			$q[] = "LEFT JOIN `#__social_clusters_nodes` AS `c`";
			$q[] = "ON `a`.`id` = `c`.`cluster_id`";
		}

		$q[] = "WHERE `a`.`cluster_type` = " . $db->q(SOCIAL_TYPE_EVENT);


		// Filter by event type
		if (isset($options['type']) && $options['type'] !== 'all') {

			// We do this is because other than getting Open and Closed, we also need to get Invite but only if it is participated by the user
			if ($options['type'] === 'user') {
				$userid = isset($options['userid']) ? $options['userid'] : ES::user()->id;

				$q[] = "AND (`a`.`type` IN (" . implode(',', $db->q(array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_SEMI_PUBLIC))) . ")";
				$q[] = "OR (`a`.`type` = " . $db->q(SOCIAL_EVENT_TYPE_INVITE);
				$q[] = "AND `nodes`.`uid` = " . $db->q($userid) . "))";

			} else {


				if (is_array($options['type'])) {
					if (count($options['type']) === 1) {
						$q[] = "AND `a`.`type` = " . $db->q($options['type'][0]);
					} else {
						$q[] = "AND `a`.`type` IN (" . implode(',', $db->q($options['type'])) . ")";
					}
				} else {
					$q[] = "AND `a`.`type` = " . $db->q($options['type']);
				}
			}
		}

		if (isset($options['type']) && $options['type'] == 'all' &&
			isset($options['creator_type']) && $options['creator_type'] == 'user' &&
			isset($options['creator_uid']) && $options['creator_uid'] != ES::user()->id) {
			// we need to get only events that are viewable by current logged in user.
			$q[] = "AND (`a`.`type` IN (" . implode(',', $db->q(array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_SEMI_PUBLIC))) . "))";
		}

		// check if the events is associated with cluster or not. if yes, make sure the user has the access to these cluster. #4105
		// group
		$q[] = "AND (b.`group_id` = 0";
		$q[] = " OR b.`group_id` = (";
		$q[] = "	select distinct(g.`id`) from `#__social_clusters` as g";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as gn on g.`id` = gn.`cluster_id`";
		}
		$q[] = "	where g.`id` = b.`group_id` and g.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_GROUP);
		if ($my->id) {
			$q[] = " and (gn.`uid` = " . $db->Quote($my->id) . " OR g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . "))";
		} else {
			$q[] = " and g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . ")";
		}
		$q[] = "	)";
		$q[] = ")";

		// page
		$q[] = "AND (b.`page_id` = 0";
		$q[] = " OR b.page_id = (";
		$q[] = "	select distinct(pp.`id`) from `#__social_clusters` as pp";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as ppn on pp.`id` = ppn.`cluster_id`";
		}
		$q[] = "	where pp.`id` = b.`page_id` and pp.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_PAGE);
		if ($my->id) {
			$q[] = " and (ppn.`uid` = " . $db->Quote($my->id) . " OR pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE) . ")";
		} else {
			$q[] = " and pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE);
		}
		$q[] = "	)";
		$q[] = ")";


		// Search for events by title
		$search = $this->normalize($options, 'search', null);

		if (!is_null($search)) {
			$q[] = 'AND a.`title` LIKE ' . $db->Quote('%' . $search . '%');
		}

		// Filter by category id
		if (isset($options['category']) && $options['category'] !== 'all') {

			$category = $options['category'];

			if (is_array($category)) {
				$q[] = "AND `a`.`category_id` IN (". implode(',', $db->q($category)) . ")";
			} else {
				$q[] = "AND `a`.`category_id` = " . $db->q($category);
			}
		}

		// Filter by featured
		if (isset($options['featured']) && $options['featured'] !== '' && $options['featured'] !== 'all') {
			$q[] = "AND `a`.`featured` = " . $db->q((int) $options['featured']);
		}

		// Inclusion
		if (isset($options['inclusion']) && !empty($options['inclusion'])) {
			$includeEvent = array();
			$inclusions = $options['inclusion'];

			foreach ($inclusions as $inclusion) {
				if ($inclusion !== '') {
					$includeEvent[] = $inclusion;
				}
			}

			if (!empty($includeEvent)) {
				$q[] = "AND `a`.`id` IN (" . implode(',', $db->q($includeEvent)) . ")";
			}
		}

		// Filter by creator
		if (isset($options['creator_uid'])) {

			if (isset($options['creator_join'])) {

				$tmpCond = "(";
				$tmpCond .= "(`a`.`creator_uid` = " . $db->q($options['creator_uid']);
				$tmpCond .= "AND `a`.`creator_type` = " . $db->q(isset($options['creator_type']) ? $options['creator_type'] : SOCIAL_TYPE_USER) . ")";
				$tmpCond .= " OR ";
				$tmpCond .= "(`c`.`uid` = " . $db->q($options['creator_uid']);
				$tmpCond .= "AND `c`.`state` = " . $db->q(SOCIAL_EVENT_GUEST_GOING) . ")";
				$tmpCond .= ")";

				$q[] = "AND " . $tmpCond;

			} else {
				$q[] = "AND `a`.`creator_uid` = " . $db->q($options['creator_uid']);
				$q[] = "AND `a`.`creator_type` = " . $db->q(isset($options['creator_type']) ? $options['creator_type'] : SOCIAL_TYPE_USER);
			}
		}

		// Filter by state
		if (isset($options['state'])) {
			$q[] = "AND `a`.`state` = " . $db->q($options['state']);
		}

		// Filter by guest state
		if (isset($options['guestuid'])) {
			$q[] = "AND `c`.`uid` = " . $db->q($options['guestuid']);

			if (isset($options['gueststate']) && $options['gueststate'] !== 'all') {
				$q[] = "AND `c`.`state` = " . $db->q($options['gueststate']);
			}
		}

		// Time filter
		// Filter by past, ongoing, or upcoming
		$now = ES::date()->toSql(true);
		if (!empty($options['past'])) {
			$q[] = "AND (";

			$q[] = "(`b`.`end` != '0000-00-00 00:00:00' AND `b`.`end` < " . $db->q($now) . ")";

			$q[] = "OR (`b`.`end` = '0000-00-00 00:00:00' AND `b`.`start` < " . $db->q($now) . ")";

			$q[] = ")";
		}

		// No need to check for end != 0000-00-00 00:00:00 because $now is ALWAYS > 0000-00-00 00:00:00, and b.end >= now will never get 0000-00-00 00:00:00
		if (!empty($options['ongoing']) && empty($options['upcoming'])) {
			$q[] = "AND `b`.`start` <= " . $db->q($now);
			$q[] = "AND `b`.`end` >= " . $db->q($now);
		}

		if (!empty($options['upcoming']) && empty($options['ongoing'])) {
			$q[] = "AND `b`.`start` >= " . $db->q($now);
		}

		if (!empty($options['ongoing']) && !empty($options['upcoming'])) {
			// Upcoming
			$q[] = "AND (`b`.`start` >= " . $db->q($now);

			// Ongoing with both startdate and enddate
			$q[] = "OR (`b`.`start` <= " . $db->q($now);
			$q[] = "AND `b`.`end` >= " . $db->q($now) . "))";
		}

		// Manual filter by start and end range
		if (!empty($options['start-before'])) {
			$q[] = "AND `b`.`start` <= " . $db->q($options['start-before']);
		}

		if (!empty($options['start-after'])) {
			$q[] = "AND `b`.`start` >= " . $db->q($options['start-after']);
		}

		if (!empty($options['end-before'])) {
			$q[] = "AND `b`.`end` <= " . $db->q($options['end-before']);
		}

		if (!empty($options['end-after'])) {
			$q[] = "AND `b`.`end` >= " . $db->q($options['end-after']);
		}

		// Date range
		if (!empty($options['dateRange'])) {
			$q[] = "AND (";
			$q[] = "((`b`.`start` <= " . $db->q($options['range-start']) . " AND `b`.`end` >= " . $db->q($options['range-start']) . ")";
			$q[] = "OR";
			$q[] = "(`b`.`start` <= " . $db->q($options['range-end']) . " AND `b`.`end` >= " . $db->q($options['range-end']) . ")";
			$q[] = ") OR (`b`.`start` <= " . $db->q($options['range-end']) . " AND `b`.`start` >= " . $db->q($options['range-start']) . "))";
		}

		// Nearby filter
		if (!empty($options['location']) && !empty($options['distance'])) {
			$range = isset($options['range']) ? $options['range'] : '<=';
			$q[] = "AND `a`.`distance` $range " . (float) $options['distance'];
		}

		// Group event filter
		if (isset($options['group_id']) && $options['group_id'] !== 'all') {
			if (is_array($options['group_id'])) {
				$q[] = "AND `b`.`group_id` IN (" . implode(',', $db->q($options['group_id'])) . ")";
			} else {
				$q[] = "AND `b`.`group_id` = " . $db->q($options['group_id']);
			}
		}

		// Group event filter
		if (isset($options['page_id']) && $options['page_id'] !== 'all') {
			if (is_array($options['page_id'])) {
				$q[] = "AND `b`.`page_id` IN (" . implode(',', $db->q($options['page_id'])) . ")";
			} else {
				$q[] = "AND `b`.`page_id` = " . $db->q($options['page_id']);
			}
		}

		// Recurring event filter
		if (isset($options['parent_id'])) {
			$q[] = "AND `a`.`parent_id` = " . $db->q($options['parent_id']);
		}

		if (ES::isBlockEnabled()) {
			// $q[] = "AND `bus`.`id` IS NULL";
			$q[] = $this->getUserBlockingClauseQuery($my);
		}


		// Ordering
		if (isset($options['ordering'])) {
			$direction = isset($options['direction']) ? $options['direction'] : 'asc';

			switch ($options['ordering']) {
				case 'recent':
				case 'created':
					$q[] = "ORDER BY `a`.`created` desc";
				break;

				default:
				case 'start':
					$q[] = "ORDER BY `b`.`start` $direction";
				break;

				case 'end':
					$q[] = "ORDER BY `b`.`end` $direction";
				break;

				case 'random':
					$q[] = "ORDER BY RAND() $direction";
				break;

				case 'distance':
					$q[] = "ORDER BY `a`.`distance` $direction";
				break;
			}
		}

		// Conditions ends here
		// We set the total here first before going into order and limit block
		$sql = $db->sql();
		$sql->raw(implode(' ', $q));

		// if ($debug) {
			// echo $sql->debug();
			// echo '<br><br>';
		// }

		// Limit
		if (isset($options['limit']) && $options['limit']) {
			$limit = $options['limit'];

			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->input->get('limitstart', 0, 'int');

			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);

			$this->setTotal($sql->getSql(), true);

			$q[] = "LIMIT $limitstart, $limit";
		}

		$query = implode(' ', $q);

		$sql = $db->sql();
		$sql->raw($query);

		// echo $sql;
		// echo '<br><br>';

		$db->setQuery($sql);

		$result = $db->loadObjectList('id');

		if (empty($result)) {
			return array();
		}

		$ids = array_keys($result);

		// Support for lightweight mode where we only want the ids
		if (isset($options['idonly']) && $options['idonly'] === true) {
			return $ids;
		}

		// ES::event() is array-ids-ready
		$events = ES::event($ids);

		// Manually assign the distance data
		if (!empty($options['location'])) {
			foreach ($events as $event) {
				$event->distance = round($result[$event->id]->distance, 1);
			}
		}

		return $events;
	}

	/**
	 * Returns the total number of events featured
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalFeaturedEvents($totalOptions = array())
	{
		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'featured' => true, 'ongoing' => true, 'upcoming' => true);

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of events created by the user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalCreatedEvents($userId = null, $totalOptions = array())
	{
		$user = ES::user($userId);

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'creator_uid' => $user->id, 'creator_type' => SOCIAL_TYPE_USER, 'type' => 'all');

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of events created or joined by the user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalCreatedJoinedEvents($userId = null, $totalOptions = array())
	{
		$user = ES::user($userId);

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'creator_type' => SOCIAL_TYPE_USER, 'type' => 'all');

		if (!isset($totalOptions['viewClusterEvents'])) {
			$defaultOptions['creator_uid'] = $user->id;
		}

		if (!isset($totalOptions['excludeJoinEvent'])) {
			$defaultOptions['creator_join'] = true;
		}

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of events created by the user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalInvitedEvents($userId = null, $totalOptions = array())
	{
		$user = ES::user($userId);

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'guestuid' => $user->id, 'gueststate' => SOCIAL_EVENT_GUEST_INVITED, 'type' => 'all', 'ongoing' => true, 'upcoming' => true);

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of past events
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalPastEvents($userId = null, $totalOptions = array())
	{
		$now = ES::date();
		$user = ES::user($userId);

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'type' => $user->isSiteAdmin() ? 'all' : 'user');
		$defaultOptions['start-before'] = $now->toSql();

		$defaultOptions['past'] = true;

		// For past events, these needs to be off
		$defaultOptions['ongoing'] = false;
		$defaultOptions['upcoming'] = false;

		$options = array_merge($totalOptions, $defaultOptions);

		if (isset($options['featured'])) {
			unset($options['featured']);
		}

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of events created by the user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalWeekEvents($weekCount = 1, $userId = null, $totalOptions = array())
	{
		$user = ES::user($userId);

		// Compute the timestamp
		$now = ES::date();

		// Compute the timestamp
		$timestamp = strtotime('+' . $weekCount . ' week');
		$date = ES::date($timestamp);

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'type' => $user->isSiteAdmin() ? 'all' : 'user', 'start-after' => $now->toSql(), 'start-before' => $date->toSql(), 'featured' => false);

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of events happening tomorrow
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalEventsTomorrow($timestamp = '', $totalOptions = array())
	{
		$user = ES::user();

		if (is_string($timestamp)) {
			$date = ES::date($timestamp);
		}

		if (is_object($timestamp)) {
			$date = $timestamp;
		}

		if (!$timestamp) {
			$date = ES::date();
		}

		$date = $date->modify('+1 day');

		$defaultOptions = array(
						'state' => SOCIAL_STATE_PUBLISHED,
						'type' => $user->isSiteAdmin() ? 'all' : 'user',
						'dateRange' => true,
						'range-start' => $date->format('Y-m-d 00:00:00', true),
						'range-end' => $date->format('Y-m-d 23:59:59', true),
						'featured' => 'all'
					);

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns the total number of events happening today
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalEventsToday($timestamp = '', $totalOptions = array())
	{
		$user = ES::user();

		if (is_string($timestamp)) {
			$date = ES::date($timestamp);
		}

		if (is_object($timestamp)) {
			$date = $timestamp;
		}

		if (!$timestamp) {
			$date = ES::date()->modify('+1 day');
		}

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'type' => $user->isSiteAdmin() ? 'all' : 'user');
		$defaultOptions['dateRange'] = true;
		$defaultOptions['range-start'] = $date->format('Y-m-d 00:00:00', true);
		$defaultOptions['range-end'] = $date->format('Y-m-d 23:59:59', true);
		$defaultOptions['featured'] = 'all';

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Get total events in a month
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalMonthEvents($date = '', $userId = null, $totalOptions = array())
	{
		$user = ES::user($userId);

		if (!$date) {
			$date = ES::date();
		}

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'type' => $user->isSiteAdmin() ? 'all' : 'user');

		// Get the maximum number of days for the month
		$max = $date->format('t', true);

		$defaultOptions['start-after'] = $date->format('Y-m-01 00:00:00', true);
		$defaultOptions['start-before'] = $date->format('Y-m-' . $max . ' 23:59:59', true);

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}


	/**
	 * Get total events in a year
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalYearEvents($date = '', $userId = null, $totalOptions = array())
	{
		$user = ES::user($userId);

		if (!$date) {
			$date = ES::date();
		}

		$defaultOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'type' => $user->isSiteAdmin() ? 'all' : 'user');

		$defaultOptions['start-after'] = $date->format('Y-01-01 00:00:00', true);
		$defaultOptions['start-before'] = $date->format('Y-12-31 23:59:59', true);

		$options = array_merge($totalOptions, $defaultOptions);

		$total = $this->getTotalEvents($options);

		return $total;
	}

	/**
	 * Returns total number of event based on options filtering for frontend listing.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getTotalEvents($options = array(), $debug = false)
	{
		$db = ES::db();
		$my = ES::user();

		$checkUserBlock = isset($options['userblock']) ? $options['userblock'] : true;

		$q = array();
		$q[] = "select count(distinct(a.id)) as id";
		$q[] = " FROM `#__social_clusters` as a";
		$q[] = " 	INNER JOIN `#__social_events_meta` as b ON a.`id` = b.`cluster_id`";

		if ($checkUserBlock && ES::isBlockEnabled()) {
			// $q[] = "LEFT JOIN `#__social_block_users` AS `bus`";
			// $q[] = "ON (`a`.`creator_uid` = `bus`.`user_id`";
			// $q[] = "AND `bus`.`target_id` = " . $db->Quote(JFactory::getUser()->id);

			// $q[] = "OR `a`.`creator_uid` = `bus`.`target_id`";
			// $q[] = "AND `bus`.`user_id` = " . $db->Quote(JFactory::getUser()->id) . ')';

			$q[] = $this->getUserBlockingJoinQuery($my, 'bus', 'creator_uid', 0, array('clusterColumnAlias' => 'id'));
		}

		if (isset($options['type']) && $options['type'] === 'user') {
			$q[] = "LEFT JOIN `#__social_clusters_nodes` AS `nodes`";
			$q[] = "ON `a`.`id` = `nodes`.`cluster_id`";
		}

		if (isset($options['guestuid']) || isset($options['creator_join'])) {
			$q[] = "LEFT JOIN `#__social_clusters_nodes` AS `c`";
			$q[] = "ON `a`.`id` = `c`.`cluster_id`";
		}

		$q[] = "WHERE a.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_EVENT);

		// Filter by event type
		if (isset($options['type']) && $options['type'] !== 'all') {

			// We do this is because other than getting Open and Closed, we also need to get Invite but only if it is participated by the user
			if ($options['type'] === 'user') {
				$userid = isset($options['userid']) ? $options['userid'] : ES::user()->id;

				$q[] = "AND (`a`.`type` IN (" . implode(',', $db->q(array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_SEMI_PUBLIC))) . ")";
				$q[] = "OR (`a`.`type` = " . $db->q(SOCIAL_EVENT_TYPE_INVITE);
				$q[] = "AND `nodes`.`uid` = " . $db->q($userid) . "))";

			} else {
				if (is_array($options['type'])) {
					if (count($options['type']) === 1) {
						$q[] = "AND `a`.`type` = " . $db->q($options['type'][0]);
					} else {
						$q[] = "AND `a`.`type` IN (" . implode(',', $db->q($options['type'])) . ")";
					}
				} else {
					$q[] = "AND `a`.`type` = " . $db->q($options['type']);
				}

			}
		}

		if (isset($options['type']) && $options['type'] == 'all' &&
			isset($options['creator_type']) && $options['creator_type'] == 'user' &&
			isset($options['creator_uid']) && $options['creator_uid'] != ES::user()->id) {
			// we need to get only events that are viewable by current logged in user.
			$q[] = "AND (`a`.`type` IN (" . implode(',', $db->q(array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_SEMI_PUBLIC))) . "))";
		}

		// check if the events is associated with cluster or not. if yes, make sure the user has the access to these cluster. #4105
		// group
		$q[] = "AND (b.`group_id` = 0";
		$q[] = " OR b.`group_id` = (";
		$q[] = "	select distinct(g.`id`) from `#__social_clusters` as g";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as gn on g.`id` = gn.`cluster_id`";
		}
		$q[] = "	where g.`id` = b.`group_id` and g.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_GROUP);
		if ($my->id) {
			$q[] = " and (gn.`uid` = " . $db->Quote($my->id) . " OR g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . "))";
		} else {
			$q[] = " and g.`type` IN (" . implode(',', $db->q(array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE))) . ")";
		}
		$q[] = "	)";
		$q[] = ")";

		// page
		$q[] = "AND (b.`page_id` = 0";
		$q[] = " OR b.page_id = (";
		$q[] = "	select distinct(pp.`id`) from `#__social_clusters` as pp";
		if ($my->id) {
			$q[] = "		left join `#__social_clusters_nodes` as ppn on pp.`id` = ppn.`cluster_id`";
		}
		$q[] = "	where pp.`id` = b.`page_id` and pp.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_PAGE);
		if ($my->id) {
			$q[] = " and (ppn.`uid` = " . $db->Quote($my->id) . " OR pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE) . ")";
		} else {
			$q[] = " and pp.`type` = " . $db->Quote(SOCIAL_PAGES_PUBLIC_TYPE);
		}
		$q[] = "	)";
		$q[] = ")";


		// Filter by category id
		if (isset($options['category']) && $options['category'] !== 'all') {
			$category = $options['category'];
			if (is_array($category)) {
				$q[] = "AND `a`.`category_id` IN (". implode(',', $db->q($category)) . ")";
			} else {
				$q[] = "AND `a`.`category_id` = " . $db->q($category);
			}
		}

		// Filter by featured
		if (isset($options['featured'])) {
			$q[] = "AND `a`.`featured` = " . $db->q((int) $options['featured']);
		}

		// Inclusion
		if (isset($options['inclusion']) && !empty($options['inclusion'])) {
			$includeEvent = array();
			$inclusions = $options['inclusion'];

			foreach ($inclusions as $inclusion) {
				if ($inclusion !== '') {
					$includeEvent[] = $inclusion;
				}
			}

			if (!empty($includeEvent)) {
				$q[] = "AND `a`.`id` IN (" . implode(',', $db->q($includeEvent)) . ")";
			}
		}

		// Filter by creator
		if (isset($options['creator_uid'])) {

			if (isset($options['creator_join']) && $options['creator_join']) {

				$tmpCond = "(";
				$tmpCond .= "(`a`.`creator_uid` = " . $db->q($options['creator_uid']);
				$tmpCond .= "AND `a`.`creator_type` = " . $db->q(isset($options['creator_type']) ? $options['creator_type'] : SOCIAL_TYPE_USER) . ")";
				$tmpCond .= " OR ";
				$tmpCond .= "(`c`.`uid` = " . $db->q($options['creator_uid']);
				$tmpCond .= "AND `c`.`state` = " . $db->q(SOCIAL_EVENT_GUEST_GOING) . ")";
				$tmpCond .= ")";

				$q[] = "AND " . $tmpCond;

			} else {
				$q[] = "AND `a`.`creator_uid` = " . $db->q($options['creator_uid']);
				$q[] = "AND `a`.`creator_type` = " . $db->q(isset($options['creator_type']) ? $options['creator_type'] : SOCIAL_TYPE_USER);
			}
		}

		if (isset($options['state'])) {
			$q[] = "AND `a`.`state` = " . $db->q($options['state']);
		}

		// Filter by guest state
		if (isset($options['guestuid'])) {
			$q[] = "AND `c`.`uid` = " . $db->q($options['guestuid']);

			if (isset($options['gueststate']) && $options['gueststate'] !== 'all') {
				$q[] = "AND `c`.`state` = " . $db->q($options['gueststate']);
			}
		}

		// Time filter
		// Filter by past, ongoing, or upcoming
		$now = ES::date()->toSql(true);

		if (!empty($options['past'])) {
			$q[] = "AND (";

			$q[] = "(`b`.`end` != '0000-00-00 00:00:00' AND `b`.`end` < " . $db->q($now) . ")";

			$q[] = "OR (`b`.`end` = '0000-00-00 00:00:00' AND `b`.`start` < " . $db->q($now) . ")";

			$q[] = ")";
		}

		// No need to check for end != 0000-00-00 00:00:00 because $now is ALWAYS > 0000-00-00 00:00:00, and b.end >= now will never get 0000-00-00 00:00:00
		if (!empty($options['ongoing']) && empty($options['upcoming'])) {
			$q[] = "AND `b`.`start` <= " . $db->q($now);
			$q[] = "AND `b`.`end` >= " . $db->q($now);
		}

		if (!empty($options['upcoming']) && empty($options['ongoing'])) {
			$q[] = "AND `b`.`start` >= " . $db->q($now);
		}

		if (!empty($options['ongoing']) && !empty($options['upcoming'])) {
			// Upcoming
			$q[] = "AND (`b`.`start` >= " . $db->q($now);

			// Ongoing with both startdate and enddate
			$q[] = "OR (`b`.`start` <= " . $db->q($now);
			$q[] = "AND `b`.`end` >= " . $db->q($now) . "))";
		}

		// Manual filter by start and end range
		if (!empty($options['start-before'])) {
			$q[] = "AND `b`.`start` <= " . $db->q($options['start-before']);
		}

		if (!empty($options['start-after'])) {
			$q[] = "AND `b`.`start` >= " . $db->q($options['start-after']);
		}

		if (!empty($options['end-before'])) {
			$q[] = "AND `b`.`end` <= " . $db->q($options['end-before']);
		}

		if (!empty($options['end-after'])) {
			$q[] = "AND `b`.`end` >= " . $db->q($options['end-after']);
		}

		// Date range
		if (!empty($options['dateRange'])) {
			$q[] = "AND (";
			$q[] = "((`b`.`start` <= " . $db->q($options['range-start']) . " AND `b`.`end` >= " . $db->q($options['range-start']) . ")";
			$q[] = "OR";
			$q[] = "(`b`.`start` <= " . $db->q($options['range-end']) . " AND `b`.`end` >= " . $db->q($options['range-end']) . ")";
			$q[] = ") OR (`b`.`start` <= " . $db->q($options['range-end']) . " AND `b`.`start` >= " . $db->q($options['range-start']) . "))";
		}

		// Nearby filter
		if (!empty($options['location']) && !empty($options['distance'])) {
			$range = isset($options['range']) ? $options['range'] : '<=';
			$q[] = "AND `a`.`distance` $range " . (float) $options['distance'];
		}

		// Group event filter
		if (isset($options['group_id']) && $options['group_id'] !== 'all') {
			if (is_array($options['group_id'])) {
				$q[] = "AND `b`.`group_id` IN (" . implode(',', $db->q($options['group_id'])) . ")";
			} else {
				$q[] = "AND `b`.`group_id` = " . $db->q($options['group_id']);
			}
		}

		// Group event filter
		if (isset($options['page_id']) && $options['page_id'] !== 'all') {
			if (is_array($options['page_id'])) {
				$q[] = "AND `b`.`page_id` IN (" . implode(',', $db->q($options['page_id'])) . ")";
			} else {
				$q[] = "AND `b`.`page_id` = " . $db->q($options['page_id']);
			}
		}

		// Recurring event filter
		if (isset($options['parent_id'])) {
			$q[] = "AND `a`.`parent_id` = " . $db->q($options['parent_id']);
		}

		if ($checkUserBlock && ES::isBlockEnabled()) {
			// $q[] = "AND `bus`.`id` IS NULL";

			$q[] = $this->getUserBlockingClauseQuery($my);
		}

		$query = implode(" ", $q);

		if ($debug) {
			echo $query;exit;
		}

		$db->setQuery($query);

		$result = $db->loadResult();

		return (int) $result;
	}

	/**
	 * Returns the total pending events for backend.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getPendingCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('cluster_type', SOCIAL_TYPE_EVENT);
		$sql->where('state',  array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING), 'IN');

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return (int) $result;
	}

	/**
	 * Main function that initiates the required event's meta data.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getMeta($ids = array())
	{
		static $loaded = array();

		$loadItems = array();

		foreach ($ids as $id) {
			$id = (int) $id;

			if (!isset($loaded[$id])) {
				$loadItems[] = $id;

				$loaded[$id] = false;
			}
		}

		if (!empty($loadItems)) {
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
			$sql->column('c.id', 'cover_id');
			$sql->column('c.uid', 'cover_uid');
			$sql->column('c.type', 'cover_type');
			$sql->column('c.photo_id', 'cover_photo_id');
			$sql->column('c.cover_id', 'cover_cover_id');
			$sql->column('c.x', 'cover_x');
			$sql->column('c.y', 'cover_y');
			$sql->column('c.modified', 'cover_modified');
			$sql->leftjoin('#__social_avatars', 'b');
			$sql->on('b.uid', 'a.id');
			$sql->on('b.type', 'a.cluster_type');
			$sql->leftjoin('#__social_covers', 'c');
			$sql->on('c.uid', 'a.id');
			$sql->on('c.type', 'a.cluster_type');

			if (count($loadItems) > 1) {
				$sql->where('a.id', $loadItems, 'IN');
			} else {
				$sql->where('a.id', $loadItems[0]);
			}

			$sql->where('a.cluster_type', SOCIAL_TYPE_EVENT);

			$db->setQuery($sql);

			$events = $db->loadObjectList('id');

			// Use array_replace instead of array_merge because the key of the array is integer, and array_merge won't replace if the key is integer.
			// array_replace is only supported php>5.3

			// $loaded = array_replace($loaded, $events);

			// While array_replace goes by base, replacement
			// Using + changes the order where base always goes last
			$loaded = $events + $loaded;
		}

		$data = array();

		foreach ($ids as $id) {
			$data[] = $loaded[$id];
		}

		return $data;
	}

	/**
	 * Retrieves the total number of pending users in the event.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getTotalPendingGuests($eventId)
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

		$sql->where('a.cluster_id', $eventId);
		$sql->where('a.state', SOCIAL_CLUSTER_PENDING);
		$sql->where('b.block', SOCIAL_JOOMLA_USER_UNBLOCKED);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}


	/**
	 * Retrieves the "Steps" available for the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getSteps(SocialEvent $event, $activeStep = 0)
	{
		static $items = array();

		if (!isset($items[$event->id])) {

			// Load admin's language file
			ES::language()->loadAdmin();

			// Get available workflows for this group
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($event->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, SOCIAL_EVENT_VIEW_DISPLAY);

			// Initialize the fields library
			$fieldsLib = ES::fields();
			$fieldsLib->init(array('privacy' => false));

			$fieldsModel = ES::model('Fields');

			$index = 1;

			foreach ($steps as &$step) {

				// Get the step title
				$step->title = JText::_($step->title);
				$step->active = $activeStep == $index;
				$step->index = $index;
				$step->hide = false;

				// Get the url for the step
				// $step->url = ESR::events(array('layout' => 'item', 'id' => $event->getAlias(), 'type' => 'info', 'infostep' => $index), false);
				$step->url = $event->getPermalink(false, false, 'item', true, array('page'=>'info', 'infostep' => $index));

				if ($index === 1) {
					// $step->url = ESR::events(array('layout' => 'item', 'id' => $event->getAlias(), 'type' => 'info'), false);
					$step->url = $event->getPermalink(false, false, 'item', true, array('page'=>'info'));
				}

				// @TODO: Should the step be hidden
				$index++;
			}

			$items[$event->id] = $steps;
		}

		return $items[$event->id];
	}

	/**
	 * Retrieves the "About" information from a group
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAbout(SocialEvent $event, $activeStep = 0, $retrieveContents = true)
	{
		static $items = array();

		if (!isset($items[$event->id])) {
			// Load admin's language file
			ES::language()->loadAdmin();

			// Get available workflows for this group
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($event->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, SOCIAL_EVENT_VIEW_DISPLAY);

			// Initialize the fields library
			$fieldsLib = ES::fields();
			$fieldsLib->init(array('privacy' => false));

			$fieldsModel = ES::model('Fields');

			$index = 1;

			foreach ($steps as &$step) {

				$fieldOptions = array('step_id' => $step->id, 'data' => true, 'dataId' => $event->id, 'dataType' => SOCIAL_TYPE_EVENT, 'visible' => SOCIAL_EVENT_VIEW_DISPLAY);

				$step->fields = $fieldsModel->getCustomFields($fieldOptions);

				// If there are fields, we should trigger the apps to prepare them
				if (!empty($step->fields)) {
					$args = array(&$event);
					$fieldsLib->trigger('onDisplay', SOCIAL_FIELDS_GROUP_EVENT, $step->fields, $args);
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

				$step->url = ESR::groups(array('layout' => 'item', 'id' => $event->getAlias(), 'type' => 'info', 'infostep' => $index), false);

				if ($index === 1) {
					$step->url = FRoute::groups(array('layout' => 'item', 'id' => $event->getAlias(), 'type' => 'info'), false);
				}

				// Get the step title
				$step->title = JText::_($step->title);

				$step->active = !$step->hide && $activeStep == $index;

				$step->index = $index;

				$index++;
			}

			$items[$event->id] = $steps;
		}

		return $items[$event->id];
	}


	/**
	 * Retrieves the total number of event guests from a particular event
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getTotalAttendees($id)
	{
		$db  = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->where('a.cluster_id', $id);
		$sql->where('a.state', SOCIAL_EVENT_GUEST_GOING);

		$db->setQuery($sql);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Alias method of getGuests to ensure compatibility with Groups model.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getMembers($id, $options = array())
	{
		return $this->getGuests($id, $options);
	}


	/**
	 * Retrieves the total number of profiles in the system.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getTotalMembers($clusterId, $options = array())
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

		// setup query criteria
		$state = ($options && isset($options['state'])) ? $options['state'] : SOCIAL_STATE_PUBLISHED;
		$adminOnly = ($options && isset($options['admin'])) ? $options['admin'] : false;

		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);

		if ($adminOnly) {
			$sql->where('a.admin', SOCIAL_STATE_PUBLISHED);
		}

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count;
	}


	/**
	 * Retrieves a list of event guests from a particular event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getGuests($id, $options = array())
	{
		static $cache = array();

		$config = ES::config();

		$my = ES::user();

		ksort($options);

		$optionskey = serialize($options);

		if (!isset($cache[$id][$optionskey]) || (isset($options['reload']) && $options['reload'])) {
			$db = ES::db();

			$query = "select a.*";
			$query .= " from `#__social_clusters_nodes` as a";

			if (ES::isBlockEnabled()) {
				// user block
				// $query .= ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

				// $query .= ' ON (';
				// $query .= ' a.' . $db->nameQuote('uid') . ' = bus.' . $db->nameQuote('user_id');
				// $query .= ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
				// $query .= ') OR (';
				// $query .= ' a.' . $db->nameQuote('uid') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
				// $query .= ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
				// $query .= ')';

				$query .= ' ' . $this->getUserBlockingJoinQuery($my, 'bus', 'uid', 0, array('clusterColumnAlias' => 'cluster_id'));
			}

			$query .= " INNER JOIN `#__users` as u on a.uid = u.id";
			$query .= " INNER JOIN `#__social_profiles_maps` as upm on u.id = upm.user_id";
			$query .= " INNER JOIN `#__social_profiles` as up on upm.profile_id = up.id and up.community_access = 1";

			$query .= " WHERE a.`cluster_id` = " . $db->Quote($id);
			$query .= " AND u.`block` = 0";

			// to prevent duplicate records on users that are in pending state.
			$subquery = '(select max(nn.`id`) from `#__social_clusters_nodes` as nn where nn.`cluster_id` = ' . $db->Quote($id) . ' and nn.`uid` = a.`uid` and nn.`type` = a.`type` and nn.`state` = a.`state`)';
			$query .= " AND a.id = " . $subquery;

			$state = isset($options['state']) ? $options['state'] : false;

			if ($state !== false) {
				$query .= " and a.state = " . $db->Quote($state);
			}

			// Determine if we should retrieve admins only
			$adminOnly = isset($options['admin']) ? $options['admin'] : '';
			if ($adminOnly) {
				$query .= " and a.admin = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
			}

			// Determine if we want to exclude this.
			$exclude = isset($options['exclude']) ? $options['exclude'] : '';

			if ($exclude) {
				if (is_array($exclude)) {
					if (count($exlude) > 1) {
						$query .= " and a.uid NOT IN (" . $exclude . ")";

					} else {
						$query .= " and a.uid != " . $db->Quote($exclude[0]);
					}
				} else {
					$query .= " and a.uid != " . $db->Quote($exclude);
				}
			}

			$search = isset($options['search']) ? $options['search'] : '';

			if ($search) {
				$usernameType = $config->get('users.displayName');

				if ($usernameType == SOCIAL_FRIENDS_SEARCH_NAME || $usernameType == SOCIAL_FRIENDS_SEARCH_REALNAME) {
					$query .= " and u.name LIKE " . $db->Quote('%' . $search . '%');
				}

				if ($usernameType == SOCIAL_FRIENDS_SEARCH_USERNAME) {
					$query .= " and u.username LIKE " . $db->Quote('%' . $search . '%');
				}
			}

			if (ES::isBlockEnabled()) {
				// $query .= ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
				$query .= ' ' . $this->getUserBlockingClauseQuery($my);
			}

			$orderBy = '';
			if (isset($options['ordering'])) {
				$direction = isset($options['direction']) ? $options['direction'] : 'asc';
				$orderBy = ' order by ' . $options['ordering'] . ' ' . $direction;
			}

			$limit = isset($options['limit']) ? $options['limit'] : '';

			if ($limit) {
				// $limitstart = $this->getUserStateFromRequest('limitstart', 0)
				$app = JFactory::getApplication();
				$limitstart = $app->input->get('limitstart', 0, 'int');
				$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

				// Set the total records for pagination.
				$this->setTotal($query, true);

				$query .= $orderBy;
				$query .= " LIMIT " . $limitstart . ", " . $limit;

				$this->setState('limit', $limit);
				$this->setState('limitstart', $limitstart);
			} else {
				$query .= $orderBy;
			}

			// echo $query;
			// echo '<br><br>';
			// exit;

			$db->setQuery($query);

			$result = $db->loadObjectList();

			$cache[$id][$optionskey] = $result;
		}

		if (!empty($options['users'])) {
			$users = array();

			foreach ($cache[$id][$optionskey] as $row) {
				$user = ES::user($row->uid);

				$users[] = $user;
			}
		} else {
			$users = $this->bindTable('EventGuest', $cache[$id][$optionskey]);
		}

		return $users;
	}

	/**
	 * Get a list of events from a user
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUserEvents($userId, $limitstart = null, $limit = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.`cluster_id` from `#__social_clusters_nodes` as a';
		$query .= '	inner join `#__social_clusters` as b on a.`cluster_id` = b.`id`';
		$query .= '		and b.`cluster_type` = ' . $db->Quote(SOCIAL_TYPE_EVENT) . ' and b.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= ' where a.`uid` = ' . $db->Quote($userId);
		$query .= ' and a.`state` = ' . $db->Quote(SOCIAL_EVENTS_MEMBER_PUBLISHED);
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
	 * Generates a unique alias for the group
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getUniqueAlias($title, $exclude = null)
	{
		// Pass this back to Joomla to ensure that the permalink would be safe.
		$alias = ES::generatePermalink($title);

		$model = ES::model('Clusters');

		$i = 2;

		// Set this to a temporary alias
		$tmp = $alias;

		do {
			$exists = $model->clusterAliasExists($alias, $exclude, SOCIAL_TYPE_EVENT);

			if ($exists) {
				$alias  = $tmp . '-' . $i++;
			}

		} while ($exists);

		return $alias;
	}

	/**
	 * Creates a new event based on the session.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function createEvent(SocialTableStepSession $session)
	{
		ES::import('admin:/includes/event/event');

		$my = ES::user();

		// Create an event object
		$event = new SocialEvent();
		$event->creator_uid = $my->id;
		$event->creator_type = SOCIAL_TYPE_USER;
		$event->category_id = $session->uid;
		$event->cluster_type = SOCIAL_TYPE_EVENT;
		$event->created = ES::date()->toSql();
		$event->key = md5(JFactory::getDate()->toSql() . $my->password . uniqid());

		$params = ES::registry($session->values);

		$category = ES::table('EventCategory');
		$category->load($session->uid);

		// Support for group event
		if ($params->exists('group_id')) {
			$group = ES::group($params->get('group_id'));
			$event->setMeta('group_id', $group->id);
		} else {
			$event->setMeta('group_id', 0);
		}

		// Support for page event
		if ($params->exists('page_id')) {
			$page = ES::page($params->get('page_id'));
			$event->setMeta('page_id', $page->id);
		} else {
			$event->setMeta('page_id', 0);
		}

		$data = $params->toArray();

		// For recurring event
		// Check if there is recurring flag in session->values
		if (isset($data['parent_id'])) {
			$event->parent_id = $data['parent_id'];
			$event->parent_type = SOCIAL_TYPE_EVENT;
		}

		// Get the custom fields for this event
		$customFields = ES::model('Fields')->getCustomFields(array('visible' => SOCIAL_EVENT_VIEW_REGISTRATION, 'group' => SOCIAL_TYPE_EVENT, 'workflow_id' => $category->getWorkflow()->id));

		$fieldsLib = ES::fields();

		$args = array(&$data, &$event);

		$callback = array($fieldsLib->getHandler(), 'beforeSave');

		$errors = $fieldsLib->trigger('onRegisterBeforeSave', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args, $callback);

		if (!empty($errors)) {
			$this->setError($errors);
			return false;
		}

		// Default to pending state
		$event->state = SOCIAL_CLUSTER_PENDING;

		// If the event is created by site admin or user doesn't need to be moderated, publish event immediately.
		if ($my->isSiteAdmin() || !$my->getAccess()->get('events.moderate')) {
			$event->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		// Trigger apps
		ES::apps()->load(SOCIAL_TYPE_USER);

		// Trigger events
		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$event, &$my, true);

		// @trigger: onEventBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventBeforeSave', $triggerArgs);

		// If the event alias is still empty at this point, there is instance where the permalink field isn't enabled.
		// This also means that all custom fields app must set the alias before saving.
		if (!$event->alias) {
			$event->alias = $this->getUniqueAlias($event->getName());
		}

		// Save the event
		$state = $event->save();

		if (!$state) {
			$this->setError($event->getError());
			return false;
		}

		// Notifies admin when a new event is created
		if ($event->state === SOCIAL_CLUSTER_PENDING || !$my->isSiteAdmin()) {
			$this->notifyAdmins($event);
		}

		// Recreate the event object
		SocialEvent::$instances[$event->id] = null;
		$event = ES::event($event->id);

		// Create a new owner object
		$event->createOwner($my->id);

		// Check for transfer flag to insert group member as event guest
		$transferMode = isset($data['member_transfer']) ? $data['member_transfer'] : 'invite';

		// Support for group event
		if ($event->isGroupEvent()) {
			$this->transferGroupMembers($event, $session, $transferMode);
		}

		// Support for page event
		if ($event->isPageEvent()) {
			$this->transferPageMembers($event, $session, $transferMode);
		}

		// Trigger the fields again
		$args = array(&$data, &$event);

		$fieldsLib->trigger('onRegisterAfterSave', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args);

		$event->bindCustomFields($data);

		$fieldsLib->trigger('onRegisterAfterSaveFields', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args);

		// @trigger: onEventAfterSave
		$triggerArgs = array(&$event, &$my, true);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterSave', $triggerArgs);

		return $event;
	}

	/**
	 * Transfer page likers to be the guest
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function transferPageMembers($event, $session, $transferMode)
	{
		$my = ES::user();

		if (!empty($transferMode) && $transferMode != 'none') {

			$nodeState = SOCIAL_EVENT_GUEST_INVITED;

			if ($transferMode == 'attend') {
				$nodeState = SOCIAL_EVENT_GUEST_GOING;
			}

			$eventId = $event->id;
			$pageId = $event->getMeta('page_id');
			$userId = $my->id;
			$now = ES::date()->toSql();

			//check this event is it create from page
			if ($session) {
				$reg = ES::registry();
				$reg->load($session->values);
				$pageId = $reg->get('page_id');
			}

			$model = ES::model('Pages');
			$options = [
				'exclude' => $my->id,
				'state' => SOCIAL_PAGES_MEMBER_PUBLISHED
			];

			// Notify invited or going users
			$targets = $model->getMembers($pageId, $options);

			// the node state is going mean the member transfer mode is attend
			if (!empty($targets) && $nodeState === SOCIAL_EVENT_GUEST_GOING) {
				// Assign point to the attendee
				foreach ($targets as $target) {
					ES::points()->assign('events.going', 'com_easysocial', $target->id);
				}
			}

			if ($transferMode == 'invite') {

				if (!empty($pageId)) {

					// If the permalink alias is empty, we need to get default alias
					if (empty($event->alias)) {
						$event->alias = $this->getUniqueAlias($event->getName());
						$event->save();
					}

					if (!empty($targets) && $event->state == SOCIAL_CLUSTER_PUBLISHED) {
						$emailOptions = (object) array(
							'title' => 'COM_EASYSOCIAL_EMAILS_EVENT_GUEST_INVITED_SUBJECT',
							'template' => 'site/event/guest.invited',
							'event' => $event->getName(),
							'eventName' => $event->getName(),
							'eventAvatar' => $event->getAvatar(),
							'eventLink' => $event->getPermalink(false, true),
							'invitorName' => $my->getName(),
							'invitorLink' => $my->getPermalink(false, true),
							'invitorAvatar' => $my->getAvatar()
						);

						$systemOptions = (object) array(
							'uid' => $event->id,
							'actor_id' => $my->id,
							'target_id' => $event->id,
							'context_type' => 'events',
							'type' => 'events',
							'url' => $event->getPermalink(true, false, 'item', false),
							'eventId' => $event->id
						);

						ES::notify('events.guest.invited', $targets, $emailOptions, $systemOptions);
					}
				}
			}

			$db = ES::db();
			$sql = $db->sql();

			$query = "INSERT INTO `#__social_clusters_nodes` (`cluster_id`, `uid`, `type`, `created`, `state`, `owner`, `admin`, `invited_by`)";
			$query .= " SELECT '$eventId' AS `cluster_id`, a.`uid`, `type`, '$now' AS `created`, '$nodeState' AS `state`, '0' AS `owner`, a.`admin`, '$userId' AS `invited_by`";
			$query .= " FROM `#__social_clusters_nodes` as a";

			//exclude esad users
			$query .= " INNER JOIN `#__social_profiles_maps` as upm on a.`uid` = upm.`user_id`";
			$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";

			$query .= " WHERE a.`cluster_id` = '$pageId' AND a.`state` = " . $db->Quote(SOCIAL_PAGES_MEMBER_PUBLISHED);
			$query .= " AND a.`type` = " . $db->Quote(SOCIAL_TYPE_USER);
			$query .= " AND a.`uid` NOT IN (SELECT b.`uid` FROM `#__social_clusters_nodes` as b WHERE b.`cluster_id` = '$eventId' AND b.`type` = '" . SOCIAL_TYPE_USER . "')";

			$sql->raw($query);
			$db->setQuery($sql);
			$db->query();
		}
	}

	/**
	 * Transfer group members to be the guest
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function transferGroupMembers($event, $session, $transferMode)
	{
		$my = ES::user();

		if (!empty($transferMode) && $transferMode != 'none') {

			$nodeState = SOCIAL_EVENT_GUEST_INVITED;

			if ($transferMode == 'attend') {
				$nodeState = SOCIAL_EVENT_GUEST_GOING;
			}

			/*

			insert into jos_social_clusters_nodes (cluster_id, uid, type, created, state, owner, admin, invited_by)
			select $eventId as cluster_id, uid, type, $now as created, $nodeState as state, 0 as owner, admin, $userId as invited_by from jos_social_clusters_nodes
			where cluster_id = $groupId
			and state = 1
			and type = 'user'
			and uid not in (select uid from jos_social_clusters_nodes where cluster_id = $eventId and type = 'user')

			*/

			$eventId = $event->id;
			$groupId = $event->getMeta('group_id');
			$userId = $my->id;
			$now = ES::date()->toSql();

			// check this event is it create from group
			if ($session) {
				$reg = ES::registry();
				$reg->load($session->values);
				$groupId = $reg->get('group_id');
			}

			$model = ES::model('Groups');
			$options = [
				'exclude' => $my->id,
				'state' => SOCIAL_GROUPS_MEMBER_PUBLISHED
			];

			// Notify invited or going users
			$targets = $model->getMembers($groupId, $options);

			// the node state is going mean the member transfer mode is attend
			if (!empty($targets) && $nodeState === SOCIAL_EVENT_GUEST_GOING) {
				// Assign point to the attendee
				foreach ($targets as $target) {
					ES::points()->assign('events.going', 'com_easysocial', $target->id);
				}
			}

			if ($transferMode == 'invite') {

				if (!empty($groupId)) {

					// If the permalink alias is empty, we need to get default alias
					if (empty($event->alias)) {
						$event->alias = $this->getUniqueAlias($event->getName());
						$event->save();
					}

					if (!empty($targets) && $event->state == SOCIAL_CLUSTER_PUBLISHED) {
						$emailOptions = (object) array(
							'title' => 'COM_EASYSOCIAL_EMAILS_EVENT_GUEST_INVITED_SUBJECT',
							'template' => 'site/event/guest.invited',
							'event' => $event->getName(),
							'eventName' => $event->getName(),
							'eventAvatar' => $event->getAvatar(),
							'eventLink' => $event->getPermalink(false, true),
							'invitorName' => $my->getName(),
							'invitorLink' => $my->getPermalink(false, true),
							'invitorAvatar' => $my->getAvatar()
						);

						$systemOptions = (object) array(
							'uid' => $event->id,
							'actor_id' => $my->id,
							'target_id' => $event->id,
							'context_type' => 'events',
							'type' => 'events',
							'url' => $event->getPermalink(true, false, 'item', false),
							'eventId' => $event->id
						);

						ES::notify('events.guest.invited', $targets, $emailOptions, $systemOptions);
					}
				}
			}

			$db = ES::db();
			$sql = $db->sql();

			$query = "INSERT INTO `#__social_clusters_nodes` (`cluster_id`, `uid`, `type`, `created`, `state`, `owner`, `admin`, `invited_by`)";
			$query .= " SELECT '$eventId' AS `cluster_id`, a.`uid`, `type`, '$now' AS `created`, '$nodeState' AS `state`, '0' AS `owner`, a.`admin`, '$userId' AS `invited_by`";
			$query .= " FROM `#__social_clusters_nodes` as a";

			//exclude esad users
			$query .= " INNER JOIN `#__social_profiles_maps` as upm on a.`uid` = upm.`user_id`";
			$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1";

			$query .= " WHERE a.`cluster_id` = '$groupId' AND a.`state` = " . $db->Quote(SOCIAL_GROUPS_MEMBER_PUBLISHED);
			$query .= " AND a.`type` = " . $db->Quote(SOCIAL_TYPE_USER);
			$query .= " AND a.`uid` NOT IN (SELECT b.`uid` FROM `#__social_clusters_nodes` as b WHERE b.`cluster_id` = '$eventId' AND b.`type` = '" . SOCIAL_TYPE_USER . "')";

			$sql->raw($query);
			$db->setQuery($sql);
			$db->query();
		}
	}

	/**
	 * Notifies administrator when a new event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function notifyAdmins($event, $edited = false)
	{
		$config = ES::config();

		if (!$config->get('notifications.email.enabled')) {
			return;
		}

		$params = array(
			'title' => $event->getName(),
			'creatorName' => $event->getCreator()->getName(),
			'creatorLink' => $event->getCreator()->getPermalink(false, true),
			'categoryTitle' => $event->getCategory()->get('title'),
			'avatar' => $event->getAvatar(SOCIAL_AVATAR_LARGE),
			'permalink' => $event->getPermalink(true, true),
			'alerts' => false
		);

		$params['type'] = $edited ? 'EDITED' : 'CREATED';

		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_MODERATE_EVENT_' . $params['type'] . '_TITLE', $event->getName());

		$template = 'site/event/created';

		if ($event->state === SOCIAL_CLUSTER_PENDING || $event->state === SOCIAL_CLUSTER_UPDATE_PENDING) {
			$params['reject'] = FRoute::controller('events', array('external' => true, 'task' => 'rejectEvent', 'id' => $event->id, 'key' => $event->key));
			$params['approve'] = FRoute::controller('events', array('external' => true, 'task' => 'approveEvent', 'id' => $event->id, 'key' => $event->key));

			$template = 'site/event/moderate';
		}

		$admins = ES::model('Users')->getSystemEmailReceiver();

		foreach ($admins as $admin) {

			$mailer = ES::mailer();

			$params['adminName'] = $admin->name;

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($admin->name, $admin->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the template
			$mailTemplate->setTemplate($template, $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}
	}

	/**
	 * Generates filter options for models
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateFilterOptions($filter, &$options = [], $userId = null)
	{
		$user = ES::user($userId);

		// Filtering by past events
		if ($filter === 'past') {
			$options['ordering'] = 'recent';
			$options['direction'] = 'desc';
			$options['past'] = true;

			// For past events, these needs to be off
			$options['ongoing'] = false;
			$options['upcoming'] = false;
		}

		// Filtering by featured events
		if ($filter === 'featured') {
			$options['featured'] = true;
		}

		// Filter events by current logged in user as creator
		if ($filter === 'mine') {
			$options['creator_uid'] = $user->id;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['creator_join'] = true;
			$options['type'] = 'all';
			$options['featured'] = 'all';
		}

		if ($filter === 'createdbyme') {
			$options['creator_uid'] = $user->id;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['type'] = 'all';
			$options['featured'] = 'all';
		}

		// events that pending user's review.
		if ($filter == 'review') {
			$options['creator_uid'] = $user->id;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['state'] = SOCIAL_CLUSTER_DRAFT;
		}

		// Filter by invited
		if ($filter === 'invited') {
			$options['gueststate'] = SOCIAL_EVENT_GUEST_INVITED;
			$options['guestuid'] = $user->id;
			$options['type'] = 'all';
		}

		// Filter by going
		if ($filter === 'going') {
			$options['gueststate'] = SOCIAL_EVENT_GUEST_GOING;
			$options['guestuid'] = $user->id;
			$options['type'] = 'all';
		}

		// Filter by pending events
		if ($filter === 'pending') {
			$options['gueststate'] = SOCIAL_EVENT_GUEST_PENDING;
			$options['guestuid'] = $user->id;
			$options['type'] = 'all';
		}

		// Filter by maybe state
		if ($filter === 'maybe') {
			$options['gueststate'] = SOCIAL_EVENT_GUEST_MAYBE;
			$options['guestuid'] = $user->id;
			$options['type'] = 'all';
		}

		// Filter events that the user is not attending
		if ($filter === 'notgoing') {
			$options['gueststate'] = SOCIAL_EVENT_GUEST_NOTGOING;
			$options['guestuid'] = $user->id;
			$options['type'] = 'all';
		}

		if ($filter == 'unpublished') {
			$options['state'] = SOCIAL_STATE_UNPUBLISHED;
			$options['ongoing'] = false;
			$options['upcoming'] = false;

			// Only load the unpublished owner event
			if (!$user->isSiteAdmin()) {
				$options['creator_uid'] = $user->id;
			}
		}

		return $options;
	}

	public function getFriendsInEvent($eventId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$userId = isset($options['userId']) ? $options['userId'] : ES::user()->id;

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('a.uid', 'uid', 'distinct');

		$showNonFriend = $config->get('events.invite.nonfriends');

		// if friends system disabled. we will exclude events members instead of friends.
		if ($config->get('friends.enabled') && (!$showNonFriend || (isset($options['fromWidget']) && $options['fromWidget']))) {
			$sql->innerjoin('#__social_friends', 'b');
			$sql->on('(');
			$sql->on('(');
			$sql->on('a.uid', 'b.actor_id');
			$sql->on('b.target_id', $userId);
			$sql->on(')');
			$sql->on('(', '', '', 'OR');
			$sql->on('a.uid', 'b.target_id');
			$sql->on('b.actor_id', $userId);
			$sql->on(')');
			$sql->on(')');
			$sql->on('b.state', SOCIAL_STATE_PUBLISHED);
		}

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('a.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->where('a.cluster_id', $eventId);

		if (isset($options['published'])) {
			$sql->where('(', '', '', 'AND');
			$sql->where('a.state', $options['published'], '=');

			if (isset($options['invited']) && $options['invited']) {
				$sql->where('a.state', SOCIAL_EVENT_GUEST_INVITED, '=', 'OR');
			}

			$sql->where(')');
		}

		$db->setQuery($sql);
		$result = $db->loadColumn();

		$users = array();

		foreach ($result as $id) {
			$users[] = ES::user($id);
		}

		return $users;
	}

	public function getOnlineGuests($eventId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the session life time so we can know who is really online.
		$lifespan = ES::jConfig()->getValue('lifetime');
		$online = time() - ($lifespan * 60);

		$sql->select('#__session', 'a');
		$sql->column('b.id');
		$sql->innerjoin('#__users', 'b');
		$sql->on('a.userid', 'b.id');
		$sql->innerjoin('#__social_clusters_nodes', 'c');
		$sql->on('c.uid', 'b.id');
		$sql->on('c.type', SOCIAL_TYPE_USER);

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('c.uid', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		$sql->where('a.time', $online, '>=');
		$sql->where('b.block', 0);
		$sql->where('c.cluster_id', $eventId);
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
	 * Get the recurring events' id
	 *
	 * @since  3.2.9
	 * @access public
	 */
	public function getRecurringEventsId($parentId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->column('id');
		$sql->where('cluster_type', SOCIAL_TYPE_EVENT);
		$sql->where('parent_id', $parentId);

		$db->setQuery($sql);

		$result = $db->loadColumn();

		$ids = array();

		foreach ($result as $id) {
			$ids[] = $db->quote($id);
		}

		if (empty($ids)) {
			return false;
		}

		$ids = implode(',', $ids);

		return $ids;
	}

	/**
	 * Deletes events entry from the calendar
	 *
	 * @since  3.2.9
	 * @access public
	 */
	public function deleteFromCalendar($parentId, $mode)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'DELETE FROM ' . $db->nameQuote('#__social_apps_calendar');

		if ($mode == 'all') {
			$recurringEventsIds = $this->getRecurringEventsId($parentId);
			$ids = $parentId;

			if ($recurringEventsIds) {
				$ids = $ids . ',' . $recurringEventsIds;
			}

			$query[] = 'WHERE ' . $db->nameQuote('uid') . ' IN (' . $ids . ')';
		} else {
			$query[] = 'WHERE ' . $db->nameQuote('uid') . ' = ' . $db->quote($parentId);
		}

		$query[] = 'AND ' . $db->nameQuote('type') . ' = ' . $db->quote(SOCIAL_TYPE_EVENT);
		$query = implode(' ', $query);

		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();
	}

	/**
	 * Deletes all the child events.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function deleteRecurringEvents($parentId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$ids = $this->getRecurringEventsId($parentId);

		if (!$ids) {
			return true;
		}

		$sql->clear();

		// delete the recurring event SEF URL during edit parent event
		$recurringEventsIds = str_replace("'", "", $ids);
		$recurringEventsIds = explode(',', $recurringEventsIds);

		if ($recurringEventsIds) {
			$event = ES::table('Event');

			foreach ($recurringEventsIds as $recurringEventsId) {
				$event->load($recurringEventsId);
				$event->deleteSEFCache();
			}
		}

		// Delete stream items
		$query = "DELETE `a`, `b` FROM `#__social_stream_item` AS `a` INNER JOIN `#__social_stream` AS `b` ON `a`.`uid` = `b`.`id` WHERE `b`.`cluster_id` IN ($ids)";

		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		// Delete notification items
		$query = "DELETE FROM `#__social_notifications` WHERE (`uid` IN ($ids) AND `type` = 'event') OR (type = 'event' AND `context_ids` IN ($ids))";
		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		// Delete avatar
		$query = "DELETE FROM `#__social_avatars` WHERE `uid` IN ($ids) AND `type` = 'event'";
		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		// Delete cover
		$query = "DELETE FROM `#__social_covers` WHERE `uid` IN ($ids) AND `type` = 'event'";
		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		// Delete albums
		$query = "DELETE FROM `#__social_albums` WHERE `uid` IN ($ids) AND `type` = 'event'";
		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		// Delete photos, metas, and tags
		$query = "DELETE `a`, `b`, `c` FROM `#__social_photos` AS `a`";
		$query .= " LEFT JOIN `#__social_photos_meta` AS `b` ON `a`.`id` = `b`.`photo_id`";
		$query .= " LEFT JOIN `#__social_photos_tag` AS `c` ON `a`.`id` = `c`.`photo_id`";
		$query .= " WHERE `a`.`type` = 'event' AND `a`.`uid` IN ($ids)";

		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		// Delete event item
		// Delete meta
		// Delete nodes
		// Delete news items
		$query = "DELETE `a`, `b`, `c`, `d` FROM `#__social_clusters` AS `a`";
		$query .= " LEFT JOIN `#__social_clusters_nodes` AS `b` ON `a`.`id` = `b`.`cluster_id`";
		$query .= " LEFT JOIN `#__social_clusters_news` AS `c` ON `a`.`id` = `c`.`cluster_id`";
		$query .= " LEFT JOIN `#__social_events_meta` AS `d` ON `a`.`id` = `d`.`cluster_id`";
		$query .= " WHERE `a`.`parent_id` = $parentId";

		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		// Delete from calendar module
		$query = "DELETE FROM `#__social_apps_calendar` WHERE `uid` IN ($ids) AND `type` = 'event'";
		$sql->raw($query);

		$db->setQuery($sql);

		$db->query();

		$sql->clear();

		return true;
	}

	public function duplicateGuests($sourceId, $targetId)
	{
		$now = ES::date()->toSql();

		$db = ES::db();
		$sql = $db->sql();
		$sql->raw("INSERT INTO `#__social_clusters_nodes` (`cluster_id`, `uid`, `type`, `created`, `state`, `owner`, `admin`, `invited_by`) SELECT '$targetId' AS `cluster_id`, `uid`, `type`, '$now' AS `created`, `state`, `owner`, `admin`, `invited_by` FROM `#__social_clusters_nodes` WHERE `cluster_id` = '$sourceId' AND `uid` NOT IN (SELECT `uid` FROM `#__social_clusters_nodes` WHERE `cluster_id` = '$targetId')");

		$db->setQuery($sql);
		$db->query();
	}

	public function getRecurringSchedule($options = array())
	{
		// Options
		// eventStart = SocialDate
		// end = string
		// type = string
		// daily = array

		$eventStart = $options['eventStart'];

		$startUnix = $eventStart->toUnix();

		// Get the recur end
		$recurringEnd = ES::date($options['end'], false);
		// We plus 1 day ahead so that the day of recur end is also considered
		$recurringEndUnix = $recurringEnd->toUnix() + (60*60*24);

		// This stores all the start and end of the recurring events for event creation
		$schedule = array();

		// Based on the type, we calculate and prepare all the schedule
		if ($options['type'] === 'daily') {
			if (empty($options['daily'])) {
				return $schedule;
			}

			$recur = $options['daily'];
			$countRecur = count($recur);

			// Build a recur cycle array
			$cycle = array();

			// Calculate the total interval to move from day to day
			for ($i = 0; $i < $countRecur; $i++) {
				// $j is the next element in the recur array
				$j = $i === ($countRecur - 1) ? 0 : $i + 1;

				$cycle[] = ($recur[$i] < $recur[$j] ? $recur[$j] - $recur[$i] : 7 + $recur[$j] - $recur[$i]) * 60*60*24;
			}

			// Get today as integer
			// 0, 1, 2, 3, 4, 5, 6, with 0 being Sunday
			$startDay = $eventStart->format('w');

			// Calculate the next nearest day from $startDay
			// Get the first recur day
			$first = $recur[0];

			// Get the last recur day
			$last = $recur[$countRecur - 1];

			// Set the next possible day as the first recur day
			$next = $first;

			// Next possible day is always $first unless:
			// $countRecur > 1
			// $startDay >= $first
			// $startDay < $last  // this only applicable if the startoftheweek is sunday.
			// $lastDay == 0 // means sunday (0) being at the end of the week.
			if ($countRecur > 1 && $startDay >= $first && ($startDay < $last || $last == 0)) {
				// As long as $startDay is < than the recur day, then that recur day is our next possible day
				foreach ($recur as $r) {
					if ($startDay < $r) {
						$next = $r;
						break;
					}
				}

				// Now that $next is no longer $first, we have to reorganize the cycle array
				$offset = array_search($next, $recur);
				$spliced = array_splice($cycle, $offset);
				$cycle = array_merge($spliced, $cycle);
			}

			// Now that we have the correct next day, we need to get the interval between $startDay and $next
			$intervalToNext = ($startDay < $next ? $next - $startDay : 7 - $startDay + $next) * 60*60*24;

			// Now we shift the startUnix to the next possible day
			$startUnix += $intervalToNext;

			$counter = 0;

			do {
				// Store this data in the schedule
				if ($startUnix < $recurringEndUnix) {
					$schedule[] = $startUnix;
				}

				// Get the next recur start
				$startUnix += $cycle[$counter % $countRecur];

				$counter++;
			} while ($startUnix < $recurringEndUnix);
		}

		if ($options['type'] === 'weekly') {
			do {
				$startUnix += 60*60*24*7;

				// Store this data in the schedule
				if ($startUnix < $recurringEndUnix) {
					$schedule[] = $startUnix;
				}
			} while ($startUnix < $recurringEndUnix);
		}

		// If is monthly, this gets a bit tricky
		// Instead of adding the unit, we alter the date by 1 month
		// Then check if the day is valid on that month or not
		// If it is not valid, then fallback to the month's max day
		if ($options['type'] === 'monthly') {
			$year = $eventStart->format('Y');
			$month = $eventStart->format('n');
			$day = $eventStart->format('d');

			$time = $eventStart->format('H:i:s');

			$nextYear = $year;

			$nextMonth = $month;

			$nextDay = $day;

			do {
				$nextMonth += 1;

				if ($nextMonth > 12) {
					$nextMonth = 1;
					$nextYear += 1;
				}

				$maxDay = ES::date($nextYear . '-' . $nextMonth . '-01')->format('t');

				$nextDay = min($day, $maxDay);

				$startUnix = ES::date($nextYear . '-' . $nextMonth . '-' . $nextDay . ' ' . $time)->toUnix();

				// Store this data in the schedule
				if ($startUnix < $recurringEndUnix) {
					$schedule[] = $startUnix;
				}
			} while ($startUnix < $recurringEndUnix);
		}

		// If it is yearly, we also need to perform a month check due to Feb end date changes
		if ($options['type'] === 'yearly') {
			$year = $eventStart->format('Y');
			$month = $eventStart->format('n');
			$day = $eventStart->format('d');

			$time = $eventStart->format('H:i:s');

			$nextYear = $year;

			$nextDay = $day;

			do {
				$nextYear += 1;

				// We only need to do this check if it is Feb
				if ($month == 2) {
					$maxDay = ES::date($nextYear . '-' . $month . '-01')->format('t');

					$nextDay = min($day, $maxDay);
				}

				$startUnix = ES::date($nextYear . '-' . $month . '-' . $nextDay . ' ' . $time)->toUnix();

				// Store this data in the schedule
				if ($startUnix < $recurringEndUnix) {
					$schedule[] = $startUnix;
				}
			} while ($startUnix < $recurringEndUnix);
		}

		return $schedule;
	}

	/**
	 * Creates a new recurring event based on the post data and parent event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function createRecurringEvent($data, $parent, $isEdit = false)
	{
		ES::import('admin:/includes/event/event');

		$event = new SocialEvent();
		$event->creator_uid = $parent->creator_uid;
		$event->creator_type = SOCIAL_TYPE_USER;
		$event->category_id = $parent->category_id;
		$event->cluster_type = SOCIAL_TYPE_EVENT;
		$event->created = ES::date()->toSql();
		$event->parent_id = $parent->id;
		$event->parent_type = SOCIAL_TYPE_EVENT;
		$event->state = SOCIAL_CLUSTER_PUBLISHED;

		$event->key = md5(JFactory::getDate()->toSql() . ES::user()->password . uniqid());

		// Support for group event
		if (isset($data['group_id'])) {
			$cluster = ES::group($data['group_id']);

			$event->setMeta('group_id', $cluster->id);
		}

		 // Support for page event
		if (isset($data['page_id'])) {
			$cluster = ES::page($data['page_id']);

			$event->setMeta('page_id', $cluster->id);
		}

		$category = ES::table('EventCategory');
		$category->load($parent->category_id);

		$customFields = ES::model('Fields')->getCustomFields(array('visible' => SOCIAL_EVENT_VIEW_REGISTRATION, 'group' => SOCIAL_TYPE_EVENT, 'workflow_id' => $category->getWorkflow()->id));

		$fieldsLib = ES::fields();

		$args = array(&$data, &$event);

		$fieldsLib->trigger('onRegisterBeforeSave', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args, array($fieldsLib->getHandler(), 'beforeSave'));

		$state = $event->save();

		// Recreate the event object
		SocialEvent::$instances[$event->id] = null;
		$event = ES::event($event->id);

		// Support for cluster event
		if ($event->isClusterEvent()) {
			// Check for transfer flag to insert cluster member as event guest
			$transferMode = isset($data['member_transfer']) ? $data['member_transfer'] : 'invite';

			// Proceed this except editing event since this membertransfer custom field only rendered on new creation form
			if (!empty($transferMode) && $transferMode != 'none' && !$isEdit) {
				$nodeState = SOCIAL_EVENT_GUEST_INVITED;

				if ($transferMode == 'attend') {
					$nodeState = SOCIAL_EVENT_GUEST_GOING;
				}

				$eventId = $event->id;

				// Here we know already this is a cluster event
				$clusterId = $cluster->id;
				$userId = $parent->creator_uid;
				$now = ES::date()->toSql();

				$query = "INSERT INTO `#__social_clusters_nodes` (`cluster_id`, `uid`, `type`, `created`, `state`, `owner`, `admin`, `invited_by`) SELECT '$eventId' AS `cluster_id`, `uid`, `type`, '$now' AS `created`, '$nodeState' AS `state`, '0' AS `owner`, `admin`, '$userId' AS `invited_by` FROM `#__social_clusters_nodes` WHERE `cluster_id` = '$clusterId' AND `state` = '" . SOCIAL_GROUPS_MEMBER_PUBLISHED . "' AND `type` = '" . SOCIAL_TYPE_USER . "' AND `uid` NOT IN (SELECT `uid` FROM `#__social_clusters_nodes` WHERE `cluster_id` = '$eventId' AND `type` = '" . SOCIAL_TYPE_USER . "') AND `uid` != '$userId'";

				$db = ES::db();
				$sql = $db->sql();
				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();
			}
		}

		// Trigger the fields again
		$args = array(&$data, &$event);

		$fieldsLib->trigger('onRegisterAfterSave', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args);

		$event->bindCustomFields($data);

		$fieldsLib->trigger('onRegisterAfterSaveFields', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args);

		if (empty($event->alias)) {
			$event->alias = $this->getUniqueAlias($event->getName());

			$event->save();
		}

		$dispatcher  = ES::dispatcher();
		$my = ES::user();

		// @trigger: onEventAfterSave
		// Put the recurring events in the calendar
		$triggerArgs = array(&$event, &$my, true);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterSave', $triggerArgs);

		return $event;
	}

	/**
	 * Retrieves a list of upcoming events
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function getUpcomingReminder()
	{
		$db = ES::db();
		$sql = $db->sql();

		$now    = ES::date();

		$query = "select a.`cluster_id` as `event_id`, a.`start`, a.`end`, a.`all_day`, a.`start_gmt`, a.`end_gmt`, c.`uid` as `user_id`";
		$query .= ", b.`title`, b.`alias`, b.`description`, b.`address`";
		$query .= ", u.`name` as `user_name`, u.`email` as `user_email`";
		$query .= " from `#__social_events_meta` as a";
		$query .= " inner join `#__social_clusters` as b on a.cluster_id = b.id";
		$query .= " inner join `#__social_clusters_nodes` as c on a.cluster_id = c.cluster_id";
		$query .= " inner join `#__users` as u on c.`uid` = u.`id`";
		$query .= " where b.state = 1";
		$query .= " and c.`type` = 'user'";
		$query .= " and c.`state` = 1";
		$query .= " and c.`reminder_sent` = 0";
		$query .= " and a.`reminder` > 0";
		$query .= " and a.`start_gmt` <= date_add(" . $db->Quote($now->toMySQL()) . ", INTERVAL a.`reminder` DAY)";


		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		// we need to group the events by users
		$items = array();

		if ($results) {

			$events = array();
			$users = array();

			foreach($results as $item) {

				if (! isset($events[$item->event_id])) {

					$event = new stdClass();

					$alias = $item->event_id . ':' . JFilterOutput::stringURLSafe($item->alias);

					$event->id = $item->event_id;
					$event->title = $item->title;
					$event->permalik = $alias;
					$event->description = $item->description;
					$event->address = $item->address;
					$event->start = $item->start;
					$event->end = $item->end;
					$event->start_gmt = $item->start_gmt;
					$event->end_gmt = $item->end_gmt;
					$event->all_day = $item->all_day;

					$events[$item->event_id] = $event;
				}

				if (! isset($users[$item->user_id])) {
					$user = new stdClass();
					$user->id = $item->user_id;
					$user->name = $item->user_name;
					$user->email = $item->user_email;

					$users[$item->user_id] = $user;
				}

				$items[$item->user_id]['user'] = $users[$item->user_id];
				$items[$item->user_id]['events'][] = $events[$item->event_id];
			}
		}

		return $items;
	}

	/**
	 * Send reminders
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function sendUpcomingReminder($items)
	{
		$count = 0;
		$jConfig = ES::jConfig();
		$config = ES::config();

		if ($items) {

			$siteName = $jConfig->getValue('sitename');
			$loginLink = ESR::events(array(), false);

			foreach ($items as $data)  {

				$user = $data['user'];
				$events = $data['events'];

				$ids = array();

				foreach ($events as $event) {
					$ids[] = $event->id;
				}

				$theme = ES::themes();
				$theme->set('events', $events);
				$eventHtml = $theme->output('site/emails/event/upcoming.reminder.event');

				// Notify the person that they are now a group admin
				$emailOptions = array(
									'title' => JText::sprintf('COM_EASYSOCIAL_EMAILS_UPCOMING_EVENT_REMINDER_SUBJECT', $user->name),
									'template' => 'site/event/upcoming.reminder',
									'siteName' => $siteName,
									'loginLink' => $loginLink,
									'recipientName' => $user->name,
									'events' => $eventHtml,
									'eventCount' => count($events)
								);

				$systemOptions = array(
									'context_type' => 'events.reminder',
									'total' => count($events),
									'events' => json_encode($ids)
								);


				ES::notify('events.reminder', array($user->id), $emailOptions, $systemOptions);

				// Mark them as sent as we do not want to generate duplicate notifications
				$this->updateReminderSentFlag($user->id, $ids, 1);

				$count++;
			}
		}

		return $count;
	}

	public function updateReminderSentFlag($userId, $eventIds, $flag)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'update `#__social_clusters_nodes` set `reminder_sent` = ' . $db->Quote($flag);
		$query .= ' where `type` = ' . $db->Quote('user');
		$query .= ' and `uid` = ' . $db->Quote($userId);
		if (count($eventIds) > 1) {
			$query .= ' and cluster_id IN (' . implode(',', $eventIds) . ')';
		} else {
			$query .= ' and cluster_id = ' . $eventIds[0];
		}

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Remove deleted user stream item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteUserStreams($eventId, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "delete a, b from `#__social_stream` as a";
		$query .= "     inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
		$query .= " where a.`actor_id` = '$userId'";
		$query .= " and a.`cluster_id` = '$eventId'";
		$query .= " and a.`cluster_type` = '" . SOCIAL_TYPE_EVENT . "'";

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}

	/**
	 * Determines if an email exists in this group
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isEmailExists($email, $eventId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_nodes', 'a');
		$sql->column('COUNT(1)');
		$sql->join('#__users', 'b');
		$sql->on('a.uid', 'b.id');
		$sql->where('b.email', $email);
		$sql->where('a.type', SOCIAL_TYPE_USER);
		$sql->where('a.cluster_id', $eventId);
		$sql->where('a.state', SOCIAL_EVENTS_MEMBER_PUBLISHED);

		$db->setQuery($sql);

		$exists = $db->loadResult() > 0;

		return $exists;
	}

	/**
	 * Returns past events
	 *
	 * @since   3.2.16
	 * @access  public
	 */
	public function getPastEvents($limit = 10)
	{
		$now = ES::date();

		$options = array(
			'type' => 'all',
			'start-before' => $now->toSql(),
			'limit' => $limit,
			'past' => true,
			'ongoing' => false,
			'upcoming' => false
		);

		$events = $this->getEvents($options);

		return $events;
	}

	/**
	 * Get event that need to be unfeatured
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getEventToUnfeatured($pastEventOnly = true)
	{
		$db = ES::db();
		$now = ES::date()->toSql(true);

		$query = array();

		$query[] = "SELECT DISTINCT `a`.`id` FROM `#__social_clusters` AS `a`";
		$query[] = "LEFT JOIN `#__social_events_meta` AS `b` ON `a`.`id` = `b`.`cluster_id`";

		$query[] = "WHERE `a`.`cluster_type` = " . $db->q(SOCIAL_TYPE_EVENT);
		$query[] = "AND `a`.`featured` = " . $db->q(1);

		// Past event
		if ($pastEventOnly) {
			$query[] = "AND `b`.`start` <= " . $db->q($now);
		}

		$query = implode(' ', $query);

		$sql = $db->sql();
		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadObjectList('id');

		if (empty($result)) {
			return array();
		}

		$result = array_keys($result);

		return $result;
	}

	/**
	 * Unfeatured all pass events
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function unfeaturedEvents($ids)
	{
		$db = ES::db();
		$now = ES::date()->toSql(true);
		$total = 1;

		$query = array();

		$query[] = "UPDATE `#__social_clusters`";

		$query[] = "SET `featured` = " . $db->q(0);

		if (is_array($ids)) {
			$query[] = "WHERE `id` IN (" . implode(',', $ids) . ")";

			$total = count($ids);
		} else {
			$query[] = "WHERE `id` = " . $db->q($ids);
		}

		$query = implode(' ', $query);

		$sql = $db->sql();
		$sql->raw($query);

		$db->setQuery($sql);
		$state = $db->query();

		return $total;
	}

	/**
	 * Retrieves all events posted by specific user, in conjuction with GDPR compliance.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getEventGDPR($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		$limit = $this->normalize($options, 'limit', false);
		$userId = $this->normalize($options, 'userid', null);
		$exclusion = $this->normalize($options, 'exclusion', null);

		$query[] = 'SELECT `id`, `title`, `description`, `created`, `cluster_type` FROM ' . $db->nameQuote('#__social_clusters');
		$query[] = ' WHERE ' . $db->nameQuote('creator_uid') . ' = ' . $db->Quote($userId);
		$query[] = ' AND ' . $db->nameQuote('cluster_type') . ' = ' . $db->Quote(SOCIAL_TYPE_EVENT);
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

			$this->setTotal($totalQuery, true);
		}

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
}
