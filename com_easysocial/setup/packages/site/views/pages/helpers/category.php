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

class EasySocialViewPagesCategoryHelper extends EasySocial
{
	/**
	 * Determines the page category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getActivePageCategory()
	{
		static $pageCategory = null;

		if (is_null($pageCategory)) {

			// Get the category id from the query
			$id = $this->input->get('id', 0, 'int');

			$pageCategory = ES::table('PageCategory');
			$pageCategory->load($id);

			// Check if the category is valid
			if (!$id || !$pageCategory->id) {
				return ES::exception('COM_EASYSOCIAL_PAGES_INVALID_PAGE_ID');
			}
		}

		return $pageCategory;
	}

	/**
	 * Retrieve a list of page child categories that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getChildCategories()
	{
		static $ids = null;

		if (is_null($ids)) {

			$category = $this->getActivePageCategory();

			$ids = $category->id;

			$categoryModel = ES::model('ClusterCategory');

			// check if this category is a container or not
			if ($category->container) {
				// Get all child ids from this category
				$childs = $categoryModel->getChildCategories($category->id, array(), SOCIAL_TYPE_PAGE, array('state' => SOCIAL_STATE_PUBLISHED));

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

			$category = $this->getActivePageCategory();

			$clusterModel = ES::model('ClusterCategory');
			$childs = $clusterModel->getImmediateChildCategories($category->id, SOCIAL_TYPE_PAGE);
		}

		return $childs;
	}

	/**
	 * Retrieve a list of page under this category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getPages()
	{
		static $pages = null;

		if (is_null($pages)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get recent 10 pages from this category
			$options = array('ordering' => 'random', 'category' => $ids, 'state' => SOCIAL_STATE_PUBLISHED);

			$model = ES::model('Pages');
			$pages = $model->getPages($options);
		}

		return $pages;
	}

	/**
	 * Retrieve a list of random pages category members that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomCategoryFollowers()
	{
		static $randomMembers = null;

		if (is_null($randomMembers)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get random members from this category
			$model = ES::model('Pages');
			$randomMembers = $model->getRandomCategoryFollowers($ids, SOCIAL_CLUSTER_CATEGORY_MEMBERS_LIMIT);

		}

		return $randomMembers;
	}

	/**
	 * Retrieve total of pages that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTotalPages()
	{
		static $totalPages = null;

		if (is_null($totalPages)) {

			// Retrieve pages category ids
			$ids = $this->getChildCategories();

			// Get total pages within a category
			$model = ES::model('Pages');
			$totalPages = $model->getTotalPages(array('category_id' => $ids));
		}

		return $totalPages;
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

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get total albums within a category
			$model = ES::model('Pages');
			$totalAlbums = $model->getTotalAlbums(array('category_id' => $ids));
		}

		return $totalAlbums;
	}

	/**
	 * Retrieve a list of random page category albums that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomAlbums()
	{
		static $randomAlbums = null;

		if (is_null($randomAlbums)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get random albums for page in this category
			$model = ES::model('Pages');
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

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get the stream for this page
			$stream = ES::stream();
			$stream->get(array('clusterCategory' => $ids, 'clusterType' => SOCIAL_TYPE_PAGE), array('perspective' => 'dashboard'));
		}

		return $stream;
	}
}
