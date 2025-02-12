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

ES::import('admin:/includes/model');

class EasySocialModelMarketplaceCategories extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('marketplacecategories', $config);
	}

	public function initStates()
	{
		// Ordering, direction, search, limit and limistart is handled by parent::initStates();
		parent::initStates();

		$state = $this->getUserStateFromRequest('state', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'lft');
		$direction = $this->getUserStateFromRequest('direction', 'asc');

		$this->setState('state', $state);
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	public function getItems()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces_categories');

		$search = $this->getState('search');

		if (!empty($search)) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		$state = $this->getState('state');

		if (isset($state) && $state !== 'all') {
			$sql->where('state', $state);
		}

		$ordering = $this->getState('ordering');
		$direction = $this->getState('direction');

		$sql->order($ordering, $direction);

		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		$categories = $this->bindTable('MarketplaceCategory', $result);

		return $categories;
	}

	public function updateCategory($uid, $categoryId)
	{
		$item = ES::table('Marketplace');
		$item->load($uid);
		$item->category_id = $categoryId;
		$item->store();

		// Get workflow for this category
		$category = ES::table('MarketplaceCategory');
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
		$sql->where('a.type', SOCIAL_TYPE_MARKETPLACE);
		$sql->where('d.type', SOCIAL_TYPE_MARKETPLACES);
		$sql->where('d.workflow_id', $workflow->id);

		$db->setQuery($sql);

		return $db->query();
	}

	/**
	 * Determines if a profile is allowed to access to this category
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function hasAccess($categoryId, $type = 'create', $profileId = null)
	{
		$db = ES::db();

		// Check if the category has any access
		$sql = $db->sql();
		$sql->select('#__social_marketplaces_categories_access', 'a');
		$sql->column('count(1)');
		$sql->where('a.category_id', $categoryId);
		$sql->where('a.type', $type);

		$db->setQuery($sql);
		$exists = $db->loadResult();

		// If no access configured, return true always.
		if (!$exists) {
			return true;
		}

		// Delete all existing access type first
		$sql->clear();
		$sql->select('#__social_marketplaces_categories_access', 'a');
		$sql->where('a.category_id', $categoryId);
		$sql->where('a.profile_id', $profileId);
		$sql->where('a.type', $type);

		$db->setQuery($sql);
		$exists = $db->loadResult();

		return $exists;
	}

	/**
	 * Returns an array of SocialTableMarketplaceCategory table object based on profileId.
	 *
	 * @since   3.3
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

			$query[] = "SELECT DISTINCT `a`.* FROM `#__social_marketplaces_categories` AS `a`";
			// $query[] = "LEFT JOIN `#__social_marketplaces_categories_access` AS `b`";
			// $query[] = "ON `a`.`id` = `b`.`category_id`";
			$query[] = "WHERE `a`.`state` = '1'";

			if ($parentOnly) {
				$query[] = "AND `a`.`parent_id` = '0'";
			} elseif ($containerOnly) {
				$query[] = "AND `a`.`container` = '0'";
			}

			// if (!ES::user()->isSiteAdmin()) {
			// 	$query[] = "AND (`b`.`profile_id` = " . $profileId;
			// 	$query[] = "OR `a`.`id` NOT IN (SELECT `category_id` FROM `#__social_marketplaces_categories_access`))";
			// }

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

			$categories = $this->bindTable('MarketplaceCategory', $result);

			$_cache[$idx] = $categories;
		}

		return $_cache[$idx];
	}

	/**
	 * Retrieve parent categories
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getParentCategories($exclusion = array(), $options = array())
	{
		$db = ES::db();

		$sql = $db->sql();
		$sql->select('#__social_marketplaces_categories', 'a');
		$sql->where('a.parent_id', '0');

		if (!empty($exclusion)) {
			$sql->where('a.id', $exclusion, 'NOT IN');
		}

		if (isset($options['state']) && $options['state'] !== 'all') {
			$sql->where('a.state', $options['state']);
		}

		$ordering = isset($options['ordering']) ? $options['ordering'] : 'ordering';

		if ($ordering == 'title') {
			$sql->order('a.title', 'ASC');
		}

		if ($ordering == 'ordering') {
			$sql->order('a.ordering');
		}

		if (isset($options['limit'])) {
			$limitstart = isset($options['limitstart']) ? $options['limitstart'] : 0;

			$sql->limit($limitstart, $options['limit']);
		}

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve child categories
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getChildCategories($parentId, $exclusion = array(), $options = array())
	{
		$db = ES::db();
		$query = array();

		$category = ES::table('MarketplaceCategory');
		$category->load($parentId);

		$query[] = "SELECT DISTINCT `a`.* FROM `#__social_marketplaces_categories` AS `a`";
		$query[] = "WHERE `a`.`lft` > " . $db->Quote($category->lft);
		$query[] = "AND `a`.`lft` < " . $db->Quote($category->rgt);

		if (isset($options['state'])) {
			$query[] = "AND `a`.`state` = " . $db->Quote($options['state']);
		}

		if (!empty($exclusion)) {
			$exclusion = implode(",", $exclusion);
			$query[] = "AND `a`.`id` NOT IN (" . $db->Quote($exclusion) . ")";
		}

		$query[] = "ORDER BY `a`.`ordering`";

		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve one level child categories
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getImmediateChildCategories($parentId)
	{
		$db = ES::db();
		$query = array();

		$query[] = "SELECT DISTINCT `a`.`id` FROM `#__social_marketplaces_categories` AS `a`";
		$query[] = "WHERE `a`.`parent_id` = " . $db->Quote($parentId);
		$query[] = "AND `a`.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$query[] = "ORDER BY `a`.`ordering`";

		$query = implode(' ', $query);
		$db->setQuery($query);

		$results = $db->loadObjectList();

		if (!$results) {
			return false;
		}

		$childs = array();

		foreach ($results as $result) {
			$table = ES::table('MarketplaceCategory');
			$table->load($result->id);

			$childs[] = $table;
		}
		return $childs;
	}

	/**
	 * Update category ordering
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function updateCategoriesOrdering($id, $order)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "update `#__social_marketplaces_categories` set ordering = " . $db->Quote($order);
		$query .= " where id = " . $db->Quote($id);

		$sql->raw($query);

		$db->setQuery($sql);
		$state = $db->query();

		return $state;
	}

	/**
	 * Returns an array of SocialTableMarketpalceCategory table object for frontend listing.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getCategories($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces_categories');

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

		$categories = $this->bindTable('marketplaceCategory', $result);

		return $categories;
	}
}
