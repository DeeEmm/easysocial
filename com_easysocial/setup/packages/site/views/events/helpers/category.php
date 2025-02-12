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

class EasySocialViewEventsCategoryHelper extends EasySocial
{
	/**
	 * Determines the event category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getActiveEventCategory()
	{
		static $eventCategory = null;

		if (is_null($eventCategory)) {

			$id = $this->input->get('id', 0, 'int');

			$eventCategory = ES::table('EventCategory');
			$state = $eventCategory->load($id);

			if (!$state) {
				ES::info()->set(false, JText::_('COM_EASYSOCIAL_EVENTS_INVALID_CATEGORY_ID'), SOCIAL_MSG_ERROR);
				return ES::redirect(ESR::events());
			}
		}

		return $eventCategory;
	}

	/**
	 * Retrieve a list of event child categories that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getChildCategories()
	{
		static $ids = null;

		if (is_null($ids)) {

			$category = $this->getActiveEventCategory();

			$ids = $category->id;

			$clusterModel = ES::model('ClusterCategory');

			// check if this category is a container or not
			if ($category->container) {
				// Get all child ids from this category
				$childs = $clusterModel->getChildCategories($category->id, array(), SOCIAL_TYPE_EVENT, array('state' => SOCIAL_STATE_PUBLISHED));

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

			$category = $this->getActiveEventCategory();

			$clusterModel = ES::model('ClusterCategory');
			$childs = $clusterModel->getImmediateChildCategories($category->id, SOCIAL_TYPE_EVENT);
		}

		return $childs;
	}

	/**
	 * Retrieve a list of event under this category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getEvents()
	{
		static $events = null;

		if (is_null($events)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			$options = array('state' => SOCIAL_STATE_PUBLISHED,
							'ordering' => 'random',
							'category' => $ids,
							'featured' => false,
							'limit' => 5,
							'limitstart' => 0,
							'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE));

			$model = ES::model('Events');
			$events = $model->getEvents($options);
		}

		return $events;
	}

	/**
	 * Retrieve a list of event under this category that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getFeatureEvents()
	{
		static $events = null;

		if (is_null($events)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			$options = array('state' => SOCIAL_STATE_PUBLISHED,
							'sort' => 'random',
							'category' => $ids,
							'featured' => true,
							'limit' => 5,
							'limitstart' => 0,
							'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE));

			$model = ES::model('Events');
			$events = $model->getEvents($options);
		}

		return $events;
	}

	/**
	 * Retrieve a list of random event category members that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomCategoryGuests()
	{
		static $randomGuests = null;

		if (is_null($randomGuests)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get random members from this category
			$categoryModel = ES::model('EventCategories');
			$randomGuests = $categoryModel->getRandomCategoryGuests($ids, SOCIAL_CLUSTER_CATEGORY_MEMBERS_LIMIT);
		}

		return $randomGuests;
	}

	/**
	 * Retrieve total of events that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTotalEvents()
	{
		static $totalEvents = null;

		if (is_null($totalEvents)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get total event within a category
			$model = ES::model('Events');
			$totalEvents = $model->getTotalEvents(array('state' => SOCIAL_STATE_PUBLISHED, 'category' => $ids, 'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE)));
		}

		return $totalEvents;
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
			$categoryModel = ES::model('EventCategories');
			$totalAlbums = $categoryModel->getTotalAlbums($ids);
		}

		return $totalAlbums;
	}

	/**
	 * Retrieve a list of random event category albums that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomCategoryAlbums()
	{
		static $randomAlbums = null;

		if (is_null($randomAlbums)) {

			// Retrieve event category ids
			$ids = $this->getChildCategories();

			// Get random albums for events in this category
			$categoryModel = ES::model('EventCategories');
			$randomAlbums = $categoryModel->getRandomCategoryAlbums($ids);
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

       		$startlimit = $this->input->get('limitstart', 0, 'int');

			$stream = ES::stream();
			$stream->get(array('clusterCategory' => $ids, 'clusterType' => SOCIAL_TYPE_EVENT, 'startlimit' => $startlimit), array('perspective' => 'dashboard'));
		}

		return $stream;
	}
}
