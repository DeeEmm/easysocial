<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewGroupsCategoryHelper extends EasySocial
{
	/**
	 * Determines the group category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getActiveGroupCategory()
	{
		static $groupCategory = null;

		if (is_null($groupCategory)) {

			// Get the category id from the query
			$id = $this->input->get('id', 0, 'int');

			$groupCategory = ES::table('GroupCategory');
			$groupCategory->load($id);

			// Check if the category is valid
			if (!$id || !$groupCategory->id) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_GROUPS_INVALID_GROUP_ID'));
			}
		}

		return $groupCategory;
	}

	/**
	 * Retrieve a list of group child categories that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getChildCategories()
	{
		static $ids = null;

		if (is_null($ids)) {

			$category = $this->getActiveGroupCategory();

			$ids = $category->id;

			$categoryModel = ES::model('ClusterCategory');

			// check if this category is a container or not
			if ($category->container) {

				// Get all child ids from this category
				$childs = $categoryModel->getChildCategories($category->id, array(), SOCIAL_TYPE_GROUP, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$ids = $childIds;
				}
			}
		}

		return $ids;
	}

	/**
	 * Retrieve immediately child categories that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getImmediateChildCategories()
	{
		static $childs = null;

		if (is_null($childs)) {

			$category = $this->getActiveGroupCategory();

			$categoryModel = ES::model('ClusterCategory');
			$childs = $categoryModel->getImmediateChildCategories($category->id, SOCIAL_TYPE_GROUP);
		}

		return $childs;
	}

	/**
	 * Retrieve a list of group under this category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getGroups()
	{
		static $groups = null;

		if (is_null($groups)) {

			// Retrieve group category ids
			$ids = $this->getChildCategories();

			// Get recent 10 groups from this category
			$options = array('ordering' => 'random', 'category' => $ids, 'state' => SOCIAL_STATE_PUBLISHED);

			$model = ES::model('Groups');
			$groups = $model->getGroups($options);
		}

		return $groups;
	}

	/**
	 * Retrieve a list of random group category members that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomCategoryMembers()
	{
		static $randomMembers = null;

		if (is_null($randomMembers)) {

			// Retrieve group category ids
			$ids = $this->getChildCategories();

			// Get random members from this category
			$model = ES::model('Groups');
			$randomMembers = $model->getRandomCategoryMembers($ids, SOCIAL_CLUSTER_CATEGORY_MEMBERS_LIMIT);
		}

		return $randomMembers;
	}

	/**
	 * Retrieve total of groups that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTotalGroups()
	{
		static $totalGroups = null;

		if (is_null($totalGroups)) {

			// Retrieve group category ids
			$ids = $this->getChildCategories();

			// Get total groups within a category
			$model = ES::model('Groups');
			$totalGroups = $model->getTotalGroups(array('category_id' => $ids));
		}

		return $totalGroups;
	}

	/**
	 * Retrieve total of albums that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTotalAlbums()
	{
		static $totalAlbums = null;

		if (is_null($totalAlbums)) {

			// Retrieve group category ids
			$ids = $this->getChildCategories();

			// Get total albums within a category
			$model = ES::model('Groups');
			$totalAlbums = $model->getTotalAlbums(array('category_id' => $ids));
		}

		return $totalAlbums;
	}

	/**
	 * Retrieve a list of random group category albums that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomAlbums()
	{
		static $randomAlbums = null;

		if (is_null($randomAlbums)) {

			// Retrieve group category ids
			$ids = $this->getChildCategories();

			// Get random albums for groups in this category
			$model = ES::model('Groups');
			$randomAlbums = $model->getRandomAlbums(array('category_id' => $ids, 'core' => false));
		}

		return $randomAlbums;
	}

	/**
	 * Retrieve a list of stream item related with category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getStreamData()
	{
		static $stream = null;

		if (is_null($stream)) {

			// Retrieve group category ids
			$ids = $this->getChildCategories();

			// Get the stream for this group
			$stream = ES::stream();
			$stream->get(array('clusterCategory' => $ids, 'clusterType' => SOCIAL_TYPE_GROUP), array('perspective' => 'dashboard'));
		}

		return $stream;
	}
}
