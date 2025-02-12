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

class EasySocialViewEvents extends EasySocialSiteView
{
	public static $allowedFilters = ['date', 'week1', 'week2', 'all', 'featured', 'mine', 'participated', 'invited', 'going', 'pending', 'maybe', 'notgoing', 'past', 'ongoing', 'upcoming', 'nearby', 'review', 'created', 'createdbyme', 'unpublished'];

	/**
	 * Checks if the event feature is enabled.
	 *
	 * @since  1.3
	 * @access public
	 */
	private function checkFeature()
	{
		if (!$this->config->get('events.enabled')) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_DISABLED'), SOCIAL_MSG_ERROR);

			$this->info->set($this->getMessage());

			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}
	}


	/**
	 * Renders the about page
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function about($event)
	{
		$model = ES::model('Events');
		$steps = $model->getAbout($event);

		$options = array('id' => $event->getAlias(), 'page' => 'info', 'layout' => 'item', 'external' => true);
		$infoLink = FRoute::events($options, false);

		// generate canonical link for the event info page
		$this->page->canonical($infoLink);

		$this->set('layout', 'info');
		$this->set('event', $event);
		$this->set('steps', $steps);

		return parent::display('site/events/about/default');
	}

	/**
	 * Renders the app view for event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function app($event, $app)
	{
		if (!$app->hasAccess($event->category_id)) {

			$this->setMessage(JText::_('COM_EASYSOCIAL_EVENT_DOES_NOT_HAVE_ACCESS'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			$redirect = ESR::events(array('layout' => 'item', 'id' => $event->getAlias()), false);

			return $this->redirect($redirect);
		}

		// lets load backend language as well.
		ES::language()->loadAdmin();

		$app->loadCss();

		$event->renderPageTitle($app->get('title'), 'events');

		// to allow 3rd party app to support custom views,
		// we need to get from customView instead of hardcode the view to groups
		// when the site enable sef mode.
		$appView = $this->input->get('customView', 'events', 'default');

		$appsLib  = ES::apps();
		$contents = $appsLib->renderView(SOCIAL_APPS_VIEW_TYPE_EMBED, $appView, $app, array('eventId' => $event->id));

		$layout = 'apps.' . $app->element;

		// To know which is the active item on the cover navigation
		if ($app->element == 'guests') {
			$layout = 'guests';
		}

		$this->set('event', $event);
		$this->set('contents', $contents);
		$this->set('layout', $layout);

		return parent::display('site/events/app/default');
	}

	/**
	 * Renders the calendar layout
	 *
	 * @since   2.2.3
	 * @access  public
	 */
	public function calendar()
	{
		ES::setMeta();

		$unix = $this->input->getString('date', ES::date()->toUnix());

		$day = date('d', $unix);
		$month = date('m', $unix);
		$year = date('Y', $unix);

		// Create a calendar object
		$calendar = new stdClass();
		$calendar->year = $year;
		$calendar->month = $month;

		// Configurable start of week
		$startOfWeek = $this->config->get('events.startofweek');

		// Here we generate the first day of the month
		$calendar->first_day = mktime(0, 0, 0, $month, 1, $year);

		// This gets us the month name
		$calendar->title = date('F', $calendar->first_day);

		// Sets the calendar header
		$date = ES::date($unix, false);
		$calendar->header = $date->format(JText::_('COM_EASYSOCIAL_DATE_MY'));

		// Here we find out what day of the week the first day of the month falls on
		$calendar->day_of_week = date('D', $calendar->first_day);

		// Once we know what day of the week it falls on, we know how many blank days occure before it. If the first day of the week is a Sunday then it would be zero
		$dayOfWeek = $date->getDayOfWeek($calendar->day_of_week);

		// Day of week is dependent on the start of the week
		if ($dayOfWeek < $startOfWeek) {
			$calendar->blank = 7 - $startOfWeek + $dayOfWeek;
		} else {
			$calendar->blank = $dayOfWeek - $startOfWeek;
		}

		// Due to timezone issue, we will use the mid date of the month to get the next / previous months. #300
		$midMonth = ES::date($date->format('Y-m') . '-15');

		// Previous month
		$calendar->previous = strtotime('-1 month', $midMonth->toUnix());

		// Next month
		$calendar->next = strtotime('+1 month', $midMonth->toUnix());

		// Determine how many days are there in the current month
		$calendar->days_in_month = date('t', $calendar->first_day);

		$categoryId = $this->input->get('categoryId', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');

		$filter = $this->input->get('filter', 'all', 'string');

		$this->set('filter', $filter);
		$this->set('calendar', $calendar);
		$this->set('categoryId', $categoryId);
		$this->set('clusterId', $clusterId);

		return parent::display('site/events/calendar/default');
	}

	/**
	 * Post processing after inviting a friend
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function sendInvites($event)
	{
		ES::info()->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::friends(array('layout' => 'invite', 'cluster_id' => $event->id), false));
		}

		return $this->redirect(ESR::events(array('layout' => 'item', 'id' => $event->getAlias()), false));
	}

	/**
	 * Displays the event listing main page.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function display($tpl = null)
	{
		$this->checkFeature();

		// Check for profile completeness
		ES::checkCompleteProfile();

		// Set Meta data
		ES::setMeta();

		// normalize the canonical link
		$canonicalOptions = ES::normalizeCanonicalOptions(true);

		// Add canonical tag for event listing page
		$this->page->canonical(ESR::events($canonicalOptions));

		$model = ES::model('Events');

		// Default page title
		$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS';

		// Get the user's id
		// Here means we are viewing the user's event
		$userid = $this->input->get('userid', null, 'int');
		$user = ES::user($userid);

		// If the user doesn't exist throw an error when someone access this event page under user page
		if ($userid && !$user->id) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'), 404);
		}

		// If the user is blocked, they should not be accessible #5069
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// Determines the current filter being viewed
		$helper = $this->getHelper('List');

		if ($userid && !$helper->canUserView($user)) {
			return $this->restricted($user);
		}

		// Retrieve known filter
		$cluster = $helper->getCluster();
		$browseView = $helper->getBrowseView();
		$filter = $helper->getFilter();
		$clusterOptions = $helper->getClusterOptions();
		$activeCategory = $helper->getActiveCategory();
		$activeDateFilter = $helper->getActiveDateFilter();
		$dateLinks = $helper->getDateLinks();
		$filtersLink = $helper->getFiltersLink();
		$showMyEvents = $helper->getShowMyEvents();
		$showPendingEvents = $helper->getShowPendingEvents();
		$showTotalInvites = $helper->getShowTotalInvites();
		$createUrl = $helper->getCreateUrl();

		$this->set('cluster', $cluster);

		// Check if the cluster is private
		// If yes, we show restricted page instead
		if ($cluster && !$cluster->canViewEvent()) {
			return $this->restricted($cluster);
		}

		// Get the filter
		$ordering = $this->input->get('ordering', 'start', 'word');
		$includePast = $this->input->get('includePast', null, 'bool');
		$hideRepetition = $this->input->get('hideRepetition', false, 'bool');

		// Check if the current filter is allowed
		if (!in_array($filter, self::$allowedFilters)) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_EVENTS_INVALID_FILTER_ID'));
		}

		// Since not logged in users cannot filter by 'invited' or 'mine' and etc, they shouldn't be able to access these filters at all
		$disallowedGuestFilters = array('invited', 'mine', 'going', 'maybe', 'notgoing', 'createdbyme');

		if ($this->my->guest && in_array($filter, $disallowedGuestFilters)) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// if filter is participated and if current viewer is a guest, if this guest user is viewing
		// another user's participated events, we should allow. else we should redirect to dashboard.
		if ($this->my->guest && $filter == 'participated' && !$userid) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Exclude these filter page shouldn't get index by search engine because end up it will index future and past date.
		$excludeIndexFilterPage = array('nearby', 'date', 'week1', 'week2', 'past');

		if (in_array($filter, $excludeIndexFilterPage)) {
			$this->doc->setMetadata('robots', 'noindex,follow');
		}

		// Theme related settings
		$showSorting = true;
		$showPastFilter = true;
		$showHideRepetitionFilter = false;
		$showDistance = false;
		$showDistanceSorting = false;
		$hasLocation = false;
		$showDateNavigation = false;
		$distance = $this->config->get('events.nearby.radius');

		// Flag to see if this process should be delayed
		// Currently it is for the case of nearby filter
		// Nearby filter can only work if the location is retrieved through javascript
		$delayed = false;

		// Default options for listing
		$options = [
			'state' => SOCIAL_STATE_PUBLISHED,
			'ordering' => $ordering,
			'type' => $this->my->isSiteAdmin() ? 'all' : 'user',
			'featured' => false,
			'limit' => ES::getLimit('events_limit')
		];

		if ($options['ordering'] == 'recent' || $includePast) {
			$options['direction'] = 'desc';
		}

		if ($cluster) {
			$options['type'] = 'all';

			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				$options['page_id'] = $cluster->id;
			}

			if ($cluster->getType() == SOCIAL_TYPE_GROUP) {
				$options['group_id'] = $cluster->id;
			}
		}

		// Explicitly display past event by default for mine and createdbyme filter
		if (is_null($includePast) && ($filter == 'mine' || $filter == 'createdbyme')) {
			$includePast = true;
		}

		// We do not want to include past events by default unless explicitly enabled
		if (!$includePast) {
			$options['ongoing'] = true;
			$options['upcoming'] = true;
		}

		// Set the route options so that filter can add extra parameters
		$routeOptions = ['option' => SOCIAL_COMPONENT_NAME, 'view' => 'events', 'filter' => $filter];

		// if ($filter != 'all' && $filter != 'created') {
		// 	$routeOptions['filter'] = $filter;
		// }

		if ($cluster && $cluster->id) {
			$title = JText::_('COM_ES_CREATED_EVENTS') . ' - ' . $cluster->getTitle();
			$routeOptions['uid'] = $cluster->getAlias();
			$routeOptions['type'] = $cluster->cluster_type;

			// Increment the hit counter
			$cluster->hit();
		}

		// If user is an admin then he should be able to see all events
		// If not then we set guestuid as the user id without any guest state
		// This is because event list should consist of
		// Open, Closed, and for Invite Only if the user is part of it
		// If filter by guest state, then types is always all because we only get what the user is involved

		// [Category Filter]
		if ($activeCategory) {
			$options['category'] = $activeCategory->id;

			// check if this category is a container or not
			if ($activeCategory->container) {
				// Get all child ids from this category
				$categoryModel = ES::model('ClusterCategory');
				$childs = $categoryModel->getChildCategories($activeCategory->id, array(), SOCIAL_TYPE_EVENT, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = [];

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}

			// Set the filter to all since it's the same as filtering by all
			$filter = 'all';
			$title = $activeCategory->getTitle();
			$routeOptions['categoryid'] = $activeCategory->getAlias();

			$clusterCategoryTbl = ES::table('ClusterCategory');
			$clusterCategoryTbl->load($activeCategory->id);

			// normalize the canonical link
			$canonicalOptions = ES::normalizeCanonicalOptions();

			$this->page->description($helper->getPageCategoryDesc());
			$this->page->canonical($clusterCategoryTbl->getFilterPermalink(true, true, $canonicalOptions));
		}

		$featuredEvents = [];

		// Process featured events
		if (($filter === 'all' && ($browseView || ($cluster && $cluster->id)))) {
			$featuredOptions = [
				'featured' => true,
				'state' => SOCIAL_STATE_PUBLISHED,
				'ordering' => 'start'
			];

			if ($cluster && $cluster->getType() === SOCIAL_TYPE_PAGE) {
				$featuredOptions['page_id'] = $cluster->id;
			}

			if ($cluster && $cluster->getType() === SOCIAL_TYPE_GROUP) {
				$featuredOptions['group_id'] = $cluster->id;
			}

			if ($userid && $user->id) {
				$options['creator_type'] = SOCIAL_TYPE_USER;
				$featuredOptions['creator_uid'] = $userid;
			}

			if ($activeCategory) {
				$featuredOptions['category'] = $activeCategory->id;
			}

			$featuredEvents = $model->getEvents($featuredOptions);
		}

		$model->generateFilterOptions($filter, $options, $this->my->id);

		// Filter by participated events
		if ($filter === 'participated' && !$browseView) {
			$options['creator_uid'] = $user->id;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['creator_join'] = true;
			$options['type'] = 'all';
			$options['featured'] = 'all';
		}

		// Filters that are related to the current logged in user
		$filtersRelatedToUser = ['invited', 'going', 'pending', 'maybe', 'notgoing'];

		if (in_array($filter, $filtersRelatedToUser)) {
			$options['guestuid'] = $this->my->id;
			$options['type'] = 'all';
		}

		// Filter by past events
		if ($filter === 'past') {
			$showPastFilter = false;
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_PAST';

			$options['ordering'] = 'recent';
			$options['direction'] = 'desc';
			$options['past'] = true;

			// Past event should show all events regardless of featured status. #1402
			if (isset($options['featured'])) {
				unset($options['featured']);
			}

			// For past events, these needs to be off
			$options['ongoing'] = false;
			$options['upcoming'] = false;
		}

		if ($filter === 'ongoing') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_ONGOING';
			$options['ongoing'] = true;
			$options['upcoming'] = false;
		}

		if ($filter === 'upcoming') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING';
			$options['upcoming'] = true;
			$options['ongoing'] = false;
		}

		// Date navigation
		$activeDate = false;
		$navigation = new stdClass();

		// Get the date filters
		$pageTitle = '';

		// Filtering by specific date
		if ($filter == 'date') {
			// We do not want to show sorting
			$showSorting = false;
			$showPastFilter = false;
			$showDateNavigation = true;

			$dateString = $this->input->get('date', '', 'string');

			// The only way to determine if the user is filtering by today, tomorrow, month or year is to break up the "-"
			if (!$dateString) {
				$parts = [];
			} else {
				$parts = explode('-', $dateString);
			}

			$totalParts = count($parts);

			// Try to see if it is tomorrow.
			if ($totalParts === 3) {
				$tomorrow = ES::date('+1 day')->format('Y-m-d');
			}

			if ($totalParts === 2) {
				$year = $parts[0];
				$month = $parts[1];
			}

			if ($totalParts === 1) {
				$year = (int) $dateString;
			}

			// Regardless of the include past option or not, we should just display the events since they are filtered by date
			if (isset($options['ongoing'])) {
				unset($options['ongoing']);
			}

			if (isset($options['upcoming'])) {
				unset($options['upcoming']);
			}
		}

		if ($activeDateFilter === 'today') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TODAY';

			// Get today's date
			$activeDate = ES::date();
			$start = $activeDate->format('Y-m-d 00:00:00');
			$end = $activeDate->format('Y-m-d 23:59:59');

			$options['dateRange'] = true;
			$options['range-start'] = $start;
			$options['range-end'] = $end;
			$options['featured'] = 'all';

			// Get the date navigation above the events
			$yesterday = ES::date('-1 day')->format('Y-m-d');
			$tomorrow = ES::date('+1 day')->format('Y-m-d');

			$navigation->previous = $yesterday;
			$navigation->next = $tomorrow;

			$navigation->previousPageTitle = ES::date('-1 day')->format('COM_EASYSOCIAL_DATE_DMY');
			$navigation->nextPageTitle = ES::date('-1 day')->format('COM_EASYSOCIAL_DATE_DMY');
		}

		if ($activeDateFilter === 'tomorrow') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TOMORROW';

			// Get today's date
			$activeDate = ES::date('+1 day');
			$start = $activeDate->format('Y-m-d 00:00:00');
			$end = $activeDate->format('Y-m-d 23:59:59');

			$options['dateRange'] = true;
			$options['range-start'] = $start;
			$options['range-end'] = $end;
			$options['featured'] = 'all';

			$navigation->previous = ES::date()->format('Y-m-d');
			$navigation->next = ES::date('+2 days')->format('Y-m-d');

			$navigation->previousPageTitle = ES::date()->format('COM_EASYSOCIAL_DATE_DMY');
			$navigation->nextPageTitle = ES::date('+2 days')->format('COM_EASYSOCIAL_DATE_DMY');
		}

		if ($activeDateFilter === 'month') {

			$start = $parts[0] . '-' . $parts[1] . '-01';

			// Need to get the month's maximum day
			$monthDate = ES::date($start, false);
			$maxDay = $monthDate->format('t');

			$end = $parts[0] . '-' . $parts[1] . '-' . str_pad($maxDay, 2, '0', STR_PAD_LEFT);

			$activeDate = ES::date($dateString, false);

			$this->page->description(JText::sprintf('COM_ES_EVENTS_META_AVAILABLE_EVENTS', $activeDate->format('DATE_FORMAT_LC1')));

			$options['start-after'] = $start . ' 00:00:00';
			$options['start-before'] = $end . ' 23:59:59';

			// due to the timezone issue, for safety purposely, we will use the mid date of the month to get the next / previous months. #5553
			$previous = ES::date($dateString .'-15')->modify('-1 month');
			$next = ES::date($dateString .'-15')->modify('+1 month');

			// Set the navigation dates
			$navigation->previous = $previous->format('Y-m');
			$navigation->next = $next->format('Y-m');

			$navigation->previousPageTitle = $previous->format('Y-m');
			$navigation->nextPageTitle = $next->format('Y-m');

			// Should also include featured event
			$options['featured'] = 'all';

			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_MONTH';
			$pageTitle = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_MONTH') . ' - ' . $activeDate->format('DATE_FORMAT_LC3');
		}

		if ($activeDateFilter === 'year') {

			$currentYear = (int) ES::date()->format('Y');

			$yearDiff = $year - $currentYear;

			$startStr = 'first day of January +' . $yearDiff . ' year';
			$endStr = 'last day of December +' . $yearDiff . ' year';

			$activeDate = ES::date($startStr, false);

			$start = $activeDate->format('Y-m-d 00:00:00');
			$end = ES::date($endStr)->format('Y-m-d 23:59:59');

			$options['start-after'] = $start;
			$options['start-before'] = $end;

			$navigation->previous = $year - 1;
			$navigation->next = $year + 1;

			$navigation->previousPageTitle = $year - 1;
			$navigation->nextPageTitle = $year + 1;

			// Should also include featured event
			$options['featured'] = 'all';

			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_YEAR';
			$pageTitle = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_YEAR') . ' - ' . $year;
		}

		if ($activeDateFilter === 'normal') {
			// Depending on the input format.
			// Could be by year, year-month or year-month-day
			$now = ES::date();
			list($nowYMD, $nowHMS) = explode(' ', $now->toSql(true));

			// Get the input for the date
			$input = $this->input->get('date', '', 'string');

			// We need segments to be populated. If no input is passed, then it is today, and we use today as YMD then
			if (!$input) {
				$input = $nowYMD;
			}

			$activeDate = ES::date($input, false);

			$segments = explode('-', $input);

			$start = $nowYMD;
			$end = $nowYMD;

			// Depending on the amount of segments
			// 1 = filter by year
			// 2 = filter by month
			// 3 = filter by day

			$mode = count($segments);

			if ($mode === 1) {
				$start = $segments[0] . '-01-01';
				$end = $segments[0] . '-12-31';
			}

			if ($mode === 2) {
					$start = $segments[0] . '-' . $segments[1] . '-01';
					// Need to get the month's maximum day
					$monthDate = ES::date($start);
					$maxDay = $monthDate->format('t');

					$end = $segments[0] . '-' . $segments[1] . '-' . str_pad($maxDay, 2, '0', STR_PAD_LEFT);
			}

			if ($mode === 3) {
				$start = $segments[0] . '-' . $segments[1] . '-' . $segments[2];
				$end = $segments[0] . '-' . $segments[1] . '-' . $segments[2];
			}

			$options['dateRange'] = true;
			$options['range-start'] = $start . ' 00:00:00';
			$options['range-end'] = $end . ' 23:59:59';
			$options['featured'] = 'all';

			$previous = ES::date($input, false)->modify('-1 day');
			$next = ES::date($input, false)->modify('+1 day');

			// Set the navigation dates
			$navigation->previous = $previous->format('Y-m-d');
			$navigation->next = $next->format('Y-m-d');

			$navigation->previousPageTitle = $previous->format('COM_EASYSOCIAL_DATE_DMY');
			$navigation->nextPageTitle = $next->format('COM_EASYSOCIAL_DATE_DMY');

			$title = JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_DATE', ES::date($input, false)->format(JText::_('COM_EASYSOCIAL_DATE_DMY')));
		}

		if ($filter === 'week1') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING_1WEEK';

			$start = ES::date()->format('Y-m-d 00:00:00');
			$end = ES::date('+1 week')->format('Y-m-d 23:59:59');

			$options['start-after'] = $start;
			$options['start-before'] = $end;

			$showPastFilter = false;
		}

		// Filter by upcoming events (2 weeks)
		if ($filter === 'week2') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING_2WEEK';

			$now = ES::date();
			$week2 = ES::date($now->toUnix() + 60*60*24*14);

			$options['start-after'] = $now->toSql();
			$options['start-before'] = $week2->toSql();

			$showPastFilter = false;
		}

		// Check if there is any location data
		$userLocation = JFactory::getSession()->get('events.userlocation', array(), SOCIAL_SESSION_NAMESPACE);

		// Filter by nearby location
		if ($filter === 'nearby') {
			$defaultDistanceRadius = $distance;
			$distance = $this->input->get('distance', $defaultDistanceRadius, 'int');

			if (!empty($distance) && $distance != $defaultDistanceRadius) {
				$routeOptions['distance'] = $distance;
			}

			$title = JText::sprintf('COM_EASYSOCIAL_EVENTS_IN_DISTANCE_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));

			$hasLocation = !empty($userLocation) && !empty($userLocation['latitude']) && !empty($userLocation['longitude']);

			// If there is no location, then we need to delay the event retrieval process
			$delayed = !$hasLocation ? true : false;

			// We do not want to display sorting by default
			$showSorting = false;

			// include feature event into nearby filter
			$featuredOptions = array('featured' => true, 'state' => SOCIAL_STATE_PUBLISHED);

			if ($hasLocation) {
				$options['location'] = true;
				$options['distance'] = $distance;
				$options['latitude'] = $userLocation['latitude'];
				$options['longitude'] = $userLocation['longitude'];
				$options['range'] = '<=';

				$featuredOptions['location'] = true;
				$featuredOptions['distance'] = $distance;
				$featuredOptions['latitude'] = $userLocation['latitude'];
				$featuredOptions['longitude'] = $userLocation['longitude'];
				$featuredOptions['range'] = '<=';

				// We do not want to include past events here
				if (!$includePast) {
					$options['ongoing'] = true;
					$options['upcoming'] = true;

					$featuredOptions['ongoing'] = true;
					$featuredOptions['upcoming'] = true;
				}

				$showDistance = true;
				$showDistanceSorting = true;
			}

			if ($cluster && $cluster->getType() == SOCIAL_TYPE_PAGE) {
				$featuredOptions['page_id'] = $cluster->id;
			}

			if ($cluster && $cluster->getType() == SOCIAL_TYPE_GROUP) {
				$featuredOptions['group_id'] = $cluster->id;
			}

			$featuredEvents = $model->getEvents($featuredOptions, true);
		}

		// if viewer viewing another person event page, then we only want to fetch the event from person beign viewed
		if ($userid && $user->id) {
			$options['creator_uid'] = $user->id;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['type'] = 'all';
			$options['featured'] = 'all';
		}

		$events = array();

		// this setting is to hide repetitive events due to recurring events. #2837
		if ($filter == 'all' && !$activeCategory) {
			$showHideRepetitionFilter = true;

			if ($hideRepetition) {
				$options['nonrepetitive'] = true;
			}
		}

		// Get a list of events if this is not delayed?
		if (!$delayed) {
			$events = $model->getEvents($options);
		}

		// Get the pagination
		$pagination = $model->getPagination();

		if ($includePast) {
			// somehow the pagination did not has this includePast. we need to
			// add manually.
			$pagination->setVar('includePast', $includePast);
		}

		// Prepare the counters on the sidebar
		$sortingUrls = array();

		// If the user is viewing others' listing, we should respect that
		if (!$browseView && !$cluster) {
			$routeOptions['userid'] = $user->getAlias();

			$pagination->setVar('userid', $user->getAlias());
		}

		$routeCurrent = $routeOptions;

		// $current = ESR::events($routeOptions);
		if ($ordering && $ordering != 'start') {
			$routeCurrent['ordering'] = $ordering;
		}

		$sortingUrls['current'] = array();

		// generating current past link that user should see
		$pastSegments = array();

		if (!$includePast) {
			$pastSegments['includePast'] = 1;
		}

		if ($includePast && ($filter == 'mine' || $filter == 'createdbyme')) {
			$pastSegments['includePast'] = 0;
		}

		if ($hideRepetition) {
			$pastSegments['hideRepetition'] = 1;
		}

		// here we build the 'reverse' link
		$sortingUrls['current']['past'] = ESR::events(array_merge($routeCurrent, $pastSegments));

		// generating current past link that user should See
		$repetitionSegments = array();

		if (!$hideRepetition) {
			$repetitionSegments['hideRepetition'] = 1;
		}

		if ($includePast) {
			$repetitionSegments['includePast'] = 1;
		}

		// here we build the 'reverse' link
		$sortingUrls['current']['repetition'] = ESR::events(array_merge($routeCurrent, $repetitionSegments));

		// now let add the two options into the sorting urls.
		if ($includePast) {
			$routeOptions['includePast'] = 1;
		}

		if (!$includePast && ($filter == 'mine' || $filter == 'createdbyme')) {
			$routeOptions['includePast'] = 0;
		}

		if ($hideRepetition) {
			$routeOptions['hideRepetition'] = 1;
		}

		// We use start as key because order is always start by default, and it is the page default link
		$sortingUrls['start'] = ESR::events($routeOptions);
		$sortingUrls['recent'] = ESR::events(array_merge($routeOptions, array('ordering' => 'recent')));
		$sortingUrls['distance'] = ESR::events(array_merge($routeOptions, array('ordering' => 'distance')));

		if (!$delayed) {

			// // Only need to create the "order by created" link.
			// if ($showSorting) {
			// 	$link = ESR::events(array_merge($routeOptions, array('ordering' => 'recent')));
			// 	$sortingUrls['recent'] = array('nopast' => $link, 'repetition' => $link);
			// }

			// // Only need to create the "order by distance" link.
			// if ($showDistanceSorting) {
			// 	$sortingUrls['distance'] = array('nopast' => ESR::events(array_merge($routeOptions, array('ordering' => 'distance'))));
			// }

			// // If past filter is displayed on the page, then we need to generate the past links counter part
			// if ($showPastFilter) {
			// 	$sortingUrls['start']['past'] = ESR::events(array_merge($routeOptions, array('includePast' => 1)));

			// 	// Only need to create the "order by created" link.
			// 	if ($showSorting) {
			// 		$sortingUrls['recent']['past'] = ESR::events(array_merge($routeOptions, array('ordering' => 'recent', 'includePast' => 1)));
			// 	}

			// 	// Only need to create the "order by distance" link.
			// 	if ($showDistanceSorting) {
			// 		$sortingUrls['distance']['past'] = ESR::events(array_merge($routeOptions, array('ordering' => 'distance', 'includePast' => 1)));
			// 	}
			// }

			// if ($showHideRepetitionFilter) {

			// 	$sortingUrls['start']['norepetition'] = ESR::events(array_merge($routeOptions, array('hideRepetition' => 1)));

			// 	// Only need to create the "order by created" link.
			// 	if ($showSorting) {
			// 		$sortingUrls['recent']['norepetition'] = ESR::events(array_merge($routeOptions, array('ordering' => 'recent', 'hideRepetition' => 1)));
			// 	}
			// }
		}

		// dump($sortingUrls);

		$dateStrings = $this->input->get('date', '', 'string');

		// Add canonical for each of the different day.
		if (!empty($dateStrings)) {
			// normalize the canonical link
			$canonicalOptions = ES::normalizeCanonicalOptions(true);

			$this->page->canonical(ESR::events($canonicalOptions));
		}

		// set the page title for each of the filter and sorting.
		$this->page->title($title);

		$excludeFilterByDate = array('date', 'week1', 'week2', 'past');

		// In this section we only render the filter and sorting page title base on this getPageTitle function
		if (!in_array($filter, $excludeFilterByDate)) {
			$title = $helper->getPageTitle();
			$this->page->title($title);
		}

		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'));

		// Set the page title specific for the month and year
		if ($pageTitle && ($activeDateFilter == 'month' || $activeDateFilter == 'year')) {
			$this->page->title($pageTitle);
		}

		// Generate empty text here
		$emptyText = 'COM_EASYSOCIAL_EVENTS_EMPTY_' . strtoupper($filter);

		if ($cluster) {
			$emptyText = 'COM_ES_CLUSTER_EVENTS_EMPTY';
		}

		// If not browse view, we default the filter to 'created'
		if (!$browseView) {

			$filter = $filter != 'all' ? $filter : 'created';

			// If this is viewing profile's event, we display a different empty text
			$emptyText = 'COM_ES_EVENTS_EMPTY_' . strtoupper($filter);

			if (!$user->isViewer()) {
				$emptyText = 'COM_ES_EVENTS_USER_EMPTY_' . strtoupper($filter);
			}
		}

		$mobileFilter = false;

		// Mobile navigation filter
		if (ES::responsive()->isMobile()) {
			if (!$activeCategory && ($filter != 'date' && $filter != 'week1' && $filter != 'week2')) {
				$mobileFilter = 'discover';
			}

			if ($activeCategory) {
				$mobileFilter = 'categories';
			}

			if (!$activeCategory && ($filter == 'date' || $filter == 'week1' || $filter == 'week2')) {
				$mobileFilter = 'date';
			}
		}

		// determine whether this is coming from ajax call
		$this->set('fromAjax', false);

		$this->set('emptyText', $emptyText);
		$this->set('browseView', $browseView);
		$this->set('showMyEvents', $showMyEvents);
		$this->set('showPendingEvents', $showPendingEvents);
		$this->set('showTotalInvites', $showTotalInvites);
		$this->set('createUrl', $createUrl);

		// Event records
		$this->set('activeCategory', $activeCategory);
		$this->set('featuredEvents', $featuredEvents);
		$this->set('events', $events);
		$this->set('pagination', $pagination);
		$this->set('filtersLink', $filtersLink);

		// Set the date filters on sidebar
		$this->set('dateLinks', $dateLinks);
		$this->set('activeDateFilter', $activeDateFilter);
		$this->set('activeDate', $activeDate);

		// Date navigation
		$this->set('showDateNavigation', $showDateNavigation);
		$this->set('navigation', $navigation);

		// Other visiblity properties
		$this->set('showSorting', $showSorting);
		$this->set('showPastFilter', $showPastFilter);
		$this->set('showHideRepetitionFilter', $showHideRepetitionFilter);


		// Distance
		$this->set('distance', $distance);
		$this->set('distanceUnit', $this->config->get('general.location.proximity.unit'));
		$this->set('showDistance', $showDistance);
		$this->set('showDistanceSorting', $showDistanceSorting);

		// Sidebar items
		$this->set('filter', $filter);
		$this->set('mobileFilter', $mobileFilter);

		// Contents
		$this->set('title', $title);

		$this->set('userLocation', $userLocation);
		$this->set('sortingUrls', $sortingUrls);
		$this->set('ordering', $ordering);

		$this->set('hasLocation', $hasLocation);
		$this->set('includePast', $includePast);
		$this->set('hideRepetition', $hideRepetition);
		$this->set('delayed', $delayed);
		$this->set('activeUser', $user);
		$this->set('helper', $helper);

		return parent::display('site/events/default/default');
	}

	/**
	 * Displays the category selection for creating an event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function create()
	{
		// Check if events is enabled.
		$this->checkFeature();

		ES::setMeta();

		ES::requireLogin();
		ES::checkCompleteProfile();

		// Ensure that the user's acl is allowed to create events
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('events.create')) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Ensure that the user did not exceed the number of allowed events
		if (!$this->my->isSiteAdmin() && $this->my->getAccess()->intervalExceeded('events.limit', $this->my->id)) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_EXCEEDED_CREATE_EVENT_LIMIT'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::events(array(), false));
		}

		$categoryRouteBaseOptions = array('controller' => 'events' , 'task' => 'selectCategory');

		// Support group events
		$groupId = $this->input->getInt('group_id', 0);

		if (!empty($groupId)) {
			$group = ES::group($groupId);

			if (!$group->canCreateEvent()) {
				$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);

				return $this->redirect($group->getPermalink());
			}

			$categoryRouteBaseOptions['group_id'] = $groupId;

			$this->set('group', $group);
		}

		// Support page events
		$pageId = $this->input->getInt('page_id', 0);

		if (!empty($pageId)) {
			$page = ES::page($pageId);

			if (!$page->canCreateEvent()) {
				$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);

				return $this->redirect($page->getPermalink());
			}

			$categoryRouteBaseOptions['page_id'] = $pageId;

			$this->set('page', $page);
		}

		$this->set('categoryRouteBaseOptions', $categoryRouteBaseOptions);

		// Detect for an existing create event session.
		$session = JFactory::getSession();
		$sessionId = $session->getId();

		// Load up necessary model and tables.
		$stepSession = ES::table('StepSession');
		$sessionExist = $stepSession->load(array('session_id' => $sessionId, 'type' => SOCIAL_TYPE_EVENT));

		// If user doesn't have a record in stepSession yet, we need to create this.
		if (!$sessionExist) {

			$stepSession->set('session_id', $sessionId);
			$stepSession->set('created', ES::get('Date')->toMySQL());
			$stepSession->set('type', SOCIAL_TYPE_EVENT);

			if (!$stepSession->store()) {
				$this->setError($stepSession->getError());
				return false;
			}
		}

		// Generate the breadcrumb for this page
		$this->page->title(JText::_('COM_EASYSOCIAL_PAGE_TITLE_SELECT_EVENT_CATEGORY'));
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS') , ESR::events());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_SELECT_EVENT_CATEGORY'));

		// Get the list of categories
		$model = ES::model('EventCategories');
		$categories = $model->getCreatableCategories($this->my->getProfile()->id, true);

		$allCategories = $model->getCreatableCategories($this->my->getProfile()->id);

		// If there only one category, we want to skip the category selection page
		if (count($allCategories) == 1) {

			// For some reason the parent categories will be get restricted but the child category still can able to allow user create event
			if (!$categories) {
				$category = $allCategories[0];
			} else {
				$category = $categories[0];
			}

			// Do not allow user to create event in container category
			if ($category->container) {
				$this->setMessage(JText::_('COM_ES_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT_UNDER_CONTAINER_CATEGORY'), SOCIAL_MSG_ERROR);
				$this->info->set($this->getMessage());

				return $this->redirect(ESR::events(array(), false));
			}

			// to integrate with 3rd party cluster submission app.
			$canCreateInCategory = true;
			$arguments = array(&$this->my, &$category, &$canCreateInCategory);

			$dispatcher = ES::dispatcher();
			$dispatcher->trigger(SOCIAL_TYPE_EVENT, 'onEasySocialSelectCategory', $arguments);

			if (!$canCreateInCategory) {
				$this->setMessage(JText::sprintf('COM_ES_EVENTS_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), SOCIAL_MSG_ERROR);
				$this->info->set($this->getMessage());

				return $this->redirect(ESR::events(array(), false));
			}
			// integration end.

			// need to check if this clsuter category has creation limit based on user points or not.
			if (!$category->hasPointsToCreate($this->my->id)) {
				$requiredPoints = $category->getPointsToCreate($this->my->id);
				$this->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_INSUFFICIENT_POINTS', $requiredPoints), SOCIAL_MSG_ERROR);
				$this->info->set($this->getMessage());

				return $this->redirect(ESR::events(array(), false));
			}

			// Remove previous sessions data
			if ($stepSession->session_id) {

				// Check whether there are redundancy cluster (group and page)
				$values = $stepSession->values;
				$reg = ES::registry();
				$reg->load($values);

				// Only one cluster can be loaded at a time
				if ((!empty($pageId) && $reg->get('group_id')) || (!empty($groupId) && $reg->get('page_id'))) {
					$stepSession->setValue('page_id', '');
					$stepSession->setValue('group_id', '');
				}
			}

			// Store the category id into the session.
			$session->set('category_id', $category->id, SOCIAL_SESSION_NAMESPACE);

			// Set the current category id.
			$stepSession->uid = $category->id;
			$stepSession->type = SOCIAL_TYPE_EVENT;

			// When user accesses this page, the following will be the first page
			$stepSession->step = 1;

			// Add the first step into the accessible list.
			$stepSession->addStepAccess(1);

			// re-assign back those page or group id into the session values
			if (!empty($pageId)) {

				$stepSession->setValue('page_id', $pageId);

			} else if (!empty($groupId)) {

				$stepSession->setValue('group_id', $groupId);
			}

			// Let's save this into a temporary table to avoid missing data.
			$stepSession->store();

			$this->steps();
			return;
		}

		$this->set('categories', $categories);
		$this->set('backId', 0);
		$this->set('clusterType', SOCIAL_TYPE_EVENTS);
		$this->set('profileId', $this->my->getProfile()->id);

		parent::display('site/clusters/create/default');
	}

	/**
	 * Post action after selecting a category for creation to redirect to steps.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function selectCategory($container = null)
	{
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {

			// Support for group events
			// If there is a group id, we redirect back to the group instead
			$groupId = $this->input->getInt('group_id');
			if (!empty($groupId)) {
				$group = ES::group($groupId);

				return $this->redirect($group->getPermalink());
			}

			// Support for page event
			$pageId = $this->input->getInt('page_id');
			if (!empty($pageId)) {
				$page = ES::page($pageId);

				return $this->redirect($page->getPermalink());
			}

			if ($container) {
				return $this->redirect(ESR::events(array('layout' => 'create'), false));
			}

			return $this->redirect(ESR::events(array(), false));
		}

		$url = ESR::events(array('layout' => 'steps', 'step' => 1), false);

		return $this->redirect($url);
	}

	/**
	 * Displays the event creation steps.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function steps()
	{
		// Require user to be logged in
		ES::requireLogin();

		// Check for profile completeness
		ES::checkCompleteProfile();

		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('events.create')) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);

			$this->info->set($this->getMessage());

			return $this->redirect(ESR::dashboard());
		}

		if (!$this->my->isSiteAdmin() && $this->my->getAccess()->intervalExceeded('events.limit', $this->my->id)) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_EXCEEDED_CREATE_EVENT_LIMIT'), SOCIAL_MSG_ERROR);

			$this->info->set($this->getMessage());

			return $this->redirect(ESR::events());
		}

		$session = JFactory::getSession();

		$stepSession = ES::table('StepSession');
		$stepSession->load(array('session_id' => $session->getId(), 'type' => SOCIAL_TYPE_EVENT));

		if (empty($stepSession->step)) {
			$this->info->set(false, 'COM_EASYSOCIAL_EVENTS_UNABLE_TO_DETECT_CREATION_SESSION', SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::events());
		}

		$categoryId = $stepSession->uid;

		$category = ES::table('EventCategory');
		$category->load($categoryId);

		// Check if there is any workflow.
		if (!$category->getWorkflow()->id) {
			return $this->exception(JText::sprintf('COM_ES_NO_WORKFLOW_DETECTED', SOCIAL_TYPE_EVENT));
		}

		if (!$category->hasAccess('create', $this->my->getProfile()->id) && !$this->my->isSiteAdmin()) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::events());
		}

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_EVENT, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->setMessage(JText::sprintf('COM_ES_EVENTS_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::events(array(), false));
		}
		// integration end.

		// Get the step
		$stepIndex = $this->input->get('step', 1, 'int');

		$sequence = $category->getSequenceFromIndex($stepIndex , SOCIAL_EVENT_VIEW_REGISTRATION);

		if (empty($sequence)) {
			$this->info->set(false, JText::_('COM_ES_NO_VALID_CREATION_STEP'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::events(array('layout' => 'create')));
		}

		// We only check if step index is not 1
		if ($stepIndex > 1 && !$stepSession->hasStepAccess($stepIndex)) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_PLEASE_COMPLETE_PREVIOUS_STEP_FIRST'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::events(array('layout' => 'steps', 'step' => 1)));
		}

		if (!$category->isValidStep($sequence, SOCIAL_EVENT_VIEW_REGISTRATION)) {

			$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_INVALID_CREATION_STEP'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::events(array('layout' => 'steps', 'step' => 1)));
		}

		$stepSession->set('step', $stepIndex);
		$stepSession->store();

		$reg = ES::registry();
		$reg->load($stepSession->values);

		// Support for group events
		$groupId = $reg->get('group_id');
		$group = '';

		if (!empty($groupId)) {
			$group = ES::group($groupId);

			if (!$group->canCreateEvent()) {
				$this->info->set(false, JText::_('COM_EASYSOCIAL_GROUPS_EVENTS_NO_PERMISSION_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);
				return $this->redirect($group->getPermalink());
			}
		}

		// Support for page events
		$pageId = $reg->get('page_id');
		$page = '';

		if (!empty($pageId)) {
			$page = ES::page($pageId);

			if (!$page->canCreateEvent()) {
				$this->info->set(false, JText::_('COM_EASYSOCIAL_PAGES_EVENTS_NO_PERMISSION_TO_CREATE_EVENT'), SOCIAL_MSG_ERROR);
				return $this->redirect($page->getPermalink());
			}
		}

		$step = ES::table('FieldStep');
		$step->loadBySequence($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, $sequence);

		$totalSteps = $category->getTotalSteps();

		$errors = $stepSession->getErrors();

		$data = $stepSession->getValues();

		$fields = ES::fields();

		// Enforce privacy option to be false for events
		$fields->init(array('privacy' => false));

		$fieldsModel = ES::model('Fields');

		$customFields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'visible' => SOCIAL_EVENT_VIEW_REGISTRATION));

		$callback = array($fields->getHandler(), 'getOutput');

		$defaultAllDay = false;

		foreach ($customFields as $customField) {
			if ($customField->element == 'allday') {
				$defaultAllDay = $customField->default ? true : false;
			}
		}

		$data['event_allday'] = !isset($data['event_allday']) ? $defaultAllDay : $data['event_allday'];

		$args = array(&$data, &$stepSession, &$category);

		if (!empty($customFields)) {
			$fields->trigger('onRegister', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args, $callback);
		}

		$conditionalFields = array();

		foreach ($customFields as $field) {
			if ($field->isConditional()) {
				$conditionalFields[$field->id] = false;
			}
		}

		if ($conditionalFields) {
			$conditionalFields = json_encode($conditionalFields);
		} else {
			$conditionalFields = false;
		}

		$steps = $category->getSteps(SOCIAL_EVENT_VIEW_REGISTRATION);

		// Pass in the steps for this profile type.
		$steps = $category->getSteps(SOCIAL_GROUPS_VIEW_REGISTRATION);

		// Get the total steps
		$totalSteps = $category->getTotalSteps(SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Set the breadcrumbs and page title
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());

		if (!empty($groupId)) {
			$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_SELECT_EVENT_CATEGORY'), ESR::events(array('layout' => 'create', 'group_id' => $groupId)));
		} else if (!empty($pageId)) {
			$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_SELECT_EVENT_CATEGORY'), ESR::events(array('layout' => 'create', 'page_id' => $pageId)));
		} else {
			$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_SELECT_EVENT_CATEGORY'), ESR::events(array('layout' => 'create')));
		}

		$this->page->breadcrumb($step->get('title'));
		$this->page->title($step->get('title'));

		// Format the steps
		if ($steps) {
			$counter = 1;

			foreach ($steps as &$step) {
				$stepClass = $step->sequence == $sequence || $sequence > $step->sequence || $sequence == SOCIAL_REGISTER_COMPLETED_STEP ? ' active' : '';
				$stepClass .= $step->sequence < $sequence || $sequence == SOCIAL_REGISTER_COMPLETED_STEP ? $stepClass . ' past' : '';

				$step->css = $stepClass;
				$step->permalink = 'javascript:void(0);';

				if ($stepSession->hasStepAccess($step->sequence) && $step->sequence != $sequence) {
					$step->permalink = ESR::events(array('layout' => 'steps', 'step' => $counter));
				}

				$counter++;
			}
		}

		$totalSteps = $category->getTotalSteps(SOCIAL_EVENT_VIEW_REGISTRATION);

		$this->set('conditionalFields', $conditionalFields);
		$this->set('stepSession', $stepSession);
		$this->set('steps', $steps);
		$this->set('currentStep', $sequence);
		$this->set('currentIndex', $stepIndex);
		$this->set('totalSteps', $totalSteps);
		$this->set('step', $step);
		$this->set('fields', $customFields);
		$this->set('errors', $errors);
		$this->set('category', $category);
		$this->set('group', $group);
		$this->set('page', $page);

		parent::display('site/events/steps/default');
	}

	/**
	 * Post action for saving a step during event creation to redirect either to the next step or the complete page.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function saveStep($stepSession = null)
	{
		// Set any messages
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			if (!empty($stepSession)) {
				return $this->redirect(ESR::events(array('layout' => 'steps', 'step' => $stepSession->step), false));
			} else {
				return $this->redirect(ESR::events(array('layout' => 'steps', 'step' => 1), false));
			}
		}

		return $this->redirect(ESR::events(array('layout' => 'steps', 'step' => $stepSession->step), false));
	}

	/**
	 * Post action after completing an event creation to redirect either to the event listing for the event item.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function complete($event, $startDatetime)
	{
		// Recurring support
		// If no recurring data, then just redirect accordingly.
		// If event is in pending, then also redirect accordingly.
		if (empty($event->recurringData) || $event->isPending()) {
			$this->info->set($this->getMessage());

			if ($event->isPublished()) {
				return $this->redirect(ESR::events(array('layout' => 'item', 'id' => $event->getAlias()), false));
			}

			$options = array();
			if ($event->isClusterEvent()) {
				$cluster = $event->getCluster();

				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->getType();
			}

			return $this->redirect(ESR::events($options, false));
		}

		// If has recurring data, then we need to show the complete page to create all the necessary recurring events
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());
		$this->page->breadcrumb($event->getName(), $event->getPermalink());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_RECURRING_EVENT'));

		$this->page->title(JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_RECURRING_EVENT'));

		// Retrieve event start date time from the custom fields
		// Ensure that these date time should follow what user set from the custom field
		$startDatetime = ES::date($startDatetime, false);

		// Get the recurring schedule
		$schedule = ES::model('Events')->getRecurringSchedule(array(
			// 'eventStart' => $event->getEventStart(),
			'eventStart' => $startDatetime,
			'end' => $event->recurringData->end,
			'type' => $event->recurringData->type,
			'daily' => $event->recurringData->daily
		));

		// count total of recurring events
		$totalRecurringEvents = count($schedule);

		$this->set('schedule', $schedule);
		$this->set('event', $event);
		$this->set('totalRecurringEvents', $totalRecurringEvents);

		echo parent::display('site/events/create/recurring');
	}

	/**
	 * Displays the edit event page.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function edit($errors = null)
	{
		ES::requireLogin();

		ES::checkCompleteProfile();

		$info = $this->info;

		if (!empty($errors)) {
			$info->set($this->getMessage());
		}

		$my = ES::user();

		$eventid = $this->input->get('id', 0, 'int');

		$event = ES::event($eventid);

		if (empty($event) || empty($event->id)) {
			$info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID'), SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::events());
		}

		$helper = $this->getHelper('Edit');
		$event = $helper->getActiveEvent();

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		$guest = $event->getGuest($my->id);

		if (!$guest->isOwner() && !$guest->isAdmin() && !$my->isSiteAdmin()) {

			$info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_EDIT_EVENT'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::events());
		}

		ES::language()->loadAdmin();

		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());
		$this->page->breadcrumb($event->getName(), $event->getPermalink());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDIT_EVENT'));

		$this->page->title(JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EDIT_EVENT_TITLE', $event->getName()));

		$steps = $helper->getEventSteps();

		$fieldsModel = ES::model('Fields');

		$fieldsLib = ES::fields();

		// Enforce privacy to be false for events
		$fieldsLib->init(array('privacy' => false));

		$callback = array($fieldsLib->getHandler(), 'getOutput');

		$conditionalFields = array();

		foreach ($steps as &$step) {
			$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $event->id, 'dataType' => SOCIAL_TYPE_EVENT, 'visible' => SOCIAL_EVENT_VIEW_EDIT));

			if (!empty($step->fields)) {
				$post = $this->input->post->getArray();
				$args = array(&$post, &$event, $errors);
				$fieldsLib->trigger('onEdit', SOCIAL_TYPE_EVENT, $step->fields, $args, $callback);

				foreach ($step->fields as $field) {
					if ($field->isConditional()) {
						$conditionalFields[$field->id] = false;
					}
				}
			}
		}

		if ($conditionalFields) {
			$conditionalFields = json_encode($conditionalFields);
		} else {
			$conditionalFields = false;
		}

		// retrieve events's approval the rejected reason.
		$rejectedReasons = array();
		if ($event->isDraft()) {
			$rejectedReasons = $event->getRejectedReasons();
		}

		$this->set('conditionalFields', $conditionalFields);
		$this->set('event', $event);
		$this->set('steps', $steps);
		$this->set('rejectedReasons', $rejectedReasons);
		$this->set('activeStep', $activeStep);

		echo parent::display('site/events/edit/default');
	}

	/**
	 * Post action after updating an event to redirect to appropriately.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function update($event = null, $isNew = 0)
	{
		// Recurring support
		// If applies to all, we need to show a "progress update" page to update all childs through ajax.
		$applyAll = !empty($event) && $event->hasRecurringEvents() && $this->input->getInt('applyRecurring');

		// Check if need to create recurring event
		// $createRecurring = !empty($event->recurringData);
		$createRecurring = false;

		if (isset($event->recurringData) && $event->recurringData) {
			$dataR = $event->recurringData;
			if ($dataR->type != 'none') {
				$createRecurring = true;
			}
		}

		// If no apply, and no recurring create, then redirect accordingly.
		if (!$applyAll && !$createRecurring) {
			$this->info->set($this->getMessage());

			if ($this->hasErrors() || empty($event)) {
				return $this->redirect(ESR::events());
			}

			$url = '';
			if ($event->isPending()) {
				$url = ESR::events(array(), false);
			} else {
				$url = $event->getPermalink(false);
			}

			return $this->redirect($url);
		}

		// if this is on pending approval, do not process any recurring.
		if ($event->isPending()) {
			$url = ESR::events(array(), false);
			return $this->redirect($url);
		}

		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());
		$this->page->breadcrumb($event->getName(), $event->getPermalink());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDIT_EVENT'));

		$this->page->title(JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EDIT_EVENT_TITLE', $event->getName()));

		$post = $this->input->post->getArray();

		$json = ES::json();
		$data = array();

		$disallowed = array(ES::token(), 'option', 'task', 'controller');

		foreach ($post as $key => $value) {
			if (in_array($key, $disallowed)) {
				continue;
			}

			if (is_array($value)) {
				$value = $json->encode($value);
			}

			$data[$key] = $value;
		}

		// If it reached here mean it processing recurring event
		$data['hasRecurringFieldData'] = true;

		$string = $json->encode($data);

		$this->set('data', $string);
		$this->set('event', $event);

		$updateids = array();

		if ($applyAll) {
			$children = $event->getRecurringEvents();

			foreach ($children as $child) {
				$updateids[] = $child->id;
			}
		}

		$this->set('updateids', $json->encode($updateids));

		$schedule = array();

		if ($createRecurring) {
			// If there is recurring data, then we back up the post values and the recurring data in the the event params
			$clusterTable = ES::table('Cluster');
			$clusterTable->load($event->id);
			$eventParams = ES::makeObject($clusterTable->params);
			$eventParams->postdata = $data;
			$eventParams->recurringData = $event->recurringData;
			$clusterTable->params = ES::json()->encode($eventParams);
			$clusterTable->store();

			$eventDate = ES::date($event->getMeta('start'), false);

			// Get the recurring schedule
			$schedule = ES::model('Events')->getRecurringSchedule(array(
				'eventStart' => $eventDate,
				'end' => $event->recurringData->end,
				'type' => $event->recurringData->type,
				'daily' => $event->recurringData->daily
			));
		}

		$this->set('schedule', $json->encode($schedule));

		// isNew value have to use 1 or 0 instead of true or false
		$this->set('isNew', $isNew);

		echo parent::display('site/events/update/default');
	}

	/**
	 * Post process after the event avatar is removed
	 *
	 * @since   1.3
	 * @access  public
	 * @param   SocialEvent     The event object
	 */
	public function removeAvatar(SocialEvent $event)
	{
		$this->info->set($this->getMessage());

		$permalink = $event->getPermalink(false);

		$this->redirect($permalink);
	}

	/**
	 * Post processing after removing a guest from an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function removeGuest(SocialEvent $event)
	{
		$this->info->set($this->getMessage());

		// Get the members page url
		$redirect = $event->getAppPermalink('guests', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Displays the event item page.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function item()
	{
		$this->checkFeature();

		ES::checkCompleteProfile();

		ES::setMeta();

		$helper = $this->getHelper('Item');
		$event = $helper->getActiveEvent();

		// Set the default redirect url
		$redirect = ESR::events(array(), false);

		// Determines if the current user is a guest of this event
		$guest = $event->getGuest($this->my->id);

		// Check if the current logged in user blocked by the event creator or not.
		if ($this->my->id != $event->creator_uid && ES::user()->isBlockedBy($event->creator_uid)) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE'));
		}

		$event->hit();
		$event->renderHeaders();

		// Render start_time and end_time opengraph tag. #203
		$event->renderStartTimeHeader();

		// Set the page attributes
		$title = $event->getName();
		$event->renderPageTitle(null, 'events');

		if ($event->isClusterEvent()) {
			$cluster = $event->getCluster();

			$this->page->breadcrumb($cluster->getName(), $cluster->getPermalink());
		}

		if (!$event->isClusterEvent()) {
			$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_EVENTS', ESR::events());
		}

		// Display category in breadcrumbs
		$category = $event->getCategory();

		$this->page->breadcrumb($category->getTitle(), $category->getPermalink());

		$this->page->breadcrumb($title);

		// Check to see if the user could really see the event items
		if (!$event->canViewItem()) {
			return $this->restricted($event);
		}

		$hashtag = $this->input->get('tag', '', 'default');
		$hashtagAlias = $this->input->get('tag', '', 'default');

		$layout = $this->input->get('page', '', 'cmd');
		$appId = $this->input->get('appId', 0, 'int');

		// Retrieve user's filter preference.
		$defaultDisplay = $event->getFilterPreferences();

		// Get the default filter type to display
		if (!$appId && !$layout && !$hashtag) {

			$layout = $defaultDisplay->filter;
		}

		// Filter by event info
		if ($layout == 'info') {
			return $this->about($event);
		}

		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($event, $app);
		}

		$filterId = $this->input->get('filterId', 0, 'int');

		// Respect what is set by the user if needed to
		if ($defaultDisplay->filter == 'custom') {
			$filterId = $defaultDisplay->id;
		}

		$customFilter = '';

		// Load Stream filter table
		if ($defaultDisplay->filter == 'custom' && $filterId) {
			$customFilter = ES::table('StreamFilter');
			$customFilter->load($filterId);
		}

		// Get a list of filters
		$customFilters = $event->getFilters();

		// Initiate stream api
		$stream = ES::stream();

		$this->set('filterId', $filterId);

		// Add canonical link for event single page
		$this->page->canonical($event->getPermalink(false, true));


		$aboutPermalink = $helper->getAboutPermalink();

		// lets get the sticky posts 1st
		// Scheduled post shouldn't be allowed to be sticky. No need to search for this.
		if ($layout != 'scheduled') {
			$stickies = $stream->getStickies(array('clusterId' => $event->id, 'clusterType' => $event->cluster_type, 'limit' => 0));

			if ($stickies) {
				$stream->stickies = $stickies;
			}
		}

		$streamOptions = array('clusterId' => $event->id, 'clusterType' => $event->cluster_type, 'nosticky' => true);

		// Load the story
		$story = ES::story($event->cluster_type);
		$story->setCluster($event->id, $event->cluster_type);
		$story->showPrivacy(false);

		// Set storyPanelFilter
		$story->setFilter($layout);

		if (!empty($customFilter->id)) {
			$tags = $customFilter->getHashtag();
			$tags = explode(',', $tags);

			if ($tags) {
				$streamOptions['tag'] = $tags;

				$hashtagRule = $this->config->get('stream.filter.hashtag', '');
				if ($hashtagRule == 'and') {
					$streamOptions['matchAllTags'] = true;
				}

				$story->setHashtags($tags);
			}
		}

		if (!empty($hashtag)) {
			$this->set('hashtag', $hashtag);
			$story->setHashtags(array($hashtag));
			$streamOptions['tag'] = array($hashtag);
		}

		// Only allow users with access to post into this event
		if ($this->my->canPostClusterStory(SOCIAL_TYPE_EVENT, $event->id)) {
			$stream->story = $story;
		}

		if ($layout == 'scheduled') {
			$streamOptions['isScheduled'] = true;
			$streamOptions['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

			$displayOptions['disableActions'] = true;

			unset($stream->stickies);
		}

		$stream->get($streamOptions);

		// RSS
		$this->addRss($event->getPermalink());

		$model = ES::model('Stream') ;
		$appFilters = $model->getAppFilters(SOCIAL_TYPE_EVENT, $event->id);

		// Activity stream filter
		$streamFilter = ES::streamFilter(SOCIAL_TYPE_EVENT, $event->canCreateStreamFilter());
		$streamFilter->setAppFilters($appFilters);
		$streamFilter->setActiveFilter($filterId ? 'custom' : $layout, $filterId);
		$streamFilter->setCustomFilters($customFilters);
		$streamFilter->setActiveHashtag($hashtag);
		$streamFilter->setCluster($event);

		$this->set('streamFilter', $streamFilter);
		$this->set('aboutPermalink', $aboutPermalink);
		$this->set('guest', $guest);
		$this->set('event', $event);
		$this->set('appId', $appId);
		$this->set('layout', $layout);
		$this->set('title', $title);
		$this->set('customFilter', $customFilter);
		$this->set('stream', $stream);
		$this->set('rssLink', $this->rssLink);
		$this->set('stream', $stream);

		parent::display('site/events/item/default');
	}


	/**
	 * Displays a restricted page
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function restricted($node)
	{
		$label = 'COM_EASYSOCIAL_EVENTS_CLOSED_EVENT_INFO';
		$text = 'COM_EASYSOCIAL_EVENTS_CLOSED_EVENT_INFO_DESC';

		if ($node instanceof SocialUser) {
			$label = 'COM_EASYSOCIAL_EVENTS_RESTRICTED';
			$text = 'COM_EASYSOCIAL_EVENTS_RESTRICTED_USER_DESC';
		} else if (!$node instanceof SocialEvent) {
			if (!$node->id || ($node->isInviteOnly() && !$node->isMember())) {
				// We should not show the invite-only cluster if the current user is not a member of it
				return ES::raiseError(404, JText::_('COM_EASYSOCIAL_' . $node->cluster_type . '_NOT_FOUND'));
			}

			$label = 'COM_EASYSOCIAL_EVENTS_RESTRICTED';
			$text = 'COM_EASYSOCIAL_EVENTS_RESTRICTED_' . strtoupper($node->cluster_type) . '_DESC';
		}

		// Cluster types
		$this->set('node', $node);
		$this->set('label', $label);
		$this->set('text', $text);

		echo parent::display('site/events/restricted/default');
	}


	/**
	 * Displays the category item page.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function category()
	{
		// Check if events is enabled.
		$this->checkFeature();

		ES::setMeta();

		ES::checkCompleteProfile();

		$helper = $this->getHelper('Category');

		// Validate for the current group category id
		$category = $helper->getActiveEventCategory();

		ES::language()->loadAdmin();

		$this->page->title($category->get('title'));
		$this->page->description($category->getDescription());

		// Add breadcrumbs
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());
		$this->page->breadcrumb($category->get('title'));

		$this->page->canonical($category->getPermalink(true, true));

		// Retrieve a list of events under this category
		$events = $helper->getEvents();

		// Retrieve a list of feature event under this category
		$featuredEvents = $helper->getFeatureEvents();

		// Retrieve a list of random event category members
		$randomGuests = $helper->getRandomCategoryGuests();

		// Retrieve a list of random event albums
		$randomAlbums = $helper->getRandomCategoryAlbums();

		// Retrieve total of events
		$totalEvents = $helper->getTotalEvents();

		// Retrieve total of album under this category
		$totalAlbums = $helper->getTotalAlbums();

		// Retrieve stream item
		$stream = $helper->getStreamData();

		$this->set('events', $events);
		$this->set('featuredEvents', $featuredEvents);
		$this->set('randomGuests', $randomGuests);
		$this->set('randomAlbums', $randomAlbums);
		$this->set('totalEvents', $totalEvents);
		$this->set('totalAlbums', $totalAlbums);
		$this->set('stream', $stream);
		$this->set('category', $category);

		return parent::display('site/events/category/default');
	}

	/**
	 * Post action after saving a filter to redirect back to event item.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function saveFilter()
	{
		$eventId = $this->input->get('uid', '', 'default');
		$event = ES::event($eventId);

		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());
		}

		$this->redirect($event->getPermalink());
	}


	/**
	 * Allows viewer to view a file
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function preview()
	{
		// Check if events is enabled.
		$this->checkFeature();

		// Get the file id from the request
		$id = $this->input->get('fileid', 0, 'int');

		$file = ES::table('File');
		$file->load($id);

		if(!$file->id || !$id) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Load up the event
		$event = ES::event($file->uid);

		// Ensure that the user is really allowed to view this item
		if (!$event->canViewItem()) {
			// Throw error message here.
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$file->preview();
		exit;
	}

	/**
	 * Post action after a guest response from an event to redirect back to the event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function guestResponse()
	{
		$this->info->set($this->getMessage());

		$id = $this->input->getInt('id', 0);

		// Load the event
		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			return $this->redirect(ESR::events());
		}

		return $this->redirect($event->getPermalink());
	}

	/**
	 * Post process after a user is approved to attend the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function approveGuest($event = null)
	{
		$this->info->set($this->getMessage());

		// Default redirect
		$redirect = ESR::events(array('layout' => 'item', 'id' => $event->getAlias()), false);

		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a user is rejected to attend the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function rejectGuest($event = null)
	{
		$this->info->set($this->getMessage());

		// Default redirect
		$redirect = ESR::events(array('layout' => 'item', 'id' => $event->getAlias()), false);

		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post action after approving an event to redirect to the event item page.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function approveEvent($event = null)
	{
		$createRecurring = !empty($event) && $event->getParams()->exists('recurringData');

		if (!$createRecurring) {
			$this->info->set($this->getMessage());

			if ($this->hasErrors()) {
				return $this->redirect(ESR::events());
			}

			return $this->redirect($event->getPermalink());
		}

		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS'), ESR::events());
		$this->page->breadcrumb($event->getName(), $event->getPermalink());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_RECURRING_EVENT'));

		$this->page->title(JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_RECURRING_EVENT'));

		$params = $event->getParams();

		$eventDate = ES::date($event->getMeta('start'), false);

		// Get the recurring schedule
		$schedule = ES::model('Events')->getRecurringSchedule(array(
			'eventStart' => $eventDate,
			'end' => $params->get('recurringData')->end,
			'type' => $params->get('recurringData')->type,
			'daily' => $params->get('recurringData')->daily
		));

		$this->set('schedule', $schedule);

		$this->set('event', $event);

		echo parent::display('site/events/create/recurring');
	}

	/**
	 * Post action after rejecting an event to redirect to the event listing page.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function rejectEvent($event = null)
	{
		$this->info->set($this->getMessage());

		return $this->redirect(ESR::events());
	}

	/**
	 * Post processing after featuring an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setFeatured(SocialEvent $event)
	{
		$this->info->set($this->getMessage());

		$permalink = $event->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post processing after unfeaturing an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function removeFeatured(SocialEvent $event)
	{
		$this->info->set($this->getMessage());

		$permalink = $event->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post processing after unpublishing an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function unpublish($event)
	{
		$this->info->set($this->getMessage());

		// Get the redirection link
		$options = array();

		if ($event->isClusterEvent()) {
			$cluster = $event->getCluster();
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $cluster->getType();
		}

		$redirect = ESR::events($options, false);

		return $this->redirect($redirect);
	}

	/**
	 * Post processing after publishing an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function publish($event)
	{
		$this->info->set($this->getMessage());

		// Get the redirection link
		$options = array();

		if ($event->isClusterEvent()) {
			$cluster = $event->getCluster();
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $cluster->getType();
		}

		$redirect = ESR::events($options, false);

		return $this->redirect($redirect);
	}

	/**
	 * Post processing after deleting an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete($event)
	{
		$this->info->set($this->getMessage());

		// Get the redirection link
		$options = array();

		if ($event->isClusterEvent()) {
			$cluster = $event->getCluster();
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $cluster->getType();
		}

		$redirect = ESR::events($options, false);

		return $this->redirect($redirect);
	}

	public function itemAction($event = null)
	{
		// Check if events is enabled.
		$this->checkFeature();

		$this->info->set($this->getMessage());

		$action = $this->input->getString('action');
		$from = $this->input->getString('from');

		// If action is feature or unfeature, and the action is executed from the item page, then we redirect to the event item page.
		if (in_array($action, array('unfeature', 'feature')) && $from == 'item' && !empty($event)) {
			return $this->redirect($event->getPermalink());
		}

		// Else if the action is delete or unpublish, regardless of where is it executed from, we always go back to the listing page.
		return $this->redirect(ESR::events());
	}

	/**
	 * Allows view to download a file from an event
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function download()
	{
		// Currently only registered users are allowed to view a file.
		ES::requireLogin();

		// Get the file id from the request
		$fileId = $this->input->get('fileid', 0, 'int');

		$file = ES::table('File');
		$file->load($fileId);

		if (!$file->id || !$fileId) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Load up the event
		$event = ES::event($file->uid);

		// Ensure that the user can really view this event
		if (!$event->canViewItem()) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$file->download();
		exit;
	}

	public function updateRecurringSuccess()
	{
		ES::requireLogin();

		ES::checkToken();

		$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_UPDATED_RECURRING_SUCCESSFULLY'), SOCIAL_MSG_SUCCESS);

		// Delete session data if there is any
		$session = JFactory::getSession();
		$stepSession = ES::table('StepSession');
		$state = $stepSession->load(array('session_id' => $session->getId(), 'type' => SOCIAL_TYPE_EVENT));
		if ($state) {
			$stepSession->delete();
		}

		$id = $this->input->getInt('id');

		$event = ES::event($id);

		// Remove the post data from params
		$clusterTable = ES::table('Cluster');
		$clusterTable->load($event->id);
		$eventParams = ES::makeObject($clusterTable->params);
		unset($eventParams->postdata);
		$clusterTable->params = ES::json()->encode($eventParams);
		$clusterTable->store();

		$this->redirect($event->getPermalink());
	}

	public function createRecurringSuccess()
	{
		ES::requireLogin();
		ES::checkToken();

		// Delete session data if there is any
		$session = JFactory::getSession();
		$stepSession = ES::table('StepSession');
		$state = $stepSession->load(array('session_id' => $session->getId(), 'type' => SOCIAL_TYPE_EVENT));

		if ($state) {
			$stepSession->delete();
		}

		// Determine this process from which page
		$fromManagePage = $this->input->get('fromManagePage', false, 'bool');

		$id = $this->input->getInt('id');
		$event = ES::event($id);

		// Remove the post data from params
		$clusterTable = ES::table('Cluster');
		$clusterTable->load($event->id);

		$eventParams = ES::makeObject($clusterTable->params);
		unset($eventParams->postdata);

		$clusterTable->params = ES::json()->encode($eventParams);
		$clusterTable->store();

		$redirectionURL = $event->getPermalink();
		$message = JText::_('COM_EASYSOCIAL_EVENTS_CREATED_SUCCESSFULLY');

		if ($fromManagePage) {
			$redirectionURL = ESR::manage(array('layout' => 'clusters', 'filter' => 'event'));
			$message = JText::sprintf('COM_ES_MODERATION_EVENT_APPROVED', $event->getName());
		}

		$this->info->set(false, $message, SOCIAL_MSG_SUCCESS);

		$this->redirect($redirectionURL);
	}

	/**
	 * Post process after a user has invited a friend
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function invite($event)
	{
		// Set the necessary message
		$this->info->set($this->getMessage());

		$permalink = $event->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post processing after guest is promoted to admin
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function promoteGuest(SocialEvent $event)
	{
		$this->info->set($this->getMessage());

		// Get the members page url
		$redirect = $event->getAppPermalink('guests', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post processing after guest is demoted from admin role
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function demoteGuest(SocialEvent $event)
	{
		$this->info->set($this->getMessage());

		$redirect = $event->getAppPermalink('guests', false);
		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Retrieves stream contents
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getStream(SocialEvent $event, SocialStream $stream, $streamFilter)
	{
		 // RSS
		$this->addRss(ESR::events(array('id' => $event->getAlias(), 'layout' => 'item'), false));

		$theme = ES::themes();
		$theme->set('rssLink', $this->rssLink);
		$theme->set('stream', $stream);
		$theme->set('event', $event);
		$theme->set('customFilter', $streamFilter);

		$contents = $theme->output('site/events/item/feeds');

		$data = new stdClass();
		$data->contents = $contents;

		echo json_encode($data);exit;
	}
}
