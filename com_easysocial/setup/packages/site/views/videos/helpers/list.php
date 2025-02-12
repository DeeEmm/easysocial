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

class EasySocialViewVideosListHelper extends EasySocial
{
	/**
	 * Determines if the current viewer is allowed to create a new video filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function canCreateFilter()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$allowed = false;

			// Get available hashtag filter
			if ($this->my->id) {
				$cluster = $this->getCluster();

				$allowed = true;
			}
		}

		return $allowed;
	}

	/**
	 * Determines if the current viewer is allowed to create videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function canCreateVideo()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$uid = $this->getUid();
			$type = $this->getType();

			// If the current type is user, we shouldn't display the creation if they are viewing another person's list of videos
			if ($type == SOCIAL_TYPE_USER && $uid != $this->my->id) {
				$allowed = false;
				return $allowed;
			}

			$adapter = $this->getAdapter($uid, $type);
			$allowed = $adapter->allowCreation();
		}

		return $allowed;
	}

	public function getActiveHashtag()
	{
		static $hashtag = null;

		if (is_null($hashtag)) {
			$hashtag = $this->input->get('hashtag', '', 'string');
		}

		return $hashtag;
	}

	/**
	 * Determines if the current viewer is viewing videos from a particular category
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCategoryId()
	{
		$id = $this->input->get('categoryId', '', 'int');

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

			$category = ES::table('VideoCategory');
			$category->load($id);

			// Make sure the active category is published.
			if (!$category->isPublished()) {
				$category = false;
			}
		}

		return $category;
	}

	/**
	 * Determines if the current viewer is viewing videos from a user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUserId()
	{
		static $id = null;

		if (is_null($id)) {
			$uid = $this->getUid();
			$type = $this->getType();

			if ($type == SOCIAL_TYPE_USER && $uid) {
				$id = $uid;
				return $id;
			}

			$id = false;
		}

		return $id;
	}

	/**
	 * Determines if the current viewer is viewing videos from a user
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
	 * Determines if the user is viewing videos from a specific custom filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCustomFilterId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('hashtagFilterId', 0, 'int');
		}

		return $id;
	}

	/**
	 * Determines if the user is viewing videos from a specific custom filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCustomFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$id = $this->getActiveCustomFilterId();

			if (!$id) {
				$filter = false;
				return $filter;
			}

			$filter = ES::Table('TagsFilter');
			$filter->load((int) $id);
		}

		return $filter;
	}

	/**
	 * Retrieves the video adapter for videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAdapter()
	{
		static $adapter = null;

		if (is_null($adapter)) {
			$uid = $this->getUid();
			$type = $this->getType();

			$allowedTypes = [
				SOCIAL_TYPE_USER,
				SOCIAL_TYPE_PAGE,
				SOCIAL_TYPE_EVENT,
				SOCIAL_TYPE_GROUP,
				''
			];

			if (!in_array($type, $allowedTypes)) {
				$adapter = false;
				return $adapter;
			}

			$adapter = ES::video($uid, $type);
		}

		return $adapter;
	}

	/**
	 * Generates the list of custom filters for videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCustomFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			$cluster = $this->getCluster();

			$tags = ES::tag();
			$filters = $tags->getFilters($this->my->id, 'videos', $cluster);
		}

		return $filters;
	}

	/**
	 * Generates the create custom filter link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCreateCustomFilterLink()
	{
		static $link = null;

		if (is_null($link)) {
			$options = array('filter' => 'filterForm');

			if ($this->isCluster()) {
				$options['uid'] = $this->getUid();
				$options['type'] = $this->getType();
			}

			$link = ESR::videos($options);
		}

		return $link;
	}

	/**
	 * Generates the canonical options on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCanonicalOptions()
	{
		static $options = null;

		if (is_null($options)) {
			$options = array('external' => true);

			$customFilter = $this->getActiveCustomFilter();

			if ($customFilter) {
				$options['hashtagFilterId'] = $customFilter->getAlias();
			}

			$cluster = $this->getCluster();

			if ($cluster) {
				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->getType();
			}

			$type = $this->getType();
			$filter = $this->getCurrentFilter();

			if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {
				$user = $this->getActiveUser();

				if ($user) {
					$options['uid'] = $user->getAlias();
				}

				$options['type'] = SOCIAL_TYPE_USER;
			}

			// this checking used in normal videos to include the featured videos when 'featured' filter clicked.
			if ($filter == 'featured') {
				$options['filter'] = 'featured';
			}

			if ($filter == 'mine') {
				$options['filter'] = 'mine';
			}

			$category = $this->getActiveCategory();

			if ($category) {
				$options['categoryId'] = $category->getAlias();
			}

			$hashtags = $this->getActiveHashtag();

			if ($hashtags && !$customFilter) {
				$options['hashtag'] = $hashtags;
			}

			$sort = $this->getSort();

			if ($sort) {
				$options['filter'] = $filter;
				$options['sort'] = $sort;

				// Exclude these ordering and sorting page shouldn't get index by search engine advised by Google
				$this->doc->setMetadata('robots', 'noindex,follow');
			}

			$limitstart = $this->getLimitstart();

			if ($limitstart) {
				$options['limitstart'] = $limitstart;
			}
		}

		return $options;
	}

	/**
	 * Generates the canonical url for the current videos listing
	 *
	 * @since	3.0.0
	 * @access	public
	 */
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
	 * Retrieve a list of categories
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCategories($counters = false)
	{
		static $categories = null;

		if (is_null($categories)) {
			$model = ES::model('Videos');
			$categories = $model->getCategories(array(
				'ordering' => 'ordering',
				'direction' => 'asc',
				'pagination' => false)
			);

			$uid = $this->getUid();
			$type = $this->getType();
			$cluster = $this->getCluster();

			// We assign page title for each category
			if ($categories) {
				foreach ($categories as &$category) {
					$category->pageTitle = $category->title;

					if ($counters) {
						$category->total = $category->getTotalVideos($cluster, $uid, $type);
					}

					if ($cluster !== false) {
						$adapter = $this->getAdapter($uid, $type);
						$category->pageTitle = $adapter->getCategoryPageTitle($category);
					}
				}
			}
		}
		return $categories;
	}

	/**
	 * Renders the counter for videos listing
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCounters()
	{
		static $total = null;

		if (is_null($total)) {
			$acl = $this->getFiltersAcl();

			$total = new stdClass();
			$total->videos = $this->getTotalVideos();
			$total->featured = $this->getTotalFeaturedVideos();

			// We only want to get the total number of videos if the user can view the "My Videos" filter
			if ($acl->mine) {
				$total->user = $this->getTotalUserVideos();
			}

			// We only want to get the total number of pending videos if the user can view the "Pending Videos" filter
			if ($acl->pending) {
				$total->pending = $this->getTotalPendingVideos();
			}
		}

		return $total;
	}

	/**
	 * Determines if the current viewer is viewing videos from a cluster
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
	 * Responsible to generate the create videos link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
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
	 * Determines the current filter being viewed on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCurrentFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', 'all', 'word');

			$category = $this->getActiveCategory();

			if ($category) {
				$filter = 'category';
			}

			$customFilter = $this->input->get('hashtagFilterId', 0, 'int');

			if ($customFilter) {
				$filter = 'customFilter';
			}
		}

		return $filter;
	}

	/**
	 * Determines which filters are viewable by the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFiltersAcl()
	{
		static $acl = null;

		if (is_null($acl)) {
			$acl = new stdClass();
			$acl->mine = $this->showMyVideos();
			$acl->pending = $this->showPendingVideos();
		}

		return $acl;
	}

	/**
	 * Determines where the user came from prior to viewing this video page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
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
	 * Generates the page title for the video
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageTitle($reload = false)
	{
		static $title = null;

		if (is_null($title) || $reload) {
			$title = '';

			$uid = $this->getUid();
			$type = $this->getType();
			$filter = $this->getCurrentFilter();
			$adapter = $this->getAdapter();
			$sort = $this->getSort();

			// If user is viewing my specific filters, we need to update the title accordingly.
			if ($filter && $filter != 'category') {
				$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_' . strtoupper($filter));
			}

			if (($uid && $type) || $filter == 'all') {
				$title = $adapter->getListingPageTitle();
			}

			if ($filter == 'featured') {
				$title = $adapter->getFeaturedPageTitle();
			}

			$activeCategory = $this->getActiveCategory();

			if ($filter == 'category' && $activeCategory) {
				$title = $activeCategory->title;

				if ($uid && $type) {
					$title = $adapter->getCategoryPageTitle($activeCategory);
				}
			}

			if ($filter == 'customFilter') {
				$active = $this->getActiveCustomFilter();

				$title = $active->title;
			}

			if ($title) {
				$title = JText::_($title);
			}

			// Not handle for the ajax call for this sorting
			if ($sort && !$reload) {
				$sort = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sort));
				$title = $title . ' - ' . $sort;
			}
		}

		return $title;
	}

	/**
	 * Generates a list of page title that is used in filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageTitles()
	{
		static $titles = null;

		if (is_null($titles)) {
			$titles = new stdClass();
			$titles->all = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_ALL');
			$titles->featured = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_FEATURED');
			$titles->mine = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_MINE');
			$titles->pending = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_PENDING');

			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$adapter = $this->getAdapter($this->getUid(), $this->getType());

				$titles->all = $adapter->getListingPageTitle();
				$titles->featured = $adapter->getFeaturedPageTitle();

			}
		}

		return $titles;
	}

	/**
	 * Determines where the user should be redirected to after performing specific actions on videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getReturnUrl()
	{
		static $url = null;

		// Generate correct return urls for operations performed here
		if (is_null($url)) {

			// Retrieve current page URL before perform any action on the video listing page.
			$url = ES::getURI(true);

			if (!$url) {
				$url = ESR::videos();
			}

			$uid = $this->getUid();
			$type = $this->getType();

			// temporary comment out this is because this getreturnUrl method only handle for the action #3472
			// if ($uid && $type) {
			// 	$adapter = $this->getAdapter();
			// 	$filter = $this->getCurrentFilter();

			// 	$url = $adapter->getAllVideosLink($filter);
			// }

			$url = base64_encode($url);
		}

		return $url;
	}

	/**
	 * Generates a list of sortable options for videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getSortables()
	{
		static $items = null;

		if (is_null($items)) {

			$items = new stdClass();
			$types = array('latest', 'alphabetical', 'popular', 'commented', 'likes');

			$filter = $this->getCurrentFilter();
			$activeCategory = $this->getActiveCategory();
			$customFilter = $this->getActiveCustomFilter();

			foreach ($types as $type) {
				$items->{$type} = new stdClass();

				// display the proper sorting name for the page title.
				$displaySortingName = $this->getPageTitle(true);
				$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($type));

				if ($filter || $activeCategory) {
					$displaySortingName = $displaySortingName . ' - ' . $sortType;
				}

				$attributes = array('data-sorting', 'data-type="' . $type . '"', 'title="' . $displaySortingName . '"');
				if ($customFilter) {
					$attributes[] = 'data-tag-id="' . $customFilter->id . '"';
				} else {
					$attributes[] = 'data-filter="' . $filter . '"';
				}

				$urlOptions = array();
				$urlOptions['sort'] = $type;

				if ($activeCategory) {
					$urlOptions['categoryId'] = $activeCategory->getAlias();
					$attributes[] = 'data-id="' . $activeCategory->id . '"';
				}

				if (!$activeCategory && !$customFilter) {
					$urlOptions['filter'] = $filter;
				}

				if ($customFilter) {
					$urlOptions['hashtagFilterId'] = $customFilter->getAlias();
				}

				$url = ESR::videos($urlOptions);

				$items->{$type}->attributes = $attributes;
				$items->{$type}->url = $url;
			}
		}

		return $items;
	}

	/**
	 * Retrieves the total number of videos on the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalVideos()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Videos');
			$user = $this->getActiveUser();

			// Get the total video for the currently viewed user
			if ($user) {
				$options = array(
							'uid' => $user->id,
							'type' => SOCIAL_TYPE_USER
				);

				$total = $model->getTotalVideos($options);

				return $total;
			}

			$options = array();
			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}

			$total = $model->getTotalVideos($options);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of videos from a user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalUserVideos()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Videos');
			$total = $model->getTotalUserVideos($this->my->id);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of featured videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalFeaturedVideos()
	{
		static $total = null;

		if (is_null($total)) {

			$model = ES::model('Videos');
			$user = $this->getActiveUser();
			$options = array();

			if ($user) {
				$options['uid'] = $user->id;
				$options['type'] = SOCIAL_TYPE_USER;

				$total = $model->getTotalFeaturedVideos($options);

				return $total;
			}

			$cluster = $this->getCluster();
			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}

			$total = $model->getTotalFeaturedVideos($options);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of pending videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalPendingVideos()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Videos');
			$total = $model->getTotalPendingVideos($this->my->id);
		}

		return $total;
	}

	/**
	 * Determines the ownership type of the videos
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
	 * Determines the current sorting type from the listing page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getSort()
	{
		static $sort = null;

		if (is_null($sort)) {
			$sort = $this->input->get('sort', '', 'word');
		}

		return $sort;
	}

	/**
	 * Determines the current limitstart from the listing page
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getLimitstart()
	{
		static $limitstart = null;

		if (is_null($limitstart)) {
			$limitstart = $this->input->get('limitstart', '', 'int');
		}

		return $limitstart;
	}

	/**
	 * Determines the ownership id of these videos
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

	/**
	 * Determines if the current viewer is viewing videos from a cluster, user or just browsing all videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isCluster()
	{
		static $cluster = null;

		if (is_null($cluster)) {
			$cluster = false;
			$type = $this->getType();
			$uid = $this->getUid();

			if ($type && $uid && $type != SOCIAL_TYPE_USER) {
				$cluster = true;
			}
		}

		return $cluster;
	}

	/**
	 * Determines if the current viewer is viewing videos from a cluster, user or just browsing all videos
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isBrowseView()
	{
		static $browseView = null;

		if (is_null($browseView)) {
			$uid = $this->getUid();

			// If no uid, means user is viewing the browsing all video view
			// We define this browse view same like $showsidebar.
			// so it won't break when other customer that still using $showsidebar
			$browseView = !$uid;
		}

		return $browseView;
	}

	/**
	 * Determines if the current viewer is viewing videos from the user profile video page
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isUserProfileView()
	{
		static $isUserProfileView = null;

		if (is_null($isUserProfileView)) {
			$uid = $this->getUid();
			$type = $this->getType();

			if ($uid && $type == SOCIAL_TYPE_USER) {
				$isUserProfileView = true;
			}
		}

		return $isUserProfileView;
	}


	/**
	 * Determines if the "My videos" filter should be rendered
	 *
	 * @since	3.0.0
	 * @access	private
	 */
	private function showMyVideos()
	{
		static $show = null;

		if (is_null($show)) {
			// Determines if the "My Videos" link should appear
			$show = true;

			// We gonna show the 'My videos' if the user is viewing browse all videos page
			$cluster = $this->getCluster();

			if (!$this->my->id || ($cluster !== false) || !$this->isBrowseView()) {
				$show = false;
			}
		}

		return $show;
	}

	/**
	 * Determines if the "Pending videos" filter should be rendered
	 *
	 * @since	3.0.0
	 * @access	private
	 */
	private function showPendingVideos()
	{
		static $show = null;

		if (is_null($show)) {
			$totalPending = $this->getTotalPendingVideos();
			$show = ($totalPending > 0);

			$cluster = $this->getCluster();

			// When viewing another person's videos
			$uid = $this->getUid();
			$type = $this->getType();

			// Do not show pending videos when viewing another person's video list
			if ($uid && $type == SOCIAL_TYPE_USER && $uid != $this->my->id) {
				$show = false;
			}
		}

		return $show;
	}
}
