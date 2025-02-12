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

class EasySocialViewEventsListHelper extends EasySocial
{
	public function getUser()
	{
		static $user = null;

		if (is_null($user)) {

			// Here means we are viewing the user's event
			$userid = $this->input->get('userid', null, 'int');
			$user = ES::user($userid);
		}

		return $user;
	}

	public function getModel()
	{
		static $model = null;

		if (is_null($model)) {
			$model = ES::model('Events');
		}

		return $model;
	}

	public function getCluster()
	{
		static $cluster = null;

		if (is_null($cluster)) {

			// uid is for cluster. if exist, means we are viewing cluster's event
			$uid = $this->input->get('uid', null, 'int');

			// Get the cluster type group/page
			$eventCluster = $this->input->get('type', '', 'string');
			$cluster = false;

			if ($eventCluster == SOCIAL_TYPE_PAGE) {
				$cluster = ES::cluster(SOCIAL_TYPE_PAGE, $uid);
			}

			if ($eventCluster == SOCIAL_TYPE_GROUP) {
				$cluster = ES::cluster(SOCIAL_TYPE_GROUP, $uid);
			}
		}

		return $cluster;
	}

	public function getClusterOptions()
	{
		$cluster = $this->getCluster();
		$browseView = $this->getBrowseView();
		$clusterOptions = array();

		// Get the cluster type group/page
		$eventCluster = $this->input->get('type', '', 'string');

		if ($eventCluster == SOCIAL_TYPE_PAGE) {
			$clusterOptions['page_id'] = $cluster->id;
		}

		if ($eventCluster == SOCIAL_TYPE_GROUP) {
			$clusterOptions['group_id'] = $cluster->id;
		}

		if (!$browseView) {
			if ($eventCluster) {
				$clusterOptions['viewClusterEvents'] = true;
				$clusterOptions['featured'] = false;
			}
		}

		return $clusterOptions;
	}

	public function getShowMyEvents()
	{
		$showMyEvents = true;
		$browseView = $this->getBrowseView();

		// We gonna show the 'showMyEvents' only if the user is viewing browse all group page
		if (!$this->my->id || !$browseView) {
			$showMyEvents = false;
		}

		return $showMyEvents;
	}

	public function getShowPendingEvents()
	{
		$showPendingEvents = false;
		$counters = $this->getCounters();
		$user = $this->getUser();

		if ($this->my->id) {
			$showPendingEvents = $counters->totalPendingEvents > 0;
		}

		if ($user->id != $this->my->id) {
			$showPendingEvents = false;
		}

		return $showPendingEvents;
	}

	public function getShowTotalInvites()
	{
		$showTotalInvites = false;
		$counters = $this->getCounters();
		$user = $this->getuser();

		if ($this->my->id) {
			$showTotalInvites = $counters->invited > 0;
		}

		if ($user->id != $this->my->id) {
			$showTotalInvites = false;
		}

		return $showTotalInvites;
	}

	public function getBrowseView()
	{
		$uid = $this->input->get('uid', null, 'int');
		$userid = $this->input->get('userid', null, 'int');

		// If no uid or userid, means user is viewing the browsing all events view
		// We define this browse view same like $showsidebar.
		// so it won't break when other customer that still using $showsidebar
		$browseView = !$uid && !$userid;

		return $browseView;
	}

	public function getDateLinks()
	{
		$cluster = $this->getCluster();

		$dateLinks = new stdClass();
		$dateLinks->today = ES::event()->getFilterPermalink(array('filter' => 'date', 'date' => ES::date()->format('Y-m-d'), 'cluster' => $cluster));
		$dateLinks->tomorrow = ES::event()->getFilterPermalink(array('filter' => 'date', 'date' => ES::date('+1 day')->format('Y-m-d'), 'cluster' => $cluster));
		$dateLinks->month = ES::event()->getFilterPermalink(array('filter' => 'date', 'date' => ES::date()->format('Y-m'), 'cluster' => $cluster));
		$dateLinks->year = ES::event()->getFilterPermalink(array('filter' => 'date', 'date' => ES::date()->format('Y'), 'cluster' => $cluster));

		return $dateLinks;
	}

	public function getActiveCategory()
	{
		static $activeCategory = null;

		if (is_null($activeCategory)) {
			$categoryId = $this->input->get('categoryid', 0, 'int');

			// the reason why need to do this checking is because the category id key name is not using this 'categoryid' when trying to get the id during ajax call
			// and it involved a lot of different places
			if (!$categoryId) {
				$categoryId = $this->input->get('categoryId', 0, 'int');
			}

			$activeCategory = false;

			if ($categoryId) {
				$activeCategory = ES::table('EventCategory');
				$state = $activeCategory->load($categoryId);

				if (!$state) {
					ES::raiseError(404, JText::_('COM_EASYSOCIAL_EVENTS_INVALID_CATEGORY_ID'));
				}
			}
		}

		return $activeCategory;
	}

	public function getFilter()
	{
		$filter = $this->input->get('filter', 'all', 'string');
		$browseView = $this->getBrowseView();
		$activeCategory = $this->getActiveCategory();

		// Set the filter to all since it's the same as filtering by all
		if ($activeCategory) {
			$filter = 'all';
		}

		// If not browse view, we default the filter to 'created'
		if (!$browseView) {
			$filter = $filter != 'all' ? $filter : 'created';

			$userid = $this->getActiveUserId();
			if ($filter == 'created' && $userid) {
				// check if this user has create event acl granted or not.
				$user = ES::user($userid);
				if (!$user->canCreateEvents()) {
					$filter = 'participated';
				}
			}
		}

		return $filter;
	}

	public function getCreateUrl()
	{
		$cluster = $this->getCluster();

		// Default create event URL
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
	 * Determine if view can access user's evenets page or not.
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
		// there is no privacy for user events. we will
		// check against user's profile viewing privacy.
		// #3111
		if ($this->my->canView($user)) {
			return true;
		}

		return false;
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
				$linkOptions['userid'] = $user->getAlias();
			}

			$filtersLink->all = ES::event()->getFilterPermalink(array_merge(array('filter' => 'all'), $linkOptions));
			$filtersLink->featured = ES::event()->getFilterPermalink(array_merge(array('filter' => 'featured'), $linkOptions));
			$filtersLink->pending = ES::event()->getFilterPermalink(array_merge(array('filter' => 'review'), $linkOptions));
			$filtersLink->invited = ES::event()->getFilterPermalink(array_merge(array('filter' => 'invited'), $linkOptions));
			$filtersLink->created = ES::event()->getFilterPermalink(array_merge(array('filter' => 'created'), $linkOptions));
			$filtersLink->participated = ES::event()->getFilterPermalink(array_merge(array('filter' => 'participated'), $linkOptions));
			$filtersLink->past = ES::event()->getFilterPermalink(array_merge(array('filter' => 'past'), $linkOptions));
			$filtersLink->unpublished = ES::event()->getFilterPermalink(array_merge(array('filter' => 'unpublishes'), $linkOptions));
		}

		return $filtersLink;
	}

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

			// Get total all event
			$allOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'type' => $this->my->isSiteAdmin() ? 'all' : 'user', 'ongoing' => true, 'upcoming' => true);
			$allOptions = array_merge($allOptions, $clusterOptions);

			$counters->all = $model->getTotalEvents($allOptions);
			$counters->featured = $model->getTotalFeaturedEvents($clusterOptions);
			$counters->created = $model->getTotalCreatedJoinedEvents(null, $clusterOptions);
			$counters->createdbyme = $model->getTotalCreatedJoinedEvents($user->id, array('excludeJoinEvent' => true, 'ongoing' => true, 'upcoming' => true));
			$counters->invited = $model->getTotalInvitedEvents(null, $clusterOptions);
			$counters->week1 = $model->getTotalWeekEvents(1, null, $clusterOptions);
			$counters->week2 = $model->getTotalWeekEvents(2, null, $clusterOptions);
			$counters->past = $model->getTotalPastEvents(null, $clusterOptions);
			$counters->today = $model->getTotalEventsToday('today', $clusterOptions);
			$counters->tomorrow = $model->getTotalEventsTomorrow('', $clusterOptions);
			$counters->month = $model->getTotalMonthEvents('', null, $clusterOptions);
			$counters->year = $model->getTotalYearEvents('', null, $clusterOptions);
			$counters->totalPendingEvents = 0;

			$unpublishedOptions = $this->my->isSiteAdmin() ? array('state' => SOCIAL_STATE_UNPUBLISHED) : array('state' => SOCIAL_STATE_UNPUBLISHED, 'creator_uid' => $this->my->id);
			$counters->unpublished = $model->getTotalEvents($unpublishedOptions);

			if (!$browseView) {
				if ($cluster) {
					// If user is viewing cluster's event,
					// we get total of all events regardless created by that logged in user or not
					$clusterOptions['viewClusterEvents'] = true;
					$clusterOptions['featured'] = false;
					$counters->created = $model->getTotalCreatedJoinedEvents(null, $clusterOptions);
				} else {
					$counters->created = $counters->createdbyme;
					$counters->participated = $model->getTotalCreatedJoinedEvents($user->id, array('ongoing' => true, 'upcoming' => true));
				}
			}

			// retrieve pending review events count
			if ($this->my->id != 0) {
				// Get the total number of groups the user created but required review
				$counters->totalPendingEvents = $this->my->getTotalPendingReview(SOCIAL_TYPE_EVENT);
			}
		}

		return $counters;
	}

	public function getActiveDateFilter()
	{
		// Get the date filters
		$activeDateFilter = '';

		$filter = $this->getFilter();

		// Filtering by specific date
		if ($filter == 'date') {

			// Default to today
			$activeDateFilter = 'today';
			$dateString = $this->input->get('date', '', 'string');

			// The only way to determine if the user is filtering by today, tomorrow, month or year is to break up the "-"
			if (!$dateString) {
				$parts = array();
			} else {
				$parts = explode('-', $dateString);
			}

			$totalParts = count($parts);

			// Try to see if it is tomorrow.
			if ($totalParts == 3) {
				$tomorrow = ES::date('+1 day')->format('Y-m-d');

				if ($tomorrow == $dateString) {
					$activeDateFilter = 'tomorrow';
				} else {
					$now = ES::date()->format('Y-m-d');

					if ($now != $dateString) {
						$activeDateFilter = 'normal';
					}
				}
			}

			if ($totalParts == 2) {
				$activeDateFilter = 'month';
			}

			if ($totalParts == 1) {
				$activeDateFilter = 'year';
			}
		}

		return $activeDateFilter;
	}

	/**
	 * Retrieves the current filter
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getCurrentFilter($fromAjax = false)
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', $this->getDefaultFilter(), 'cmd');

			// If trigger from the ajax call then have to retrieve different value
			// because the form data is not using 'filter' name
			if ($fromAjax) {
				$filter = $this->input->get('type', $this->getDefaultFilter(), 'cmd');
			}
		}

		return $filter;
	}

	/**
	 * Retrieves the default filter that should be used on the page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getDefaultFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			// If we are viewing profile's page listing
			// Default the $filter to 'created'
			if ($this->getActiveUser()->canCreateEvents()) {
				$filter = $this->isBrowseView() ? 'all' : 'created';
			} else {
				$filter = $this->isBrowseView() ? 'all' : 'participated';
			}
		}

		return $filter;
	}

	/**
	 * Determines if the user is currently browsing thorugh all events on the site
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isBrowseView()
	{
		static $browse = null;

		if (is_null($browse)) {
			$activeUserId = $this->getActiveUserId();
			$user = $this->getActiveUser();
			$browse = true;

			if ($activeUserId && $user->id) {
				$browse = false;
			}
		}

		return $browse;
	}

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

	public function getActiveUser()
	{
		static $user = null;

		if (is_null($user)) {
			$userId = $this->getActiveUserId();
			$user = ES::user($userId);
		}

		return $user;
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
	 * Include the title data attribute in cluster sorting option
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function renderTitleAttribute($filter = '', $activeCategory = '', $sort = '', $fromAjax = false)
	{
		// display the proper filter name for the page title.
		$displaySortingName = JText::_($this->getPageTitle(true, $fromAjax));

		// Add into the data attribute for this title
		$title = 'title="' . $displaySortingName . '"';

		// sorting page title
		if ($filter || $activeCategory) {
			$title = 'title="' . $displaySortingName . ' - ' . JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sort)) . '"';
		}

		return $title;
	}

	/**
	 * Only show the sorting page title on the event page
	 * For now only handle for the sorting page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getPageTitle($reload = false, $fromAjax = false, $debug = false)
	{
		static $title = null;

		if (is_null($title) || $reload) {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS';

			// All Events
			// Featured Events
			// My Events
			// My Created Events
			// Nearby Events
			// Event Category

			// Retrieve the fiter from above
			$filter = $this->getCurrentFilter($fromAjax);

			// Retrieve the sorting e.g. recent added and closest date
			$ordering = $this->getOrdering();

			// Retrieve the current active category
			$category = $this->getActiveCategory();

			if ($category) {
				$title = $category->getTitle();
			}

			if ($filter == 'featured') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_FEATURED';
			}

			if ($filter == 'mine') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_MINE';
			}

			if ($filter == 'createdbyme') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_CREATEDBYME';
			}

			if ($filter == 'nearby') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_NEARBY';
			}

			if ($filter == 'created') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_CREATED';
			}

			if ($filter == 'participated') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_PARTICIPATED';
			}

			if ($filter == 'review') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_REVIEW';
			}

			if ($filter == 'unpublished') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UNPUBLISHED';
			}

			// Not handle for the ajax call sorting part
			if ($ordering && !$reload) {
				$ordering = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($ordering));
				$title = JText::_($title) . ' - ' . $ordering;
			}
		}

		return $title;
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
}
