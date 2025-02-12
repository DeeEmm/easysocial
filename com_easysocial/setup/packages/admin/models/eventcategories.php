<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelEventCategories extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('eventcategories', $config);
	}

	public function initStates()
	{
		// Ordering, direction, search, limit, limitstart is handled by parent::initStates();
		parent::initStates();

		$state = $this->getUserStateFromRequest('state', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'lft');
		$direction = $this->getUserStateFromRequest('direction', 'asc');

		$this->setState('state', $state);
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Returns an array of SocialTableEventCategory table object for backend listing.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getItems()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');

		$search = $this->getState('search');

		if (!empty($search)) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		$state = $this->getState('state');

		if (isset($state) && $state !== 'all') {
			$sql->where('state', $state);
		}

		$sql->where('type', SOCIAL_TYPE_EVENT);

		$ordering = $this->getState('ordering');
		$direction = $this->getState('direction');

		$sql->order($ordering, $direction);

		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		$categories = $this->bindTable('EventCategory', $result);

		return $categories;
	}

	/**
	 * Returns an array of SocialTableEventCategory table object for frontend listing.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getCategories($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories');
		$sql->where('type', SOCIAL_TYPE_EVENT);

		if (isset($options['state']) && $options['state'] !== 'all') {
			$sql->where('state', $options['state']);
		}

		if (isset($options['parentOnly'])) {
			$sql->where('parent_id', '0');
		}

		if (isset($options['search']) && !empty($options['search'])) {
			$sql->where('title', '%' . $options['search'] . '%', 'LIKE');
		}

		if (isset($options['excludeContainer'])) {
			$sql->where('container', '0');
		}

		if (isset($options['ordering'])) {
			$direction = isset($options['direction']) ? $options['direction'] : 'asc';

			$sql->order($options['ordering'], $direction);
		}

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$categories = $this->bindTable('eventCategory', $result);

		return $categories;
	}

	/**
	 * Returns an array of SocialTableEventCategory table object based on profileId.
	 *
	 * @since   1.3
	 * @access  public
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
			$query[] = "WHERE `a`.`type` = 'event'";
			$query[] = "AND `a`.`state` = '1'";

			if ($parentOnly) {
				$query[] = "AND `a`.`parent_id` = '0'";
			} elseif ($containerOnly) {
				$query[] = "AND `a`.`container` = '0'";
			}

			if (!ES::user()->isSiteAdmin()) {
				$query[] = "AND (`b`.`profile_id` = " . $profileId;
				$query[] = "OR `a`.`id` NOT IN (SELECT `category_id` FROM `#__social_clusters_categories_access`))";
			}

			$query[] = "ORDER BY `a`.`lft`";

			$query = implode(' ', $query);

			$db->setQuery($sql->raw($query));

			$result = $db->loadObjectList();

			// we only filter when the parentOnly is not required.
			// Please note: we can only run this because its order by lft column.
			if ($result && !$parentOnly) {

				$items = array();
				$parentCats = array();
				$childCats = array();

				// we need to filter out child categories that has no parent attached.
				foreach ($result as $item) {

					// Only assign that category into it if that is parent category from the result.
					// Only assign child category into it if that parent category exist from the result.
					if (!$item->parent_id || ($item->parent_id && array_key_exists($item->parent_id, $items))) {
						$items[$item->id] = $item;
					}
				}

				// reset the results
				$result = $items;
			}

			$categories = $this->bindTable('EventCategory', $result);

			$_cache[$idx] = $categories;
		}

		return $_cache[$idx];
	}

	/**
	 * Retrieves a list of random members from a particular category
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getRandomCategoryGuests($categoryId, $limit = false)
	{
		$db = ES::db();
		$my = ES::user();

		$q = array();

		$q[] = 'select DISTINCT(a.`uid`) from `#__social_clusters_nodes` as a';
		$q[] = ' INNER JOIN `#__social_clusters` as b on a.`cluster_id` = b.`id`';
		$q[] = ' INNER JOIN `#__social_profiles_maps` as upm on a.`uid` = upm.`user_id`';
		$q[] = ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';
		$q[] = ' INNER JOIN `#__social_users` as u on a.`uid` = u.`user_id`';

		if (ES::isBlockEnabled()) {
			$q[] = $this->getUserBlockingJoinQuery($my, 'bus', 'uid');
		}

		$categoryId = ES::makeArray($categoryId);
		$q[] = ' WHERE b.category_id IN (' . implode(',', $categoryId) . ')';
		$q[] = ' AND u.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$q[] = ' AND b.`type` IN (' . implode(',', array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE)) . ')';

		if (ES::isBlockEnabled()) {
			$q[] = $this->getUserBlockingClauseQuery($my);
		}

		$q[] = ' ORDER BY RAND()';

		if ($limit) {
			$q[] = ' LIMIT ' . $limit;
		}

		// lets join the query.
		$query = implode(' ', $q);

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
	 * Returns an array of SocialTableAlbum object based on category.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getRandomCategoryAlbums($categoryId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_albums', 'a');
		$sql->column('a.*');
		$sql->leftjoin('#__social_clusters', 'b');
		$sql->on('a.uid', 'b.id');
		$sql->on('a.type', 'b.cluster_type');
		$sql->where('b.category_id', $categoryId, 'IN');
		$sql->where('b.type', SOCIAL_EVENT_TYPE_PUBLIC);
		$sql->order('', 'ASC', 'rand');
		$sql->limit(10);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$albums = $this->bindTable('Album', $result);

		return $albums;
	}

	/**
	 * Returns the total number of albums in a category.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getTotalAlbums($categoryId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_albums', 'a');
		$sql->column('a.*');
		$sql->leftjoin('#__social_clusters', 'b');
		$sql->on('a.uid', 'b.id');
		$sql->on('a.type', 'b.cluster_type');
		$sql->where('b.category_id', $categoryId, 'IN');
		$sql->where('b.type', SOCIAL_EVENT_TYPE_PUBLIC);

		$db->setQuery($sql->getTotalSql());

		return $db->loadResult();
	}

	/**
	 * Returns event creation stats in this category.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getCreationStats($categoryId)
	{
		$db = ES::db();
		$sql = $db->sql();
		$dates = array();

		// Get the past 7 days
		$curDate = ES::date();
		for ($i = 0 ; $i < 7; $i++) {
			$obj = new stdClass();

			if ($i == 0) {
				$obj->date = $curDate->toSql();
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
			$date = ES::date($row->date)->format('Y-m-d');

			$query = array();
			$query[] = "SELECT COUNT(1) FROM `#__social_clusters`";
			$query[] = "WHERE DATE_FORMAT(`created`, GET_FORMAT(DATE, 'ISO')) = '$date'";
			$query[] = "AND `category_id` = $categoryId";
			$query[] = "AND `type` = " . SOCIAL_EVENT_TYPE_PUBLIC;
			$query[] = 'GROUP BY `category_id`';

			$query = implode(' ', $query);
			$sql->raw($query);

			$db->setQuery($sql);

			$total = $db->loadResult();

			$result[] = (int) $total;
		}

		return $result;
	}

	public function updateEventCategory($uid, $categoryId)
	{
		$cluster = ES::table('Cluster');
		$cluster->load($uid);

		$cluster->category_id = $categoryId;

		$cluster->store();

		// Get workflow id instead of category id
		$category = ES::table('ClusterCategory');
		$category->load($categoryId);

		$workflow = $category->getWorkflow();

		$db = ES::db();
		$sql = $db->sql();

		$sql->update('#__social_fields_data', 'a');
		$sql->leftjoin('#__social_fields', 'b');
		$sql->on('a.field_id', 'b.id');
		$sql->leftjoin('#__social_fields', 'c');
		$sql->on('b.unique_key', 'c.unique_key');
		$sql->leftjoin('#__social_fields_steps', 'd');
		$sql->on('c.step_id', 'd.id');
		$sql->set('a.field_id', 'c.id', false);
		$sql->where('a.uid', $uid);
		$sql->where('a.type', 'event');
		$sql->where('d.type', 'clusters');
		$sql->where('d.workflow_id', $workflow->id);

		$db->setQuery($sql);

		return $db->query();
	}
}
