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

class EasySocialViewMarketplacesListHelper extends EasySocial
{
	public function getUser()
	{
		static $user = null;

		if (is_null($user)) {

			// Here means we are viewing the user's listing
			$userid = $this->input->get('uid', null, 'int');
			$user = ES::user($userid);
		}

		return $user;
	}

	public function getModel()
	{
		static $model = null;

		if (is_null($model)) {
			$model = ES::model('Marketplaces');
		}

		return $model;
	}

	/**
	 * Determines if the current viewer is allowed to create a new marketplace filter
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function canCreateFilter()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$allowed = false;

			if ($this->my->id) {
				$allowed = true;
			}
		}

		return $allowed;
	}

	/**
	 * Determines if the current viewer is allowed to sell item
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function canSellItem()
	{
		static $allowed = null;

		if (is_null($allowed)) {
			$uid = $this->getUid();
			$type = $this->getType();

			// If the current type is user, we shouldn't display the creation if they are viewing another person's list of marketplace
			if ($type == SOCIAL_TYPE_USER && $uid != $this->my->id) {
				$allowed = false;
				return $allowed;
			}
		}

		return $allowed;
	}

	/**
	 * Determines if the current viewer is viewing videos from a particular category
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getActiveCategoryId()
	{
		$id = $this->input->get('categoryid', '', 'int');

		return $id;
	}

	/**
	 * Determines if the current viewer is viewing videos from a particular category
	 *
	 * @since	3.3
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

			$category = ES::table('MarketplaceCategory');
			$category->load($id);
		}

		return $category;
	}

	/**
	 * Determines if the current viewer is viewing videos from a user
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getActiveUserId()
	{
		static $userId = false;

		if ($userId === false) {
			// Determines if we should render groups created by a specific user
			$userId = $this->input->get('userid', 0, 'int');
			$userId = !$userId ? null : $userId;
		}

		return $userId;
	}

	/**
	 * Determines if the current viewer is viewing videos from a user
	 *
	 * @since	3.3
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
	 * @since	3.3
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
	 * Determines if the user is viewing marketplace from a specific custom filter
	 *
	 * @since	3.3
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
	 * Generates the list of custom filters for videos
	 *
	 * @since	3.3
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
	 * @since	3.3
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

			$link = ESR::marketplaces($options);
		}

		return $link;
	}

	/**
	 * Generates the canonical options on the page
	 *
	 * @since	3.3
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

				$options['uid'] = $user->getAlias();
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
				$options['categoryid'] = $category->getAlias();
			}

			$ordering = $this->getOrdering();

			if ($ordering) {
				$options['filter'] = $filter;
				$options['ordering'] = $ordering;

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
	 * @since	3.3
	 * @access	public
	 */
	public function getCanonicalUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$options = $this->getCanonicalOptions();

			$url = ESR::marketplaces($options);
		}

		return $url;
	}

	/**
	 * Renders the counter for videos listing
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getCounters()
	{
		static $counters = null;

		if (is_null($counters)) {

			// Prepare the counters on the sidebar
			$counters = new stdClass();

			$clusterOptions = $this->getClusterOptions();
			$browseView = $this->getBrowseView();
			$cluster = $this->getCluster();
			$model = $this->getModel();
			$user = $this->getUser();

			$counters->all = $this->getTotalItems();
			$counters->featured = $this->getTotalFeaturedItems();
			$counters->unpublished = $model->getTotalItems(array('state' => SOCIAL_STATE_UNPUBLISHED));
			$counters->created = $this->getTotalUserItems();

			$counters->pending = 0;

			// retrieve pending review listings count
			if ($this->my->id != 0) {
				// Get the total number of listing the user created but required review
				$counters->pending = $model->getTotalPendingReview($this->my->id);
			}
		}

		return $counters;
	}

	/**
	 * Retrieves the total number of featured listings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalFeaturedItems()
	{
		static $total = null;

		if (is_null($total)) {

			$model = $this->getModel();
			$type = $this->getType();
			$uid = $this->getUid();
			$options = array('state' => SOCIAL_STATE_PUBLISHED);

			if ($uid && $type == SOCIAL_TYPE_USER) {
				$options['uid'] = $uid;
				$options['type'] = SOCIAL_TYPE_USER;

				$total = $model->getTotalFeaturedItems($options);

				return $total;
			}

			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}

			$total = $model->getTotalFeaturedItems($options);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of listings on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalItems()
	{
		static $total = null;

		if (is_null($total)) {
			$model = $this->getModel();
			$type = $this->getType();
			$uid = $this->getUid();
			$options = array('state' => SOCIAL_STATE_PUBLISHED);

			// Get the total video for the currently viewed user
			if ($uid && $type == SOCIAL_TYPE_USER) {
				$options = array( 'uid' => $uid, 'type' => SOCIAL_TYPE_USER);
				$options['uid'] = $uid;
				$options['type'] = SOCIAL_TYPE_USER;

				$total = $model->getTotalItems($options);

				return $total;
			}

			$options = array();
			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}

			$total = $model->getTotalItems($options);

			return $total;
		}

		return $total;
	}

	public function getClusterOptions()
	{
		$cluster = $this->getCluster();
		$browseView = $this->getBrowseView();
		$clusterOptions = array();

		if (!$cluster) {
			return $clusterOptions;
		}

		$clusterOptions['uid'] = $cluster->id;
		$clusterOptions['type'] = $cluster->getType();

		if (!$browseView) {
			$clusterOptions['featured'] = false;
		}

		return $clusterOptions;
	}

	/**
	 * Determines if the current viewer is viewing marketplaces from a cluster
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getCluster()
	{
		static $cluster = null;

		if (is_null($cluster)) {

			// uid is for cluster. if exist, means we are viewing cluster's listing
			$uid = $this->input->get('uid', null, 'int');

			// Get the cluster type group/page
			$listingCluster = $this->input->get('type', '', 'string');
			$cluster = false;

			if ($listingCluster == SOCIAL_TYPE_PAGE) {
				$cluster = ES::cluster(SOCIAL_TYPE_PAGE, $uid);
			}

			if ($listingCluster == SOCIAL_TYPE_GROUP) {
				$cluster = ES::cluster(SOCIAL_TYPE_GROUP, $uid);
			}
		}

		return $cluster;
	}


	/**
	 * Responsible to generate the create videos link
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getCreateUrl()
	{
		$cluster = $this->getCluster();

		// Default create listing URL
		$createUrl = array('layout' => 'create');

		if ($cluster) {
			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				$createUrl['page_id'] = $cluster->id;
			}

			if ($cluster->getType() == SOCIAL_TYPE_GROUP) {
				$createUrl['group_id'] = $cluster->id;
			}
		}

		return $createUrl;
	}

	/**
	 * Determines the current filter being viewed on the page
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getCurrentFilter($fromAjax = false)
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', 'all', 'cmd');

			// If trigger from the ajax call then have to retrieve different value
			// because the form data is not using 'filter' name
			if ($fromAjax) {
				$filter = $this->input->get('type', 'all', 'cmd');
			}
		}

		return $filter;
	}

	/**
	 * Determines which filters are viewable by the user
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getFiltersAcl()
	{
		static $acl = null;

		if (is_null($acl)) {
			$acl = new stdClass();
			$acl->mine = $this->showMyListings();
			$acl->pending = $this->getShowPending();
		}

		return $acl;
	}

	/**
	 * Determines where the user came from prior to viewing this video page
	 *
	 * @since	3.3
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
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPageTitle($reload = false, $fromAjax = false, $debug = false)
	{
		static $title = null;

		if (is_null($title) || $reload) {
			$title = 'COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_ALL';

			// Retrieve the fiter from above
			$filter = $this->getCurrentFilter($fromAjax);

			// Retrieve the sorting e.g. recent added and closest date
			$sorting = $this->getSort();

			// Retrieve the current active category
			$category = $this->getActiveCategory();

			if ($category) {
				$title = $category->getTitle();
			}

			if ($filter == 'featured') {
				$title = 'COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_FEATURED';
			}

			if ($filter == 'created') {
				$title = 'COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_CREATED';
			}

			if ($filter == 'review') {
				$title = 'COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_PENDING';
			}

			// Not handle for the ajax call sorting part
			if ($sorting && !$reload) {
				$title = JText::_($title) . ' - ' . JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sorting));
			}
		}

		return $title;
	}

	/**
	 * Determines where the user should be redirected to after performing specific actions on videos
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getReturnUrl()
	{
		static $url = null;

		// Generate correct return urls for operations performed here
		if (is_null($url)) {

			// Retrieve current page URL before perform any action on the video listing page.
			$url = ES::getURI();

			if (!$url) {
				$url = ESR::marketplaces();
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
	 * @since	3.3
	 * @access	public
	 */
	public function getSortables($activeCategory = false, $fromAjax = false)
	{
		static $items = null;

		if (is_null($items)) {

			$items = new stdClass();
			$types = array('alphabetical', 'latest', 'oldest', 'price_high', 'price_low', 'stock_high', 'stock_low', 'commented', 'likes');

			$filter = $this->getCurrentFilter($fromAjax);
			$activeCategory = $activeCategory ? $activeCategory : $this->getActiveCategory();
			$customFilter = $this->getActiveCustomFilter();

			foreach ($types as $type) {
				$items->{$type} = new stdClass();

				// display the proper sorting name for the page title.
				$displaySortingName = JText::_($this->getPageTitle(true));
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
				$urlOptions['ordering'] = $type;

				if ($activeCategory) {
					$urlOptions['categoryid'] = $activeCategory->getAlias();
					$attributes[] = 'data-id="' . $activeCategory->id . '"';
				}

				if (!$activeCategory && !$customFilter) {
					$urlOptions['filter'] = $filter;
				}

				if ($customFilter) {
					$urlOptions['hashtagFilterId'] = $customFilter->getAlias();
				}

				$url = ESR::marketplaces($urlOptions);

				$items->{$type}->attributes = $attributes;
				$items->{$type}->url = $url;
			}
		}

		return $items;
	}

	/**
	 * Retrieves the total number of videos on the site
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getTotalListings()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Marketplaces');
			$user = $this->getActiveUser();

			// Get the total video for the currently viewed user
			if ($user) {
				$options = array(
							'uid' => $user->id,
							'type' => SOCIAL_TYPE_USER
				);

				$total = $model->getTotalItems($options);

				return $total;
			}

			$options = array();
			$cluster = $this->getCluster();

			if ($cluster !== false) {
				$options['uid'] = $cluster->id;
				$options['type'] = $cluster->getType();
			}

			$total = $model->getTotalItems($options);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of listings from a user
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getTotalUserItems()
	{
		static $total = null;

		if (is_null($total)) {
			$model = ES::model('Marketplaces');
			$total = $model->getTotalUserItems($this->my->id);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of pending videos
	 *
	 * @since	3.3
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
	 * Determines the ownership type of the item
	 *
	 * @since	3.3
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
	 * @since	3.3
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
	 * @since	3.3
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
	 * Determines if the current viewer is viewing marketplace from a cluster, user or just browsing all items
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function isBrowseView()
	{
		static $browseView = null;

		if (is_null($browseView)) {
			$uid = $this->getUid();

			// If no uid, means user is viewing the browsing all marketplace items view
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
	 * @since	3.3
	 * @access	private
	 */
	private function showMyListings()
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
	 * Determine if view can access user's marketplace page or not.
	 *
	 * @since	3.3
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
		// there is no privacy for user marketplaces. we will
		// check against user's profile viewing privacy.
		// #3111
		if ($this->my->canView($user)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the "Pending videos" filter should be rendered
	 *
	 * @since	3.3
	 * @access	private
	 */
	public function getShowPending()
	{
		$showPending = false;
		$counters = $this->getCounters();
		$user = $this->getUser();

		if ($this->my->id) {
			$showPending = $counters->pending > 0;
		}

		if ($user->id != $this->my->id) {
			$showPending = false;
		}

		return $showPending;
	}

	public function getFiltersLink()
	{
		static $filtersLink = null;

		if (is_null($filtersLink)) {

			$cluster = $this->getCluster();
			$user = $this->getUser();
			$browseView = $this->getBrowseView();

			$filtersLink = new stdClass;
			$linkOptions = array('cluster' => $cluster);

			// If the user is viewing others' listing, we should respect that
			if (!$browseView && !$cluster) {
				$linkOptions['uid'] = $user->getAlias();
				$linkOptions['type'] = SOCIAL_TYPE_USER;
			}

			$filtersLink->all = ES::marketplace()->getFilterPermalink(array_merge(array('filter' => 'all'), $linkOptions));
			$filtersLink->featured = ES::marketplace()->getFilterPermalink(array_merge(array('filter' => 'featured'), $linkOptions));

			if ($browseView) {
				$filtersLink->nearby = ES::marketplace()->getFilterPermalink(array_merge(array('filter' => 'nearby'), $linkOptions));

				if (!$cluster && $user->canCreateListing()) {
					$filtersLink->created = ES::marketplace()->getFilterPermalink(array_merge(array('filter' => 'created'), $linkOptions));
				}
			}
		}

		return $filtersLink;
	}

	public function getBrowseView()
	{
		$uid = $this->input->get('uid', null, 'int');
		$userid = $this->input->get('userid', null, 'int');

		// If no uid or userid, means user is viewing the browsing all listing view
		// We define this browse view same like $showsidebar.
		// so it won't break when other customer that still using $showsidebar
		$browseView = !$uid && !$userid;

		return $browseView;
	}
}
