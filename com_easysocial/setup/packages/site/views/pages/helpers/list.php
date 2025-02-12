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

class EasySocialViewPagesListHelper extends EasySocial
{
	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUserId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('userid', false, 'int');

			if ($id === false) {
				// ajax is usering userId so we need to check again if the id is false.
				$id = $this->input->get('userId', false, 'int');
			}
		}

		return $id;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUser()
	{
		$id = $this->getActiveUserId();

		if ($id === false) {
			return false;
		}

		$user = ES::user($id);
		return $user;
	}

	/**
	 * Determines if the current viewer is viewing videos from a particular category
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCategoryId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('categoryid', 0, 'int');

			// the reason why need to do this checking is because the category id key name is not using this 'categoryid' when trying to get the id during ajax call
			// and it involved a lot of different places
			if (!$id) {
				$id = $this->input->get('categoryId', 0, 'int');
			}
		}

		return $id;
	}

	/**
	 * Determines if the current viewer is viewing videos from a particular category
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCategory()
	{
		static $category = null;

		if (is_null($category)) {
			$id = $this->getActiveCategoryId();

			if (!$id) {
				$category = false;
				return $category;
			}

			$category = ES::table('PageCategory');
			$category->load($id);
		}

		return $category;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterLinks()
	{
		static $links = null;

		if (is_null($links)) {
			$links = new stdClass();
			$options = array();

			// If the user is viewing others' listing, we should respect that
			$user = $this->getActiveUser();

			if ($user) {
				$options['userid'] = $user->getAlias();
			}

			$links->all = ESR::pages($options);
			$links->featured = ESR::pages(array_merge(array('filter' => 'featured'), $options));
			$links->pending = ESR::pages(array_merge(array('filter' => 'pending'), $options));
			$links->invited = ESR::pages(array_merge(array('filter' => 'invited'), $options));
			$links->created = ESR::pages(array_merge(array('filter' => 'created'), $options));
			$links->participated = ESR::pages(array_merge(array('filter' => 'participated'), $options));
		}

		return $links;
	}

	public function getCanonicalUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$options = $this->getCanonicalOptions();

			$url = ESR::videos($options);
		}

		return $url;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getEmptyText()
	{
		static $text = null;

		if (is_null($text)) {
			$filter = $this->getCurrentFilter();
			$text = 'COM_EASYSOCIAL_PAGES_EMPTY_' . strtoupper($filter);

			// If this is viewing profile's event, we display a different empty text
			if (!$this->isBrowseView()) {
				$text = 'COM_ES_PAGES_EMPTY_' . strtoupper($filter);
				$user = $this->getActiveUser();

				if ($user && !$user->isViewer()) {
					$text = 'COM_ES_PAGES_USER_EMPTY_' . strtoupper($filter);
				}
			}
		}

		return $text;
	}

	public function getCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$model = ES::model('Videos');
			$categories = $model->getCategories(array(
				'ordering' => 'ordering',
				'direction' => 'asc',
				'pagination' => false)
			);

			$cluster = $this->getCluster();

			// We assign page title for each category
			if ($categories) {
				foreach ($categories as &$category) {
					$category->pageTitle = $category->title;

					if ($cluster !== false) {
						$adapter = $this->getAdapter($this->getUid(), $this->getType());
						$category->pageTitle = $adapter->getCategoryPageTitle($category);
					}
				}
			}
		}
		return $categories;
	}

	public function getCreateLink()
	{
		static $link = null;

		if (is_null($link)) {
			// Construct the video creation link
			$options = array('layout' => 'form');

			$category = $this->getActiveCategory();

			if ($category) {
				$options['categoryId'] = $category->id;
			}

			$cluster = $this->getCluster();

			if ($cluster) {
				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->getType();
			}

			$link = ESR::videos($options);
		}

		return $link;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCurrentFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', 'all', 'cmd');

			if ($filter === 'all') {
				$activeUser = $this->getActiveUser();

				if ($activeUser === false) {
					$activeUser = ES::user();
				}

				$canCreatePages = $activeUser->canCreatePages();

				// If we are viewing profile's page listing default the $filter to 'created'
				if ($canCreatePages) {
					$filter = $this->isBrowseView() ? 'all' : 'created';
				}

				if (!$canCreatePages) {
					$filter = $this->isBrowseView() ? 'all' : 'participated';
				}
			}

			$category = $this->getActiveCategory();

			if ($category) {
				$filter = 'category';
			}

			if ($this->isSearch()) {
				$filter = 'search';
			}
		}

		return $filter;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCluster()
	{
		static $cluster = null;

		if (is_null($cluster)) {
			$uid = $this->getUid();
			$type = $this->getType();

			if (!$uid || $type == SOCIAL_TYPE_USER) {
				$cluster = false;
				return $cluster;
			}

			$cluster = ES::cluster($type, $uid);
		}

		return $cluster;
	}

	/**
	 * Determine if view can access user's pages page or not.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canUserView($user)
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->my->id && $this->my->id == $user->id) {
			return true;
		}

		// since this is checking against user's pages and
		// there is no privacy for user pages. we will
		// check against user's profile viewing privacy.
		// #3111
		if ($this->my->canView($user)) {
			return true;
		}

		return false;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getType()
	{
		static $type = null;

		if (is_null($type)) {
			$type = $this->input->get('type', '', 'word');
		}

		return $type;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUid()
	{
		static $uid = null;

		if (is_null($uid)) {
			$uid = $this->input->get('uid', 0, 'int');
		}

		return $uid;
	}

	public function getFrom()
	{
		static $from = null;

		if (is_null($from)) {
			$from = 'listing';

			$cluster = $this->getCluster();

			if ($cluster) {
				$from = $cluster->getType();
			}

			$type = $this->getType();
			$filter = $this->getCurrentFilter();

			if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {
				$from = SOCIAL_TYPE_USER;
			}
		}

		return $from;
	}

	/**
	 * Determines the current sorting type from the listing page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getOrdering()
	{
		static $ordering = null;

		if (is_null($ordering)) {
			$ordering = $this->input->get('ordering', '', 'word');
		}

		return $ordering;
	}

	/**
	 * Determines the current category filter description
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function getPageCategoryDesc($reload = false)
	{
		static $description = null;

		if (is_null($description) || $reload) {

			$description = '';
			$category = $this->getActiveCategory();

			if ($category) {
				$description = $category->getDescription();
			}
		}

		return $description;
	}

	/**
	 * Generates the page title for the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageTitle($reload = false)
	{
		static $title = null;

		if (is_null($title) || $reload) {

			$title = 'COM_EASYSOCIAL_PAGE_TITLE_PAGES';

			$filter = $this->getCurrentFilter();
			$ordering = $this->getOrdering();

			if ($filter == 'invited') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_INVITED';
			}

			if ($filter == 'mine') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_MY_PAGES';
			}

			if ($filter == 'pending') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_PENDING';
			}

			if ($filter == 'liked') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_LIKED';
			}

			if ($filter == 'featured') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_FEATURED';
			}

			$activeCategory = $this->getActiveCategory();

			if ($filter == 'category' && $activeCategory) {
				$title = $activeCategory->title;
			}

			// Not handle for the ajax call for this sorting
			if ($ordering && !$reload) {

				$ordering = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($ordering));
				$title = JText::_($title) . ' - ' . $ordering;
			}
		}

		return $title;
	}

	public function getPageTitles()
	{
		static $titles = null;

		if (is_null($titles)) {
			$titles = new stdClass();
			$titles->all = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_ALL');
			$titles->featured = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_FEATURED');

			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$adapter = $this->getAdapter($this->getUid(), $this->getType());

				$titles->all = $adapter->getListingPageTitle();
				$titles->featured = $adapter->getFeaturedPageTitle();
			}
		}

		return $titles;
	}

	public function getReturnUrl()
	{
		static $url = null;

		if (is_null($url)) {
			// Generate correct return urls for operations performed here
			$url = ESR::videos();
			$uid = $this->getUid();
			$type = $this->getType();

			if ($uid && $type) {
				$adapter = $this->getAdapter();
				$filter = $this->getCurrentFilter();

				$url = $adapter->getAllVideosLink($filter);
			}

			$url = base64_encode($url);
		}

		return $url;
	}

	public function getSortables()
	{
		static $items = null;

		if (is_null($items)) {
			$items = new stdClass();
			$types = array('latest', 'name', 'popular');

			$browseView = $this->isBrowseView();

			if ($browseView) {
				$activeCategory = $this->getActiveCategory();
				$filter = $this->getCurrentFilter();

				foreach ($types as $type) {

					$items->{$type} = new stdClass();

					// display the proper sorting name for the page title.
					$displaySortingName = JText::_($this->getPageTitle(true));
					$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($type));

					if ($filter || $activeCategory) {
						$displaySortingName = $displaySortingName . ' - ' . $sortType;
					}

					$attributes = array('data-sorting', 'data-filter="' . $filter . '"', 'data-type="' . $type . '"', 'title="' . $displaySortingName . '"');

					if ($activeCategory) {
						$attributes[] = 'data-id="' . $activeCategory->id . '"';
					}

					$urlOptions = array();
					$urlFilter = $filter;

					if ($urlFilter == 'category') {
						$urlFilter = 'all';
					}

					$urlOptions['filter'] = $urlFilter;
					$urlOptions['ordering'] = $type;

					if ($activeCategory) {
						$urlOptions['categoryid'] = $activeCategory->getAlias();
					}

					$items->{$type}->attributes = $attributes;
					$items->{$type}->url = ESR::pages($urlOptions);
				}
			}

		}

		return $items;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCounters()
	{
		static $counters = null;

		$isBrowseView = $this->isBrowseView();

		if (is_null($counters)) {
			$counters = new stdClass();
			$counters->total = $this->getTotalPages();
			$counters->featured = $this->getTotalFeaturedPages();
			$counters->pending = 0;
			$counters->created = $this->getTotalPagesCreated();
			$counters->participated = $this->getTotalParticipatedPages();
			$counters->unpublished = $this->getTotalPagesUnpublished();

			if ($this->my->id) {
				$counters->pending = $this->my->getTotalPendingReview(SOCIAL_TYPE_PAGE);
				$counters->invites = $this->getTotalPageInvites();
			}

		}

		return $counters;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalPages()
	{
		static $total = null;

		if (is_null($total)) {

			$model = ES::model('Pages');

			$options = array(
				'types' => $this->my->isSiteAdmin() ? 'all' : 'user'
			);

			$total = $model->getTotalPages($options);
		}

		return $total;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalPageInvites()
	{
		static $total = null;

		if (is_null($total)) {
			// Get total number of invitations
			$model = ES::model('Pages');

			$total = $model->getTotalInvites($this->my->id);
		}

		return $total;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalFeaturedPages()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Pages');
			$total = $model->getTotalPages(array('featured' => true));
		}

		return $total;
	}

	/**
	 * Get the total pages created by the user
	 *
	 * @since	3.2.2
	 * @access	public
	 */
	public function getTotalPagesCreated()
	{
		static $total = null;

		if (is_null($total)) {
			$id = $this->input->get('userid', 0, 'int');
			$userId = $id ? $id : $this->my->id;

			$model = ES::model('Pages');
			$total = $model->getTotalCreatedPages($userId);
		}

		return $total;
	}

	/**
	 * Get the total pages participated by the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalParticipatedPages()
	{
		static $total = null;

		if (is_null($total)) {
			$id = $this->input->get('userid', 0, 'int');
			$userId = $id ? $id : $this->my->id;

			$model = ES::model('Pages');
			$total = $model->getTotalParticipatedPages($userId);
		}

		return $total;
	}


	/**
	 * Returns the total number unpublished of pages the user has created.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalPagesUnpublished()
	{
		static $total = null;

		if (is_null($total)) {
			$id = $this->input->get('userid', 0, 'int');
			$userId = $id ? $id : $this->my->id;

			$model = ES::model('Pages');
			$total = $model->getTotalUnpublishedPages($userId);
		}

		return $total;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isSearch()
	{
		static $search = null;

		if (is_null($search)) {
			$layout = $this->input->get('layout', '', 'cmd');

			$search = $layout == 'search';
		}

		return $search;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isBrowseView()
	{
		static $browseView = null;

		if (is_null($browseView)) {

			// If there is a user id, means we are viewing profile's page listing
			// $browseView: is a listing view of all page.
			$browseView = true;

			// If no uid, means user is viewing the browsing all pages view.
			// We define this browse view same like $showsidebar. so it won't break when other customer that still using $showsidebar
			$user = $this->getActiveUser();

			if ($user) {
				$browseView = false;
			}

			if ($this->isSearch()) {
				$browseView = true;
			}
		}

		return $browseView;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showMyPages()
	{
		static $show = null;

		if (is_null($show)) {
			$show = true;

			// We gonna show the 'showMyPages' only if the user is viewing browse all page page
			if (!$this->my->id || !$this->isBrowseView()) {
				$show = false;
			}

			if ($this->isSearch()) {
				$show = false;
			}
		}

		return $show;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showInvites()
	{
		static $show = null;

		if (is_null($show)) {
			$show = false;

			if ($this->my->id) {
				$model = ES::model('Pages');
				$show = $model->getTotalInvites($this->my->id) > 0;
			}

			$user = $this->getActiveUser();

			if (!$this->isBrowseView() && $user && !$user->isViewer()) {
				$show = false;
			}

			if ($this->isSearch()) {
				$show = false;
			}
		}

		return $show;
	}

	/**
	 *
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showPendingPages()
	{
		static $show = null;

		if (is_null($show)) {
			$show = true;

			if ($this->my->id) {
				$show = $this->my->getTotalPendingReview(SOCIAL_TYPE_PAGE) > 0;
			}

			$user = $this->getActiveUser();
			if (!$this->isBrowseView() && $user && !$user->isViewer()) {
				$show = false;
			}

			if ($this->isSearch()) {
				$show = false;
			}

			if ($this->my->guest) {
				$show = false;
			}
		}

		return $show;
	}
}
