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

class EasySocialModelMarketplaces extends EasySocialModel
{

	public function __construct($config = array())
	{
		parent::__construct('marketplaces', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('filter', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');
		$state = $this->getUserStateFromRequest('state', 'all');

		$this->setState('filter', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
		$this->setState('state', $state);
	}

	/**
	 * Retrieves a list of items from the site.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getItems($options = array())
	{
		$db = $this->db;
		$sql = $this->db->sql();

		$search = $this->getState('search');

		$query = array();

		$query[] = 'SELECT * from ' . $db->qn('#__social_marketplaces');

		$state = $this->getState('state');
		$states = array(SOCIAL_CLUSTER_PENDING, SOCIAL_CLUSTER_DRAFT, SOCIAL_CLUSTER_UPDATE_PENDING);
		$tmpStates = '';

		foreach ($states as $item) {
			$tmpStates .= ($tmpStates) ? ', ' . $this->db->Quote($item) : $this->db->Quote($item);
		}

		if ($state !== 'all') {
			if ($state == SOCIAL_MARKETPLACE_PENDING) {
				$query[] = 'WHERE state IN (' . $tmpStates . ')';
			} else {
				$query[] = 'WHERE state = ' . $db->Quote($state);
			}
		} else {
			$query[] = 'WHERE state NOT  IN (' . $tmpStates . ')';
		}

		$search = $this->getState('search');

		if ($search) {
			$query[] = 'AND title LIKE ' . $db->Quote('%' . $search . '%');
		}

		// Should not include pending scheduled.
		$query[] = 'AND scheduled = ' . $this->db->Quote(SOCIAL_MARKETPLACE_UNSCHEDULED);

		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction');
			$query[] = 'ORDER BY ' . $db->qn($ordering) . ' ' . $direction;
		}

		$query = implode(' ', $query);

		// Set the total records for pagination.
		$this->setTotal($query, true);

		$result = $this->getData($query);

		if (!$result) {
			return $result;
		}

		$items = array();

		foreach ($result as $row) {
			$item = ES::marketplace($row->id);
			$cluster = $item->getCluster();
			$item->creator = $item->getListingCreator($cluster);

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Retrieves the meta data of a list of items
	 *
	 * @since	3.3
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

			$sql->select('#__social_marketplaces', 'a');
			$sql->column('a.*');
			// $sql->column('b.small');
			// $sql->column('b.medium');
			// $sql->column('b.large');
			// $sql->column('b.square');
			// $sql->column('b.avatar_id');
			// $sql->column('b.photo_id');
			// $sql->column('b.storage', 'avatarStorage');
			// $sql->column('f.id', 'cover_id');
			// $sql->column('f.uid', 'cover_uid');
			// $sql->column('f.type', 'cover_type');
			// $sql->column('f.photo_id', 'cover_photo_id');
			// $sql->column('f.cover_id'	, 'cover_cover_id');
			// $sql->column('f.x', 'cover_x');
			// $sql->column('f.y', 'cover_y');
			// $sql->column('f.modified', 'cover_modified');
			// $sql->join('#__social_avatars', 'b');
			// $sql->on('b.uid', 'a.id');
			// $sql->on('b.type', 'a.cluster_type');
			// $sql->join('#__social_covers', 'f');
			// $sql->on('f.uid', 'a.id');
			// $sql->on('f.type', 'a.cluster_type');

			if (count($loadItems) > 1) {
				$sql->where('a.id', $loadItems, 'IN');
				$sql->group('a.id');
			} else {
				$sql->where('a.id', $loadItems[0]);
			}

			// Debugging mode
			// echo $sql->debug();

			$db->setQuery($sql);

			$listings = $db->loadObjectList();

			if ($listings) {
				foreach ($listings as $listing) {
					$loaded[ $listing->id ] = $listing;
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
	 * Retrieves the total number of pending marketplace from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPendingCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces', 'a');
		$sql->where('a.state', array(SOCIAL_MARKETPLACE_PENDING, SOCIAL_MARKETPLACE_DRAFT, SOCIAL_MARKETPLACE_UPDATE_PENDING), 'IN');

		$db->setQuery($sql);

		$total 		= (int) $db->loadResult();

		return $total;
	}

	/**
	 * Return parent total value
	 *
	 * @since  4.0.0
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Retrieves the total items available on site
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getTotalItems($options = array(), $debug = false)
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();
		$uid = $this->normalize($options, 'uid', null);
		$category = $this->normalize($options, 'category', null);
		$type = $this->normalize($options, 'type', null);
		$userid = $this->normalize($options, 'userid', null);
		$state = $this->normalize($options, 'state', SOCIAL_STATE_PUBLISHED);
		$privacy = $this->normalize($options, 'privacy', true);
		$day = $this->normalize($options, 'day', false);

		$skipItems = $this->normalize($options, 'skipItems', array());

		$viewer = ES::user();
		$isSiteAdmin = $viewer->isSiteAdmin();

		$conditions = array();
		$query = array();

		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__social_marketplaces') . ' AS a';

		if (!$isSiteAdmin && $privacy && $type != 'user' && !is_null($type)) {
			$query[] = 'INNER JOIN `#__social_clusters` AS `cls`';
			$query[] = 'ON a.`uid` = cls.`id`';
			$query[] = 'AND a.`type` = cls.`cluster_type`';
		}

		// When blocking is enabled, we should also respect the blocks against the viewer
		$query[] = $this->getUserBlockingJoinQuery($viewer);

		// Build conditions
		if ($state != 'all') {
			$conditions[] = "a.state = " . $this->db->Quote($state);
		}

		if ($userid) {
			$conditions[] = "a.user_id = " . $this->db->Quote($userid);
		}

		if ($category) {
			if (!is_array($category)) {
				$category = ES::makeArray($category);
			}

			$conditions[] = "a.`category_id` IN (" . implode(',', $category) . ")";
		}

		if ($uid && $type && $type == 'user') {
			$conditions[] = "a.user_id = " . $this->db->Quote($uid);
		}

		// This portion of the query is to filter out items created on clusters
		if ($uid && $type) {
			$conditions[] = "a.uid = " . $this->db->Quote($uid);
			$conditions[] = " a.type = " . $this->db->Quote($type);
		}

		if ($skipItems) {
			$tmpIdStr = '';
			foreach ($skipItems as $item) {
				$tmpIdStr .= ($tmpIdStr) ? ', ' . $this->db->Quote($item) : $this->db->Quote($item);
			}

			$conditions[] = " a.id NOT IN (" . $tmpIdStr . ")";
		}

		// quick fix. if the query is empty, dont add into array
		$userBlockQuery = $this->getUserBlockingClauseQuery($viewer, 'bus', false);

		if ($userBlockQuery) {
			$conditions[] = $userBlockQuery;
		}

		if ($day) {
			$start = $day . ' 00:00:01';
			$end = $day . ' 23:59:59';

			$conditions[] = '(a.`created` >= ' . $this->db->Quote($start) . ' AND a.`created` <= ' . $this->db->Quote($end) . ')';
		}

		if ((!$type && !$uid) || ($type == 'user' && !$uid)) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		if (!$isSiteAdmin && $privacy) {

			if ($type && $type != 'user') {
				$tmp = "(";
				$tmp .= " (cls.`type` IN (1,4)) OR";
				$tmp .= " (cls.`type` > 1) AND " . $this->db->Quote($viewer->id) . " IN ( select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`uid` and scn.`type` = " . $this->db->Quote(SOCIAL_TYPE_USER) . " and scn.`state` = 1)";
				$tmp .= ")";

				$conditions[] = $tmp;
			}

			if (!$type || $type == 'user') {

				// Retrieve listing from clusters as well for global listings
				if ((!$type && !$uid) || ($type == 'user' && !$uid)) {
					$conditions[] = $this->getClusterPrivacyClauseQuery($viewer->id, 'a', 'cls', '');
				}
			}
		}

		// Should not include scheduled.
		$conditions[] = "a.scheduled = " . $this->db->Quote(SOCIAL_MARKETPLACE_UNSCHEDULED);

		if ($conditions) {

			if (count($conditions) == 1) {
				$query[] = 'WHERE ' . $conditions[0];
			}

			if (count($conditions) > 1) {

				$whereCond = array_shift($conditions);

				$query[] = 'WHERE ' . $whereCond;
				$query[] = 'AND ' . implode(' and ', $conditions);
			}
		}

		if ($debug) {
			echo str_ireplace('#__', 'jos_', implode(' ', $query));exit;
		}

		$sql->raw($query);

		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of categories from the site
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getCategories($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.* FROM ' . $db->qn('#__social_marketplaces_categories') . ' AS a';

		// Filter for respecting creation access
		$respectAccess = $this->normalize($options, 'respectAccess', false);
		$profileId = $this->normalize($options, 'profileId', 0);

		// if ($respectAccess && $profileId) {
		// 	$query[] = 'LEFT JOIN ' . $db->qn('#__social_videos_categories_access') . ' AS b';
		// 	$query[] = 'ON a.id = b.category_id';
		// }

		$query[] = 'WHERE 1 ';

		// Filter for searching categories
		$search = $this->normalize($options, 'search', '');

		if ($search) {
			$query[] = 'AND ';
			$query[] = $db->qn('title') . ' LIKE ' . $db->Quote('%' . $search . '%');
		}

		$excludeContainer = $this->normalize($options, 'excludeContainer', '');

		if ($excludeContainer) {
			$query[] = 'AND ';
			$query[] = $db->qn('container') . ' = ' . $db->Quote(0);
		}

		// TODO when we move out from abstract style
		// // Respect category creation access
		// if ($respectAccess && $profileId) {
		// 	$query[] = 'AND (';
		// 	$query[] = '(b.`profile_id`=' . $db->Quote($profileId) . ')';
		// 	$query[] = 'OR';
		// 	$query[] = '(a.' . $db->qn('id') . ' NOT IN (SELECT `category_id` FROM `#__social_videos_categories_access`))';
		// 	$query[] = ')';
		// }

		// Ensure that the videos are published
		$state = $this->normalize($options, 'state', true);

		// Ensure that all the categories are listed in backend
		$adminView = $this->normalize($options, 'administrator', false);

		if (!$adminView) {
			$query[] = 'AND ' . $db->qn('state') . '=' . $db->Quote($state);
		}

		$ordering = $this->normalize($options, 'ordering', '');
		$direction = $this->normalize($options, 'direction', '');

		if ($ordering) {
			$query[] = ' ORDER BY ' . $db->qn($ordering) . ' ' . $direction;
		}

		$query = implode(' ', $query);

		// Determines if we need to paginate the result
		$paginate = $this->normalize($options, 'pagination', true);

		if ($paginate) {
			// Set the total records for pagination.
			$totalSql = str_ireplace('a.*', 'COUNT(1)', $query);
			$this->setTotal($totalSql);
		}

		// We need to go through our paginated library
		$result = $this->getData($query, $paginate);

		if (!$result) {
			return $result;
		}

		$categories = array();

		foreach ($result as $row) {
			$category = ES::table('MarketplaceCategory');
			$category->bind($row);

			$categories[] = $category;
		}

		return $categories;
	}

	/**
	 * Retrieves the total number of listings from a category
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getTotalListingsFromCategory($categoryId, $cluster = false, $uid = null, $type = null)
	{
		$config = ES::config();
		$db = $this->db;
		$sql = $db->sql();
		$viewer = ES::user();

		$idx = '';
		static $_cache = array();

		if ($cluster) {
			$idx = $cluster->id . '-' . $cluster->getType();
		}

		if (!$cluster) {
			$idx = $uid . '-' . $type;
		}

		// Determines if we should check agains tthe privacy
		$privacy = !$viewer->isSiteAdmin() && $config->get('privacy.enabled');

		if (!isset($_cache[$idx])) {
			$query = array();
			$query[] = 'SELECT a.`category_id`, COUNT(a.`id`) AS `total`';
			$query[] = 'FROM `#__social_marketplaces` AS a';
			$query[] = $this->getUserBlockingJoinQuery($viewer);

			// Retrieve listings from clusters as well for global listings
			if (!$cluster && !$uid && !$type) {
				$query[] = $this->getClusterPrivacyJoinQuery();
			}

			$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED);
			$query[] = 'AND a.`scheduled`=' . $db->Quote(SOCIAL_MARKETPLACE_UNSCHEDULED);

			// Get listings by specific uid and type
			if ($uid && $type) {
				$query[] = 'AND a.`uid`=' . $db->Quote($uid);
				$query[] = 'AND a.`type`=' . $db->Quote($type);

				// cluster do not use privacy access column
				if ($cluster) {
					$privacy = false;
				}
			}

			// User blocking feature
			$query[] = $this->getUserBlockingClauseQuery($viewer);

			$query[] = 'GROUP BY a.`category_id`';

			$sql->raw($query);
			$db->setQuery($sql);
			$results = $db->loadObjectList();

			$tmp = array();

			if ($results) {
				foreach ($results as $item) {
					$tmp[$item->category_id] = $item->total;
				}
			}

			$_cache[$idx] = $tmp;
		}

		$data = $_cache[$idx];
		$total = 0;

		if (isset($data[$categoryId]) && $data[$categoryId]) {
			$total = $data[$categoryId];
		}

		return $total;
	}

	/**
	 * Retrieves the total featured listings available on site
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getTotalFeaturedItems($options = array())
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();

		$uid = (int) $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$userid = (int) $this->normalize($options, 'userid', null);
		$privacy = $this->normalize($options, 'privacy', true);
		$viewer = ES::user();

		$query[] = 'SELECT COUNT(1) FROM `#__social_marketplaces` AS a';

		// When viewer is a logged in user, we need to check against the blocking features
		$query[] = $this->getUserBlockingJoinQuery($viewer);

		// Retrieve listings from clusters as well for global listings
		if (!$uid && !$type) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		$query[] = 'WHERE a.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = 'AND a.`featured` = ' . $db->Quote(SOCIAL_MARKETPLACE_FEATURED);
		$query[] = 'AND a.`scheduled`=' . $db->Quote(SOCIAL_MARKETPLACE_UNSCHEDULED);

		// Filter only listings by a specific user
		if ($userid) {
			$query[] = 'AND a.`user_id` = ' . $db->Quote($userid);
		}

		// We only filter by uid and type when filtering listings by clusters
		if ($uid && $type) {
			$query[] = 'AND a.`uid` = ' . $db->Quote($uid);
			$query[] = 'AND a.`type` = ' . $db->Quote($type);

			// cluster do not use privacy access column
			if ($type != 'user') {
				$privacy = false;
			}
		}

		// Clause for blocking features
		$query[] = $this->getUserBlockingClauseQuery($viewer);

		// Site admins should never be constrained by the privacy of listings
		$isSiteAdmin = $viewer->isSiteAdmin();

		// Retrieve listings from clusters as well for global listings, since global listings does not have uid and type
		if (!$uid && !$type) {
			$query[] = $this->getClusterPrivacyClauseQuery($viewer->id);
		}

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total user's listings available on site
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getTotalUserItems($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$db = $this->db;
		$sql = $db->sql();

		$query = array();
		$query[] = "select count(1) from `#__social_marketplaces` as a";
		$query[] = "where (a.state = " . $this->db->Quote(SOCIAL_STATE_PUBLISHED) . " OR a.`state` = " . $this->db->Quote(SOCIAL_MARKETPLACE_SOLD) . ")";
		$query[] = "and a.`scheduled`=" . $db->Quote(SOCIAL_MARKETPLACE_UNSCHEDULED);
		$query[] = "and a.user_id = " . $this->db->Quote($userId);

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total pending listings available on site
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getTotalPendingReview($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$sql = $this->db->sql();

		$query = "select count(1) from `#__social_marketplaces` as a";
		$query .= " where a.state = " . $this->db->Quote(SOCIAL_MARKETPLACE_PENDING);
		$query .= " and a.user_id = " . $this->db->Quote($userId);
		$query .= " and a.`type` = " . $this->db->Quote('user');

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of items from the site
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getListings($options = [], $debug = false)
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		// search criteria
		$privacy = $this->normalize($options, 'privacy', true);
		$filter = $this->normalize($options, 'filter', '');
		$featured = $this->normalize($options, 'featured', null);
		$category = $this->normalize($options, 'category', '');
		$sorting = $this->normalize($options, 'sort', 'latest');
		$maxlimit = $this->normalize($options, 'maxlimit', 0);
		$limit = $this->normalize($options, 'limit', false);
		$includeFeatured = $this->normalize($options, 'includeFeatured', null);
		$storage = $this->normalize($options, 'storage', false);
		$uid = $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$source = $this->normalize($options, 'source', false);
		$userid = $this->normalize($options, 'userid', null);
		$hashtags = $this->normalize($options, 'hashtags', null);

		$useLimit = true;

		$query = [];
		$viewer = ES::user();
		$isSiteAdmin = $viewer->isSiteAdmin();
		$countQuery = '';

		// When listings need to be sorted by most likes, we need to get the likes count
		if ($sorting == 'likes') {
			$countQuery = ", (select count(1) from `#__social_likes` as exb where exb.uid = a.id and exb.type = 'marketplaces.user.create') as likes";
		}

		// When listings need to be sorted by most comments, we need to get the comments count
		if ($sorting == 'commented') {
			$countQuery = ", (select count(1) from `#__social_comments` as exb where exb.uid = a.id and exb.element = 'marketplaces.user.create') as totalcomments";
		}

		if (!empty($options['location'])) {
			// If this is a location based search, then we want to include distance column
			$searchUnit = strtoupper($config->get('general.location.proximity.unit','mile'));

			$unit = constant('SOCIAL_LOCATION_UNIT_' . $searchUnit);
			$radius = constant('SOCIAL_LOCATION_RADIUS_' . $searchUnit);

			$lat = $options['latitude'];
			$lng = $options['longitude'];

			if (!$lat && !$lng) {
				// lets get the lat and lon from current logged in user address
				$my = ES::user();
				$address = $my->getFieldValue('ADDRESS');
				$lat = $address->value->latitude ? $address->value->latitude : 0;
				$lng = $address->value->longitude ? $address->value->longitude : 0;
			}

			// If there is a distance provided, then we need to put the distance column into a subquery in order to filter condition on it
			if (!empty($options['distance'])) {
				$distance = $options['distance'];

				$lat1 = $lat - ($distance / $unit);
				$lat2 = $lat + ($distance / $unit);

				$lng1 = $lng - ($distance / abs(cos(deg2rad($lat)) * $unit));
				$lng2 = $lng + ($distance / abs(cos(deg2rad($lat)) * $unit));

				$query[] = "SELECT DISTINCT `a`.`id` " . $countQuery . ", `a`.`distance` FROM (
					SELECT `x`.*, ($radius * acos(cos(radians($lat)) * cos(radians(`x`.`latitude`)) * cos(radians(`x`.`longitude`) - radians($lng)) + sin(radians($lat)) * sin(radians(`x`.`latitude`)))) AS `distance` FROM `#__social_marketplaces` AS `x` WHERE (cast(`x`.`latitude` AS DECIMAL(10, 6)) BETWEEN $lat1 AND $lat2) AND (cast(`x`.`longitude` AS DECIMAL(10, 6)) BETWEEN $lng1 AND $lng2)
				) AS `a`";

			} else {
				$query[] = "SELECT DISTINCT `a`.`id` " . $countQuery . ", ($radius * acos(cos(radians($lat)) * cos(radians(`a`.`latitude`)) * cos(radians(`a`.`longitude`) - radians($lng)) + sin(radians($lat)) * sin(radians(`a`.`latitude`)))) AS `distance` FROM `#__social_marketplaces` AS `a`";
			}
		} else {
			$query[] = 'SELECT a.*';

			// When listings need to be sorted by most likes/commented, we need to get the count
			$query[] = $countQuery;
			$query[] = 'FROM `#__social_marketplaces` AS a';
		}

		// Check for user blocking
		$query[] = $this->getUserBlockingJoinQuery($viewer);

		// Filter listings by specific cluster type (e.g viewing a cluster page)
		if (!is_null($type) && $type != 'user' && $type != 'all') {
			$query[] = 'INNER JOIN `#__social_clusters` AS `cls`';
			$query[] = 'ON a.`uid` = cls.`id`';
			$query[] = 'AND a.`type` = cls.`cluster_type`';
		}


		// Retrieve listings from clusters as well
		if ((!$type && !$uid) || ($type == 'user' && !$uid)) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		if ($filter == 'pending') {
			$query[] = "WHERE a.`state` = " . $db->Quote(SOCIAL_MARKETPLACE_PENDING);
		} else if ($filter == 'created') {
			$query[] = "WHERE (a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED) . " OR a.`state` = " . $db->Quote(SOCIAL_MARKETPLACE_SOLD) . ")";
		} else {
			$query[] = "WHERE a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
			$query[] = "AND a.`scheduled`=" . $db->Quote(SOCIAL_MARKETPLACE_UNSCHEDULED);
		}

		// Filter listings by specific types
		if ($uid && $type) {
			$query[] = 'AND a.`uid`=' . $db->Quote($uid);
			$query[] = 'AND a.`type`=' . $db->Quote($type);
		}

		if ($filter == 'created') {
			$query[] = "and a.`user_id` = " . $db->Quote($viewer->id);
		}

		if ($filter == 'pending' && $userid) {
			$query[] = "and a.`user_id` = " . $db->Quote($userid);
		}

		if ($filter == SOCIAL_TYPE_USER) {
			$query[] = "and a.`user_id` = " . $db->Quote($userid);
		}

		if ($category) {
			if (!is_array($category)) {
				// This is to fixed data that came from module setting which have this format 6:6:alias
				$category = (int) $category;

				$category = ES::makeArray($category);
			}

			$query[] = "and a.`category_id` IN (" . implode(',', $category) . ")";
		}

		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND a.' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		if (!$includeFeatured && !is_null($featured) && $featured !== '') {
			$query[] = "and a.`featured` = " . $db->Quote((int) $featured);
		}

		$query[] = $this->getUserBlockingClauseQuery($viewer);

		if (!$isSiteAdmin && $privacy) {

			// Filtering listings by cluster. We should also respect the privacy
			if ($type && $type != 'user') {
				$query[] = " AND (";
				$query[] = " (cls.`type` IN (1,4)) OR";
				$query[] = " ((cls.`type` > 1) AND " . $db->Quote($viewer->id) . " IN ( select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`uid` and scn.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and scn.`state` = 1))";
				$query[] = ")";

			}

			if (!$type || $type == 'user') {

				// Check against user privacy
				// currently marketplace not yet has privacy
				// if ($config->get('privacy.enabled')) {
				// 	$query[] = $this->getPrivacyQuery($viewer->id, 'marketplaces', 'a');
				// }

				// Retrieve listings from clusters as well for global listings
				if ((!$type && !$uid) || ($type == 'user' && !$uid)) {
					$query[] = $this->getClusterPrivacyClauseQuery($viewer->id);
				}
			}

		}

		$navigationType = $this->normalize($options, 'navigationType', null);
		$created = $this->normalize($options, 'created', null);

		if ($navigationType && $created) {
			// Need to use its own ordering for navigation
			$sorting = false;

			if ($navigationType == 'prev') {
				$query[] = 'AND a.`created` < ' . $db->Quote($created);
				$query[] = 'ORDER BY a.`created` DESC';
			}

			if ($navigationType == 'next') {
				$query[] = 'AND a.`created` > ' . $db->Quote($created);
				$query[] = 'ORDER BY a.`created` ASC';
			}
		}

		if (!$maxlimit && $limit) {

			$totalQuery = implode(' ', $query);

			// Set the total number of items.
			$this->setTotal($totalQuery, true);
		}

		// the sorting must come after the privacy checking to have better sql performance.
		if ($sorting) {

			if ($sorting == 'alphabetical') {
				$query[] = 'ORDER BY a.`title` ASC';
			}

			// Latest first
			if ($sorting == 'latest') {
				$query[] = 'ORDER BY a.`created` DESC';
			}

			// Oldest first
			if ($sorting == 'oldest') {
				$query[] = 'ORDER BY a.`created` ASC';
			}

			// Highest price first
			if ($sorting == 'price_high') {
				$query[] = 'ORDER BY a.`price` DESC';
			}

			// Lowest price first
			if ($sorting == 'price_low') {
				$query[] = 'ORDER BY a.`price` ASC';
			}

			// Highest stocks first
			if ($sorting == 'stock_high') {
				$query[] = 'ORDER BY a.`stock` DESC';
			}

			// Lowest stocks first
			if ($sorting == 'stock_low') {
				$query[] = 'ORDER BY a.`stock` ASC';
			}

			if ($sorting == 'likes') {
				$query[] = 'ORDER BY `likes` DESC';
			}

			if ($sorting == 'commented') {
				$query[] = 'ORDER BY `totalcomments` DESC';
			}

			if ($sorting == 'popular') {
				$query[] = 'ORDER BY `hits` DESC';
			}

			if ($sorting == 'random') {

				$rndColumns = array('a.id', 'a.title', 'a.hits', 'a.featured', 'a.title');
				$rndSorts = array('asc', 'desc', 'desc', 'asc', 'asc', 'desc');

				$rndColumn = $rndColumns[array_rand($rndColumns)];
				$rndSort = $rndSorts[array_rand($rndSorts)];

				$query[] = "order by $rndColumn $rndSort";
			}
		}

		if ($maxlimit) {
			$useLimit = false;
			$query[] = "limit $maxlimit";
		} else if ($limit) {

			// Get the limitstart.
			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->input->get('limitstart', 0, 'int');
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);

			$query[] = "limit $limitstart, $limit";
		}

		$query = implode(' ', $query);

		// if ($debug) {
			// echo str_ireplace('#__', 'jos_', $query);
			// echo '<br><br>';
			// exit;
		// }

		$sql->clear();
		$sql->raw($query);

		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$listings = array();

		foreach ($result as $row) {
			$item = ES::marketplace($row->id);

			$cluster = $item->getCluster();
			$item->creator = $item->getListingCreator($cluster);

			// Manually assign the distance data
			if (!empty($filter['location'])) {
				$item->distance = round($row->distance, 1);
			}

			$listings[] = $item;
		}

		return $listings;
	}

	/**
	 * Retrieves a list of marketplace categories on the site
	 *
	 * @since	3.3
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

			$query[] = "SELECT DISTINCT `a`.* FROM `#__social_marketplaces_categories` AS `a`";
			// $query[] = "LEFT JOIN `#__social_marketplaces_categories_access` AS `b`";
			// $query[] = "ON `a`.`id` = `b`.`category_id`";
			$query[] = "WHERE `a`.`state` = '1'";

			// We want to get parent only
			if ($parentOnly) {
				$query[] = "AND `a`.`parent_id` = '0'";
			} else {
				$query[] = "AND `a`.`container` = '0'";
			}

			// if (!ES::user()->isSiteAdmin()) {
			// 	$query[] = "AND (`b`.`profile_id` = " . $profileId;
			// 	$query[] = "OR `a`.`id` NOT IN (SELECT `category_id` FROM `#__social_marketplaces_categories_access`))";
			// }

			$query[] = "ORDER BY `a`.`ordering`";

			$db->setQuery($sql->raw(implode(' ', $query)));


			$result = $db->loadObjectList();

			$categories = $this->bindTable('MarketplaceCategory', $result);

			$_cache[$idx] = $categories;

		}

		return $_cache[$idx];
	}

	/**
	 * Creates a new listing based on the session.
	 *
	 * @since  3.3
	 * @access public
	 */
	public function createListing(SocialTableStepSession $session)
	{
		ES::import('admin:/includes/marketplace/marketplace');

		$my = ES::user();

		// Create an listing object
		$listing = new SocialMarketplace();
		$listing->user_id = $my->id;
		$listing->uid = $my->id;
		$listing->type = SOCIAL_TYPE_USER;
		$listing->category_id = $session->uid;
		$listing->created = ES::date()->toSql();

		$params = ES::registry($session->values);

		$category = ES::table('MarketplaceCategory');
		$category->load($session->uid);

		// Support for group marketplace
		if ($params->exists('group_id') && !empty($params->get('group_id'))) {
			$group = ES::group($params->get('group_id'));
			$listing->type = SOCIAL_TYPE_GROUP;
			$listing->uid = $group->id;
		}

		// Support for page marketplace
		if ($params->exists('page_id') && !empty($params->get('page_id'))) {
			$page = ES::page($params->get('page_id'));
			$listing->type = SOCIAL_TYPE_PAGE;
			$listing->uid = $page->id;
		}

		$data = $params->toArray();

		// Get the custom fields for this listing
		$customFields = ES::model('Fields')->getCustomFields(array('visible' => SOCIAL_EVENT_VIEW_REGISTRATION, 'group' => SOCIAL_TYPE_MARKETPLACE, 'workflow_id' => $category->getWorkflow()->id));

		$fieldsLib = ES::fields();

		$args = [&$data, &$listing];

		$callback = array($fieldsLib->getHandler(), 'beforeSave');

		$errors = $fieldsLib->trigger('onRegisterBeforeSave', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args, $callback);

		if (!empty($errors)) {
			$this->setError($errors);
			return false;
		}

		// Default to pending state
		$listing->state = SOCIAL_MARKETPLACE_PENDING;

		// If the listing is created by site admin or user doesn't need to be moderated, publish it immediately.
		if ($my->isSiteAdmin() || !$my->getAccess()->get('marketplaces.moderate')) {
			$listing->state = SOCIAL_STATE_PUBLISHED;
		}

		// Trigger apps
		ES::apps()->load(SOCIAL_TYPE_USER);

		// Trigger events
		$dispatcher = ES::dispatcher();
		$triggerArgs = [&$listing, &$my, true];

		// @trigger: onMarketplaceBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onMarketplaceBeforeSave', $triggerArgs);

		// Save the listing
		$state = $listing->save();

		if (!$state) {
			$this->setError($listing->table->getError());

			return false;
		}

		// Notifies admin when a new listing is created
		if ($listing->state === SOCIAL_MARKETPLACE_PENDING || !$my->isSiteAdmin()) {
			$this->notifyAdmins($listing);
		}

		// Trigger the fields again
		$args = [&$data, &$listing];

		$fieldsLib->trigger('onRegisterAfterSave', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args);

		$listing->bindCustomFields($data);

		$fieldsLib->trigger('onRegisterAfterSaveFields', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args);

		// @trigger: onMarketplaceAfterSave
		$triggerArgs = [&$listing, &$my, true];
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onMarketplaceAfterSave', $triggerArgs);

		return $listing;
	}

	/**
	 * Notifies administrator when a new listing is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function notifyAdmins($listing, $edited = false)
	{
		$config = ES::config();

		if (!$config->get('notifications.email.enabled')) {
			return;
		}

		$params = array(
			'title' => $listing->getTitle(),
			'creatorName' => $listing->getCreator()->getName(),
			'creatorLink' => $listing->getCreator()->getPermalink(false, true),
			'categoryTitle' => $listing->getCategory()->get('title'),
			'avatar' => $listing->getSinglePhoto(),
			'permalink' => $listing->getPermalink(true, null, null, false, true),
			'alerts' => false
		);

		$params['type'] = $edited ? 'EDITED' : 'CREATED';

		$title = JText::sprintf('COM_ES_EMAILS_MODERATE_LISTING_' . $params['type'] . '_TITLE', $listing->getTitle());

		$template = 'site/marketplace/created';

		if ($listing->state === SOCIAL_CLUSTER_PENDING || $listing->state === SOCIAL_CLUSTER_UPDATE_PENDING) {
			$params['reject'] = FRoute::controller('marketplaces', array('external' => true, 'task' => 'rejectListing', 'id' => $listing->id, 'key' => $listing->key));
			$params['approve'] = FRoute::controller('marketplaces', array('external' => true, 'task' => 'approveListing', 'id' => $listing->id, 'key' => $listing->key));

			$template = 'site/marketplace/moderate';
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
	 * Retrieves the information of the listing
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getInfo(SocialMarketplace $listing, $activeStep = 0, $retrieveContents = true)
	{
		static $items = array();

		if (!isset($items[$listing->id])) {
			// Load admin's language file
			ES::language()->loadAdmin();

			// Get available workflows for this group
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($listing->getWorkflow()->id, SOCIAL_TYPE_MARKETPLACES, SOCIAL_EVENT_VIEW_DISPLAY);

			// Initialize the fields library
			$fieldsLib = ES::fields();
			$fieldsLib->init(array('privacy' => false));

			$fieldsModel = ES::model('Fields');

			$index = 1;

			foreach ($steps as &$step) {

				$fieldOptions = [
					'step_id' => $step->id,
					'data' => true,
					'dataId' => $listing->id,
					'dataType' => SOCIAL_TYPE_MARKETPLACE,
					'visible' => SOCIAL_EVENT_VIEW_DISPLAY,
					'exclusion' => array('address')
				];

				$step->fields = $fieldsModel->getCustomFields($fieldOptions);

				// If there are fields, we should trigger the apps to prepare them
				if (!empty($step->fields)) {
					$args = [&$listing];
					$fieldsLib->trigger('onDisplay', SOCIAL_FIELDS_GROUP_MARKETPLACE, $step->fields, $args);
					$fieldsLib->trigger('onGetValue', SOCIAL_FIELDS_GROUP_MARKETPLACE, $step->fields, $args);
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

				$step->url = ESR::marketplaces(array('layout' => 'item', 'id' => $listing->getAlias(), 'type' => 'info', 'infostep' => $index), false);

				if ($index === 1) {
					$step->url = ESR::marketplaces(array('layout' => 'item', 'id' => $listing->getAlias(), 'type' => 'info'), false);
				}

				// Get the step title
				$step->title = JText::_($step->title);

				$step->active = !$step->hide && $activeStep == $index;

				$step->index = $index;

				$index++;
			}

			$items[$listing->id] = $steps;
		}

		return $items[$listing->id];
	}

	public function installDefaults()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces_categories');
		$sql->column('COUNT(1)');

		$db->setQuery($sql);
		$total 	= $db->loadResult();

		// There are categories already, we shouldn't be doing anything here.
		if ($total) {
			return;
		}

		// Install marketplace fields
		$this->installFields();

		// First create the default workflow
		$type = 'group';
		$type = 'marketplace';
		$workflow = ES::workflows(0, $type);
		$workflow->createDefaultWorkflow();

		$categories = array('vehicles','apparel','electronics','entertainment','hobbies');

		foreach ($categories as $categoryKey) {
			$results[] = $this->createMarketplaceCategory($categoryKey);
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class 	= $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return;
	}

	public function createMarketplaceCategory($categoryTitle)
	{
		$key = strtoupper($categoryTitle);
		$title = JText::_('COM_ES_INSTALLATION_DEFAULT_MARKETPLACE_CATEGORY_' . $key);
		$desc = JText::_('COM_ES_INSTALLATION_DEFAULT_MARKETPLACE_CATEGORY_' . $key . '_DESC');

		$category = ES::table('MarketplaceCategory');
		$category->alias = strtolower($categoryTitle);
		$category->title = $title;
		$category->description = $desc;
		$category->created = ES::date()->toSql();
		$category->uid = ES::user()->id;
		$category->state = SOCIAL_STATE_PUBLISHED;

		$category->store();
		$category->assignWorkflow();

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('Created marketplace category <b>%1$s</b>', $title);

		return $result;
	}

	/**
	 * Installs required custom fields
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function installFields()
	{
		$group = 'marketplace';

		// Detect if the target folder exists
		$target = JPATH_ROOT . '/media/com_easysocial/apps/fields/' . $group;

		// Get a list of apps within this folder.
		$fields = JFolder::folders( $target , '.' , false , true );

		// Go through the list of apps on the site and try to install them.
		foreach ($fields as $field) {
			$results[] = $this->installField($field);
		}

		return;
	}

	/**
	 * Installs a single custom field
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function installField($path)
	{
		// Retrieve the installer library.
		$installer = ES::get('Installer');

		// Get the element
		$element = basename($path);

		// Try to load the installation from path.
		$state = $installer->load($path);

		// Try to load and see if the previous field apps already has a record
		$oldField = ES::table('App');
		$fieldExists = $oldField->load(array('type' => SOCIAL_APPS_TYPE_FIELDS , 'element' => $element, 'group' => 'marketplace'));

		// Let's try to install it now.
		$app = $installer->install();

		// If there's an error installing, log this down.
		if ($app === false) {
			dump('COM_EASYSOCIAL_INSTALLATION_FIELD_ERROR_INSTALLING_FIELD');
		}

		// If the field apps already exist, use the previous title.
		if ($fieldExists) {
			$app->title = $oldField->title;
			$app->alias = $oldField->alias;
		}

		// Ensure that the field apps is published
		$app->state	= $fieldExists ? $oldField->state : SOCIAL_STATE_PUBLISHED;
		$app->store();

		return $element;
	}

	/**
	 * Retrieves all listings posted by specific user, in conjuction with GDPR compliance.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getMarketplaceGDPR($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$query = array();

		$limit = $this->normalize($options, 'limit', false);
		$userId = $this->normalize($options, 'userid', null);
		$exclusion = $this->normalize($options, 'exclusion', null);

		$query[] = 'SELECT `id`, `title`, `description`, `created` FROM ' . $db->nameQuote('#__social_marketplaces');
		$query[] = ' WHERE ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

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

	/**
	 * Gets the total number of nodes in a marketplace category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalNodes($categoryId , $options = array())
	{
		$db = ES::db();

		$excludeBlocked = isset($options['excludeblocked'] ) ? $options[ 'excludeblocked' ] : 0;

		$query = array();

		$query[] = "select count(1) from `#__social_marketplaces` as a";

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
	 * Check if the marketplace category alias exist
	 *
	 * @since  4.0
	 * @access public
	 */
	public function categoryAliasExists($alias, $exclude = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces_categories');
		$sql->where('alias', $alias);

		if (!empty($exclude)) {
			$sql->where('id', $exclude, '!=');
		}

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Retrieve marketplace fields based on category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getStoryFormFields($category)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_fields', 'a');
		$sql->column('a.*');
		$sql->column('d.element');
		$sql->leftjoin('#__social_fields_steps', 'b');
		$sql->on('a.step_id', 'b.id');
		$sql->leftjoin('#__social_marketplaces_categories', 'c');
		$sql->on('b.workflow_id', $category->getWorkflow()->id);
		$sql->leftjoin('#__social_apps', 'd');
		$sql->on('a.app_id', 'd.id');
		$sql->where('a.visible_registration', 1);
		$sql->where('b.type', SOCIAL_TYPE_MARKETPLACES);
		$sql->where('c.id', $category->id);
		$sql->where('d.group', SOCIAL_FIELDS_GROUP_MARKETPLACE);
		$sql->where('d.type', SOCIAL_APPS_TYPE_FIELDS);
		$sql->where('d.element', array('title', 'description', 'price', 'stock', 'condition'), 'in');

		$db->setQuery($sql);

		return $db->loadObjectList();
	}
}
