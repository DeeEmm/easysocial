<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelPageCategories extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('pagecategories', $config);
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

	/**
	 * Get page categories
	 *
	 * @since   2.0
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

		$sql->where('type', SOCIAL_TYPE_PAGE);

		$ordering = $this->getState('ordering');
		$direction = $this->getState('direction');

		$sql->order($ordering, $direction);

		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		$categories = $this->bindTable('PageCategory', $result);

		return $categories;
	}

	/**
	 * Get pages categories
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getCategories($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_categories', 'a');
		$sql->column('a.*');

		if (isset($options['search']) && !empty($options['search'])) {
			$sql->where('a.title', '%' . $options['search'] . '%', 'LIKE');
		}

		if (isset($options['state']) && $options['state'] !== 'all') {
			$sql->where('a.state', $options['state']);
		}

		if (isset($options['profile_id'])) {
			$sql->join('#__social_clusters_categories_access', 'c');
			$sql->on('a.id', 'c.category_id');
			$sql->on('c.type', 'create');

			$sql->where('c.profile_id', $options['profile_id']);
		}

		if (isset($options['parentOnly'])) {
			$sql->where('a.parent_id', '0');
		}

		if (isset($options['excludeContainer'])) {
			$sql->where('a.container', '0');
		}

		$sql->where('a.type', SOCIAL_TYPE_PAGE);

		$ordering = isset($options['ordering']) ? $options['ordering'] : 'ordering';

		if ($ordering == 'title') {
			$sql->order('a.title', 'ASC');
		}

		// Order by total number of pages
		if ($ordering == 'pages') {
			$sql->join('#__social_clusters', 'b');
			$sql->on('b.category_id', 'a.id');
			$sql->on('b.state', SOCIAL_CLUSTER_PUBLISHED);
			$sql->order('COUNT(b.id)', 'DESC');
			$sql->group('a.id');
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

		$categories = $this->bindTable('PageCategory', $result);

		return $categories;
	}

	public function updatePageCategory($uid, $categoryId)
	{
		$cluster = ES::table('Cluster');
		$cluster->load($uid);

		$cluster->category_id = $categoryId;

		$cluster->store();

		// Get workflow for this category
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
		$sql->where('a.type', 'page');
		$sql->where('d.type', 'clusters');
		$sql->where('d.workflow_id', $workflow->id);

		$db->setQuery($sql);

		return $db->query();
	}
}
