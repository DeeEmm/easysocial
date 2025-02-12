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
	/**
	 * Post processing after filtering events
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function filter($filter, $events, $pagination, $activeCategory, $featuredEvents, $browseView, $activeUserId)
	{
		// Default properties
		$showDateNavigation = false;
		$showPastFilter = true;
		$showHideRepetitionFilter = false;
		$showSorting = true;
		$showDistanceSorting = false;
		$showDistance = false;
		$distance = $this->config->get('events.nearby.radius');
		$includePast = $this->input->get('includePast', null, 'int');
		$hideRepetition = $this->input->get('hideRepetition', 0, 'int');

		$ordering = $this->input->get('ordering', 'start', 'word');
		$delayed = false;
		$user = ES::user($activeUserId);

		// Determines the current filter being viewed
		$helper = ES::viewHelper('Events', 'List');

		$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS';


		$clusterId = $this->input->get('clusterId', '', 'string');
		$clusterType = false;


		// Set the route options so that filter can add extra parameters
		$routeOptions = array('option' => SOCIAL_COMPONENT_NAME, 'view' => 'events');

		$cluster = false;

		if ($clusterId) {
			$cluster = ES::cluster($clusterId);
			$clusterType = $cluster->getType();

			// if ($clusterType == SOCIAL_TYPE_PAGE) {
			// 	$clusterOption['page_id'] = $cluster->id;
			// }

			// if ($clusterType == SOCIAL_TYPE_GROUP) {
			// 	$clusterOption['group_id'] = $cluster->id;
			// }

			// $options = array_merge($options, $clusterOption);
		}



		if ($cluster) {
			$routeOptions['uid'] = $cluster->getAlias();
			$routeOptions['type'] = $cluster->cluster_type;
		}

		if ($filter != 'category') {
			$routeOptions['filter'] = $filter;
		}

		// We want to set a different title for non "all" or "category" filter
		if ($filter != 'all' && $filter != 'category' && $filter != 'date') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_' . strtoupper($filter);
		}

		if ($filter == 'week1') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING_1WEEK';
		}

		if ($filter == 'week2') {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING_2WEEK';
		}

		if ($filter == 'all') {
			$showHideRepetitionFilter = true;
		}

		// Date navigation
		$activeDateFilter = '';
		$activeDate = false;
		$navigation = new stdClass();

		// Filtering by date
		if ($filter == 'today' || $filter == 'tomorrow' || $filter == 'month' || $filter == 'year' || $filter == 'date') {

			$showSorting = false;
			$showPastFilter = false;
			$showDateNavigation = true;

			// If the filter is made from sidebar, we need to build the correct date string
			$dateString = '';

			if ($filter == 'today') {
				$dateString = ES::date()->format('Y-m-d');
			}

			if ($filter == 'tomorrow') {
				$dateString = ES::date('+1 day')->format('Y-m-d');
			}

			if ($filter == 'month') {
				$dateString = ES::date()->format('Y-m');
			}

			if ($filter == 'year') {
				$dateString = ES::date()->format('Y');
			}

			// Default to today
			$activeDateFilter = 'today';

			if (!$dateString) {
				$dateString = $this->input->get('date', '', 'string');
			}

			// The only way to determine if the user is filtering by today, tomorrow, month or year is to break up the "-"
			$parts = explode('-', $dateString);
			$totalParts = count($parts);

			// Try to see if it is tomorrow.
			if ($totalParts == 3) {
				$activeDate = ES::date($dateString, false);

				$tomorrow = ES::date('+1 day')->format('Y-m-d');
				$today = ES::date()->format('Y-m-d');

				if ($today == $dateString) {
					$activeDateFilter = 'today';
					$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TODAY';
				} else if ($tomorrow == $dateString) {
					$activeDateFilter = 'tomorrow';
					$title = 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TOMORROW';
				} else {
					$activeDateFilter = 'normal';
					$title = $activeDate->format(JText::_('COM_EASYSOCIAL_DATE_DMY'));
				}

				$previous = ES::date($dateString, false)->modify('-1 day');
				$next = ES::date($dateString, false)->modify('+1 day');

				// Set the navigation dates
				$navigation->previous = $previous->format('Y-m-d');
				$navigation->next = $next->format('Y-m-d');

				$navigation->previousPageTitle = $previous->format('COM_EASYSOCIAL_DATE_DMY');
				$navigation->nextPageTitle = $next->format('COM_EASYSOCIAL_DATE_DMY');
			}

			if ($totalParts == 2) {
				$activeDate = ES::date($dateString . '-01', false);
				$activeDateFilter = 'month';

				// due to the timezone issue, for safety purposely, we will use the mid date of the month to get the next / previous months. #5553
				$previous = ES::date($dateString .'-15')->modify('-1 month');
				$next = ES::date($dateString .'-15')->modify('+1 month');

				// Set the navigation dates
				$navigation->previous = $previous->format('Y-m');
				$navigation->next = $next->format('Y-m');

				$navigation->previousPageTitle = $previous->format('COM_EASYSOCIAL_DATE_MY');
				$navigation->nextPageTitle = $next->format('COM_EASYSOCIAL_DATE_MY');
			}

			if ($totalParts == 1) {
				$activeDate = ES::date($dateString . '-01-01', false);
				$activeDateFilter = 'year';

				$previous = ES::date($dateString . '-01-01')->modify('-1 year');
				$next = ES::date($dateString . '-01-01')->modify('+1 year');

				// Set the navigation dates
				$navigation->previous = $previous->format('Y');
				$navigation->next = $next->format('Y');

				$navigation->previousPageTitle = $previous->format('Y');
				$navigation->nextPageTitle = $next->format('Y');
			}
		}

		// Get the active category alias
		if ($activeCategory && $activeCategory->id) {
			$routeOptions['categoryid'] = $activeCategory->getAlias();
		}

		// Determines if the sorting should be visible
		$disallowedSorting = array('date', 'today', 'tomorrow', 'month', 'year');

		if (in_array($filter, $disallowedSorting)) {
			$showSorting = false;
		}

		// Determines if the past filter should be visible
		$disallowedPastFilters = array('today', 'tomorrow', 'month', 'year', 'past', 'ongoing', 'upcoming', 'week1', 'week2');

		if (in_array($filter, $disallowedPastFilters)) {
			$showPastFilter = false;
		}

		// Filter by near by events
		if ($filter === 'nearby') {
			$showSorting = false;
			$showDistance = true;
			$showDistanceSorting = true;

			// Since nearby filter doesnt show 'recent' option, we default it to 'start'
			if ($ordering == 'recent') {
				$ordering = 'start';
			}
			$defaultDistanceRadius = $distance;
			$distance = $this->input->get('distance', $defaultDistanceRadius, 'int');

			if (!empty($distance) && $distance != $defaultDistanceRadius) {
				$routeOptions['distance'] = $distance;
			}

			$title = JText::sprintf('COM_EASYSOCIAL_EVENTS_IN_DISTANCE_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));
		}

		$sortingUrls = array();

		// If the user is viewing others' listing, we should respect that
		if (!$browseView & !$cluster) {
			$routeOptions['userid'] = $user->getAlias();
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

		if ($showHideRepetitionFilter && $hideRepetition) {
			$pastSegments['hideRepetition'] = 1;
		}

		// here we build the 'reverse' link
		$sortingUrls['current']['past'] = ESR::events(array_merge($routeCurrent, $pastSegments), false);

		// generating current past link that user should See
		$repetitionSegments = array();

		if (!$hideRepetition) {
			$repetitionSegments['hideRepetition'] = 1;
		}

		if ($includePast) {
			$repetitionSegments['includePast'] = 1;
		}

		// here we build the 'reverse' link
		$sortingUrls['current']['repetition'] = ESR::events(array_merge($routeCurrent, $repetitionSegments), false);

		// now let add the two options into the sorting urls.
		if ($includePast) {
			$routeOptions['includePast'] = 1;
		}

		if (!$includePast && ($filter == 'mine' || $filter == 'createdbyme')) {
			$routeOptions['includePast'] = 0;
		}

		if ($showHideRepetitionFilter && $hideRepetition) {
			$routeOptions['hideRepetition'] = 1;
		}

		// We use start as key because order is always start by default, and it is the page default link
		$sortingUrls['start'] = ESR::events($routeOptions, false);
		$sortingUrls['recent'] = ESR::events(array_merge($routeOptions, array('ordering' => 'recent')), false);
		$sortingUrls['distance'] = ESR::events(array_merge($routeOptions, array('ordering' => 'distance')), false);


		// // We use start as key because order is always start by default, and it is the page default link
		// $sortingUrls['start'] = array('nopast' => ESR::events($routeOptions, false));

		// if (!$delayed) {

		// 	// Only need to create the "order by created" link.
		// 	if ($showSorting) {
		// 		$sortingUrls['created'] = array('nopast' => ESR::events(array_merge($routeOptions, array('ordering' => 'created')), false));
		// 	}

		// 	// Only need to create the "order by distance" link.
		// 	if ($showDistanceSorting) {
		// 		$sortingUrls['distance'] = array('nopast' => ESR::events(array_merge($routeOptions, array('ordering' => 'distance')), false));
		// 	}

		// 	// If past filter is displayed on the page, then we need to generate the past links counter part
		// 	if ($showPastFilter) {
		// 		$sortingUrls['start']['past'] = ESR::events(array_merge($routeOptions, array('includePast' => 1)), false);

		// 		// Only need to create the "order by created" link.
		// 		if ($showSorting) {
		// 			$sortingUrls['created']['past'] = ESR::events(array_merge($routeOptions, array('ordering' => 'created', 'includePast' => 1)), false);
		// 		}

		// 		// Only need to create the "order by distance" link.
		// 		if ($showDistanceSorting) {
		// 			$sortingUrls['distance']['past'] = ESR::events(array_merge($routeOptions, array('ordering' => 'distance', 'includePast' => 1)), false);
		// 		}
		// 	}
		// }

		// // For nearby filter, we want to get the "distance"
		// $distanceUrlWithPast = '';
		// $distanceUrlWithoutPast = '';

		// if ($filter == 'nearby') {
		// 	$distanceUrlWithoutPast = $sortingUrls['distance']['nopast'];
		// 	$distanceUrlWithPast = $sortingUrls['distance']['past'];
		// }


		$emptyText = 'COM_EASYSOCIAL_EVENTS_EMPTY_' . strtoupper($filter);

		// If this is viewing profile's event, we display a different empty text
		if (!$browseView) {
			$emptyText = 'COM_ES_EVENTS_EMPTY_' . strtoupper($filter);

			if (!$user->isViewer()) {
				$emptyText = 'COM_ES_EVENTS_USER_EMPTY_' . strtoupper($filter);
			}
		}

		$theme = ES::themes();
		$theme->set('showDistance', $showDistance);
		$theme->set('showHideRepetitionFilter', $showHideRepetitionFilter);
		$theme->set('showDistanceSorting', $showDistanceSorting);
		$theme->set('showPastFilter', $showPastFilter);
		$theme->set('showDateNavigation', $showDateNavigation);
		$theme->set('showSorting', $showSorting);
		$theme->set('delayed', $delayed);
		$theme->set('includePast', $includePast);
		$theme->set('hideRepetition', $hideRepetition);
		$theme->set('sortingUrls', $sortingUrls);
		$theme->set('ordering', $ordering);
		$theme->set('routeOptions', $routeOptions);
		$theme->set('browseView', $browseView);

		// Since ajax requests to filter only occurds when sidebar is enabled, we should enable by default
		$theme->set('showSidebar', true);

		// Date navigation
		$theme->set('activeDateFilter', $activeDateFilter);
		$theme->set('activeDate', $activeDate);
		$theme->set('navigation', $navigation);

		// Distance options
		$theme->set('distance', $distance);
		$theme->set('distanceUnit', $this->config->get('general.location.proximity.unit'));

		// Content attributes
		$theme->set('title', $title);

		$theme->set('filter', $filter);
		$theme->set('featuredEvents', $featuredEvents);
		$theme->set('events', $events);
		$theme->set('pagination', $pagination);
		$theme->set('activeCategory', $activeCategory);
		$theme->set('emptyText', $emptyText);
		$theme->set('helper', $helper);

		// determine whether this is coming from ajax call
		$theme->set('fromAjax', true);

		$namespace = 'wrapper';

		$sort = $this->input->get('sort', false, 'bool');

		if ($sort) {
			$namespace = 'items';
		}

		$output = $theme->output('site/events/default/' . $namespace);


		$recentSortingUrl = $sortingUrls['recent'];
		$startSortingUrl = $sortingUrls['start'];
		$distanceSortingUrl = $sortingUrls['distance'];
		$includePastUrl = $sortingUrls['current']['past'];
		$hideRepetitionUrl = $sortingUrls['current']['repetition'];

		return $this->ajax->resolve($output, $includePastUrl, $hideRepetitionUrl, $recentSortingUrl, $startSortingUrl, $distanceSortingUrl);
	}

	/**
	 * Displays confirmation to remove a user from an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmRemoveGuest()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the guest object.
		// this is cluster node id
		$id = $this->input->get('id', 0, 'int');

		$guest = ES::table('EventGuest');
		$guest->load($id);

		// Retrieve the guest user id under `uid` column from cluster node table
		$user = ES::user($guest->uid);
		$event = ES::event($guest->cluster_id);

		if (!$event->canRemoveGuest($guest->uid, $this->my->id)) {
			return $this->exception();
		}

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('return', $return);
		$theme->set('guest', $guest);
		$theme->set('user', $user);
		$contents = $theme->output('site/events/dialogs/guest.remove');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to approve user application
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function confirmApproveGuest()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Load the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		// Get the user id
		$ids = $this->input->get('userId', array(), 'array');

		$message = JText::sprintf('COM_ES_EVENTS_DIALOG_APPROVE_USER_REQUEST_CONFIRMATION', $event->getName());

		if ($ids && count($ids) > 1) {
			$message = JText::sprintf('COM_ES_EVENTS_DIALOG_APPROVE_USERS_REQUEST_CONFIRMATION', $event->getName());
		}

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('return', $return);
		$theme->set('event', $event);
		$theme->set('ids', $ids);
		$theme->set('message', $message);

		$contents = $theme->output('site/events/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to reject user application
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function confirmRejectGuest()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Load the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		// Get the user id
		$ids = $this->input->get('userId', array(), 'array');

		$message = JText::sprintf('COM_ES_EVENTS_DIALOG_REJECT_USER_REQUEST_CONTENT', $event->getName());

		if ($ids && count($ids) > 1) {
			$message = JText::sprintf('COM_ES_EVENTS_DIALOG_REJECT_USERS_REQUEST_CONTENT', $event->getName());
		}

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('return', $return);
		$theme->set('event', $event);
		$theme->set('ids', $ids);
		$theme->set('message', $message);

		$contents = $theme->output('site/events/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	public function removeGuest()
	{
		//Remove Event guest user.
		return $this->ajax->resolve();
	}

	public function rsvpFailed($errorMessage)
	{
		$theme = ES::themes();
		$theme->set('errorMessage', $errorMessage);
		$contents = $theme->output('site/events/dialogs/rsvp.failed');

		return $this->ajax->reject($contents);
	}

	/**
	 * Renders the popbox for an event
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function popbox()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$event = ES::event($id);

		if (!$event->canViewItem()) {
			$theme = ES::themes();
			$contents = $theme->output('site/events/popbox/restricted');
			return $this->ajax->resolve($contents);
		}

		$theme = ES::themes();
		$theme->set('event', $event);

		$contents = $theme->output('site/events/popbox/event');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a user rsvp to an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function rsvp(SocialEvent $event, SocialTableEventGuest $guest)
	{
		$button = ES::themes()->html('event.action', $event);
		$model = ES::model('Events');
		$counter = $model->getTotalCreatedJoinedEvents(null);

		return $this->ajax->resolve($button, $guest->getCurrentStateTitle(), $counter);
	}

	public function guestResponse($state = null)
	{
		return $this->ajax->resolve($state);
	}

	public function getFilter($event = null, $filter = null)
	{
		$theme = ES::themes();

		$theme->set('controller', 'events');
		$theme->set('filter', $filter);
		$theme->set('uid', $event->id);

		$contents = $theme->output('site/stream/form.edit');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after getting app contents
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAppContents(SocialEvent $event, $app)
	{
		// Load the library.
		$lib = ES::getInstance('Apps');
		$contents = $lib->renderView(SOCIAL_APPS_VIEW_TYPE_EMBED, 'events', $app, array('eventId' => $event->id));

		// Return the contents
		return $this->ajax->resolve($contents);
	}

	public function initInfo($steps = null)
	{
		return $this->ajax->resolve($steps);
	}

	/**
	 * Retrieves the event info
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getInfo(SocialEvent $event, $fields)
	{
		// Go through each of the steps and only pick the active one
		$contents = '';

		$theme = ES::themes();
		$theme->set('fields', $fields);

		$contents = $theme->output('site/events/item/about');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Responsible to show the invite friends dialog.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function invite()
	{
		ES::requireLogin();

		// Get the event
		$id = $this->input->get('id', '0', 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->isPublished()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
		}

		if (!$event->canViewItem()) {
			return $this->view->exception(JText::_('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT'));
		}

		$exclusion = array();

		$model = ES::model('Events');

		if ($event->isClusterEvent() && $event->isInviteOnly()) {
			$guests = $model->getGuests($event->id);

			if ($guests) {
				foreach ($guests as $guest) {
					$exclusion[] = $guest->uid;
				}
			}
		}

		if (!$event->isClusterEvent()) {
			$friends = $model->getFriendsInEvent($event->id, array('userId' => $this->my->id, 'published' => true, 'invited' => true));

			foreach ($friends as $friend) {
				$exclusion[] = $friend->id;
			}
		}

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('exclusion', $exclusion);
		$theme->set('event', $event);
		$theme->set('returnUrl', $returnUrl);


		$namespace = 'site/events/dialogs/invite';
		if ($event->isClusterEvent() && $event->isInviteOnly()) {

			$cluster = $event->getCluster();
			$theme->set('cluster', $cluster);

			$namespace .= '.members';
		}

		$contents = $theme->output($namespace);

		return $this->ajax->resolve($contents);
	}

	/**
	 * Callback after inviting friends.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function inviteFriends()
	{
		return $this->ajax->resolve(JText::_('COM_EASYSOCIAL_EVENTS_SUCCESSFULLY_INVITED_FRIENDS'));
	}

	/**
	 * Displays confirmation to feature events
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmUnfeature()
	{
		ES::requireLogin();

		// Get the event
		$id = $this->input->getInt('id', 0);
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		// Ensure that the user can really unpublish the event
		if (!$event->canFeature($this->my->id)) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('event', $event);
		$theme->set('returnUrl', $returnUrl);

		$output = $theme->output('site/events/dialogs/unfeature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to feature events
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmFeature()
	{
		ES::requireLogin();

		// Get the event
		$id = $this->input->getInt('id', 0);
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		// Ensure that the user can really unpublish the event
		if (!$event->canFeature($this->my->id)) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('event', $event);
		$theme->set('returnUrl', $returnUrl);

		$output = $theme->output('site/events/dialogs/feature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to unpublish an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmUnpublish()
	{
		ES::requireLogin();

		// Get the event
		$id = $this->input->getInt('id', 0);
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		$theme = ES::themes();
		$theme->set('event', $event);
		$output = $theme->output('site/events/dialogs/unpublish');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to publish an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmPublish()
	{
		ES::requireLogin();

		// Get the event
		$id = $this->input->getInt('id', 0);
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		$theme = ES::themes();
		$theme->set('event', $event);
		$output = $theme->output('site/events/dialogs/publish');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the delete event dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmDelete()
	{
		ES::requireLogin();

		$id = $this->input->getInt('id', 0);
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		// Ensure that the user can really unpublish the event
		if (!$event->canDelete($this->my->id)) {
			return $this->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		$theme = ES::themes();
		$theme->set('event', $event);

		$namespace = 'site/events/dialogs/delete';

		// Recurring support
		if ($event->isRecurringEvent() || $event->hasRecurringEvents()) {
			$namespace = 'site/events/dialogs/delete.recurring';
		}

		$contents = $theme->output($namespace);

		return $this->ajax->resolve($contents);
	}

	public function deleteFilter($eventId)
	{
		ES::requireLogin();
		$this->info->set($this->getMessage());

		$event = ES::event($eventId);
		$url = $event->getPermalink(false, false, 'item');

		return $this->ajax->redirect($url);
	}

	public function update($event)
	{
		if (empty($event)) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	public function edit($errors)
	{
		if (!empty($errors)) {
			return $this->ajax->reject($this->getMessage(), $errors);
		}

		return $this->ajax->resolve();
	}

	public function createRecurring()
	{
		return $this->ajax->resolve();
	}

	public function deleteRecurringDialog()
	{
		ES::requireLogin();

		// Might be calling this from backend
		ES::language()->loadSite();

		$id = $this->input->getInt('id', 0);

		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID'));
		}

		$guest = $event->getGuest($this->my->id);

		if (!$guest->isOwner() && !$this->my->isSiteAdmin()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT'));
		}

		$theme = ES::themes();
		$theme->set('event', $event);
		$contents = $theme->output('site/events/dialogs/recurringevent.delete');

		return $this->ajax->resolve($contents);
	}

	public function deleteRecurring()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Renders the calendar via ajax
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function renderCalendar()
	{
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
		$calendar->day_of_week = date('D', $calendar->first_day) ;

		// Once we know what day of the week it falls on, we know how many blank days occure before it. If the first day of the week is a Sunday then it would be zero
		$dayOfWeek = 0;

		switch ($calendar->day_of_week) {
			case "Sun":
				$dayOfWeek = 0;
				break;
			case "Mon":
				$dayOfWeek = 1;
				break;
			case "Tue":
				$dayOfWeek = 2;
				break;
			case "Wed":
				$dayOfWeek = 3;
				break;
			case "Thu":
				$dayOfWeek = 4;
				break;
			case "Fri":
				$dayOfWeek = 5;
				break;
			case "Sat":
				$dayOfWeek = 6;
				break;
		}

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

		// Create a date range to retrieve all the events
		$start = $year . '-' . $month . '-' . '01 00:00:00';
		$end = $year . '-' . $month . '-' . $calendar->days_in_month . ' 23:59:59';

		// Basic options
		$options = array();
		$options['state'] = SOCIAL_STATE_PUBLISHED;
		$options['type'] = $this->my->isSiteAdmin() ? 'all' : 'user';
		$options['ordering'] = 'start';
		$options['dateRange'] = true;
		$options['range-start'] = $start;
		$options['range-end'] = $end;

		$filter = $this->input->get('filter', 'all', 'default');
		$categoryId = $this->input->get('categoryId', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');

		// Filter by category
		if ($filter === 'category') {
			$category = ES::table('EventCategory');
			$category->load($categoryId);

			$activeCategory = $category;

			$options['category'] = $categoryId;

			// check if this category is a container or not
			if ($category->container) {

				// Get all child ids from this category
				$categoryModel = ES::model('ClusterCategory');
				$childs = $categoryModel->getChildCategories($category->id, array(), SOCIAL_TYPE_EVENT, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}
		}

		// Filter by clusters (group/page)
		if ($filter === 'cluster') {
			$cluster = ES::cluster($clusterId);

			$clusterType = $cluster->getType();

			if ($clusterType == SOCIAL_TYPE_PAGE) {
				$options['page_id'] = $cluster->id;
			} else if ($clusterType == SOCIAL_TYPE_GROUP) {
				$options['group_id'] = $cluster->id;
			}
		}

		$events = ES::model('Events')->getEvents($options);

		// This array groups the events by days
		$days = array();

		foreach($events as $event) {

			// Get the number of days this event being held
			$numberOfDays = $event->getNumberOfDays($start, $end);
			$dayStart = $event->getEventStart()->format('j', true);
			$monthStart = $event->getEventStart()->format('m', true);

			// Day start will always be 1 if start date is not within this month
			if ($monthStart != $month) {
				$dayStart = 1;
			}

			if ($numberOfDays > 1) {
				for ($i = 0; $i < $numberOfDays; $i++) {
					$days[$dayStart + $i][] = $event;
				}
			} else {
				$days[$dayStart][] = $event;
			}
		}

		// Compute the start of week
		$weekdays = ES::date()->getWeekdays($this->config->get('events.startofweek'));

		$theme = ES::themes();
		$theme->set('weekdays', $weekdays);
		$theme->set('calendar', $calendar);
		$theme->set('days', $days);
		$theme->set('events', $events);

		$today = ES::date()->format('Y-m-d', true);
		$tomorrow = ES::date()->modify('+1 day')->format('Y-m-d', true);

		$theme->set('today', $today);
		$theme->set('tomorrow', $tomorrow);
		$theme->set('filter', $filter);
		$theme->set('categoryId', $categoryId);
		$theme->set('clusterId', $clusterId);

		$output = $theme->output('site/events/default/calendar');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the calendar layout
	 *
	 * @since   2.2.3
	 * @access  public
	 */
	public function renderFullCalendar()
	{
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

		// Create a date range to retrieve all the events
		$start = $year . '-' . $month . '-' . '01 00:00:00';
		$end = $year . '-' . $month . '-' . $calendar->days_in_month . ' 23:59:59';

		// Basic options
		$options = array();
		$options['state'] = SOCIAL_STATE_PUBLISHED;
		$options['type'] = $this->my->isSiteAdmin() ? 'all' : 'user';
		$options['ordering'] = 'start';
		$options['dateRange'] = true;
		$options['range-start'] = $start;
		$options['range-end'] = $end;

		$filter = $this->input->get('filter', 'all', 'default');
		$categoryId = $this->input->get('categoryId', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');

		// Filter by category
		if ($filter === 'category') {
			$category = ES::table('EventCategory');
			$category->load($categoryId);

			$activeCategory = $category;

			$options['category'] = $categoryId;

			// check if this category is a container or not
			if ($category->container) {

				// Get all child ids from this category
				$categoryModel = ES::model('ClusterCategory');
				$childs = $categoryModel->getChildCategories($category->id, array(), SOCIAL_TYPE_EVENT, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}
		}

		// Filter by clusters (group/page)
		if ($filter === 'cluster') {
			$cluster = ES::cluster($clusterId);

			$clusterType = $cluster->getType();

			if ($clusterType == SOCIAL_TYPE_PAGE) {
				$options['page_id'] = $cluster->id;
			} else if ($clusterType == SOCIAL_TYPE_GROUP) {
				$options['group_id'] = $cluster->id;
			}
		}

		$model = ES::model('Events');
		$model->generateFilterOptions($filter, $options, $this->my->id);

		$events = $model->getEvents($options);

		// This array groups the events by days
		$days = array();

		foreach($events as $event) {

			// Get the number of days this event being held
			$numberOfDays = $event->getNumberOfDays($start, $end);
			$dayStart = $event->getEventStart()->format('j', true);
			$monthStart = $event->getEventStart()->format('m', true);

			// Day start will always be 1 if start date is not within this month
			if ($monthStart != $month) {
				$dayStart = 1;
			}

			if ($numberOfDays > 1) {
				for ($i = 0; $i < $numberOfDays; $i++) {
					$days[$dayStart + $i][] = $event;
				}
			} else {
				$days[$dayStart][] = $event;
			}
		}

		// Compute the start of week
		$weekdays = ES::date()->getWeekdays($this->config->get('events.startofweek'));

		$today = ES::date()->format('Y-m-d', true);
		$tomorrow = ES::date()->modify('+1 day')->format('Y-m-d', true);

		$theme = ES::themes();
		$theme->set('weekdays', $weekdays);
		$theme->set('calendar', $calendar);
		$theme->set('days', $days);
		$theme->set('events', $events);
		$theme->set('today', $today);
		$theme->set('tomorrow', $tomorrow);
		$theme->set('filter', $filter);
		$theme->set('categoryId', $categoryId);
		$theme->set('clusterId', $clusterId);

		$output = $theme->output('site/events/calendar/full');

		return $this->ajax->resolve($output);
	}

	/**
	 * Confirmation to remove an avatar
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmRemoveAvatar()
	{
		// Only registered users can do this
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('clusterType', 'events');
		$theme->set('id', $id);
		$contents = $theme->output('site/clusters/dialogs/remove.avatar');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display confirmation to promote guest
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmPromoteGuest()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the guest object.
		$uid = $this->input->get('uid', 0, 'int');
		$guest = ES::table('EventGuest');
		$guest->load($uid);

		// Get the user object.
		$user = ES::user($guest->uid);

		// Get the current user
		$my = ES::user();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		$return = $this->input->get('return', '', 'default');

		// Get the current user as a guest object in the same event
		$myGuest = ES::table('EventGuest');
		$myGuest->load(array('uid' => $my->id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $guest->cluster_id));

		$theme = ES::themes();

		if ($my->isSiteAdmin() || $myGuest->isAdmin()) {
			$theme->set('user', $user);
			$theme->set('uid', $uid);
			$theme->set('event', $event);
			$theme->set('return', $return);
			$theme->set('isMember', $guest->isMember());

			$contents = $theme->output('site/events/dialogs/promote');

			return $this->ajax->resolve($contents);
		}

		return $this->ajax->resolve($theme->output('site/events/dialogs/dialog.guest.error'));
	}

	/**
	 * Display confirmation to demote guest from admin role
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmDemoteGuest()
	{
		ES::requireLogin();

		// Get the guest object.
		$uid = $this->input->get('uid', 0, 'int');
		$guest = ES::table('EventGuest');
		$guest->load($uid);

		// Get the current user as a guest object in the same event
		$myGuest = ES::table('EventGuest');
		$myGuest->load(array('uid' => $this->my->id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $guest->cluster_id));

		$theme = ES::themes();

		if (!$this->my->isSiteAdmin() && !$myGuest->isAdmin()) {
			return $this->ajax->resolve($theme->output('site/events/dialogs/dialog.guest.error'));
		}

		// Get the user object of the guest
		$user = ES::user($guest->uid);

		// Get the event
		$eventId = $this->input->get('id', 0, 'int');
		$event = ES::event($eventId);

		$return = $this->input->get('return', '', 'default');

		$theme->set('return', $return);
		$theme->set('user', $user);
		$theme->set('uid', $uid);
		$theme->set('event', $event);

		$contents = $theme->output('site/events/dialogs/demote');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to take a picture
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function saveCamPicture()
	{
		// Ensure that the user is a valid user
		ES::requireLogin();

		$image = $this->input->get('image', '', 'default');
		$image = imagecreatefrompng($image);

		ob_start();
		imagepng($image, null, 9);
		$contents = ob_get_contents();
		ob_end_clean();

		// Store this in a temporary location
		$file = md5(ES::date()->toSql()) . '.png';
		$tmp = JPATH_ROOT . '/tmp/' . $file;
		$uri = JURI::root() . 'tmp/' . $file;

		JFile::write($tmp, $contents);

		$result = new stdClass();
		$result->file = $file;
		$result->url = $uri;

		return $this->ajax->resolve($result);
	}

	/**
	 * Allows caller to take a picture
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function takePicture()
	{
		// Ensure that the user is logged in
		ES::requireLogin();

		$theme = ES::themes();

		$uid = $this->input->get('uid', 0, 'int');

		$event = ES::event($uid);

		$theme->set('uid', $event->id);

		$output = $theme->output('site/avatar/dialogs/capture.picture');

		return $this->ajax->resolve($output);
	}

	/**
	 * Output for getting subcategories
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSubcategories($subcategories, $groupId, $pageId, $backId)
	{
		// Retrieve current logged in user profile type id
		$profileId = $this->my->getProfile()->id;

		$theme = ES::themes();
		$theme->set('backId', $backId);
		$theme->set('clusterType', SOCIAL_TYPE_EVENTS);
		$theme->set('profileId', $profileId);

		$html = '';

		$categoryRouteBaseOptions = array('controller' => 'events' , 'task' => 'selectCategory');

		if ($groupId) {
			$categoryRouteBaseOptions['group_id'] = $groupId;
		}

		if ($pageId) {
			$categoryRouteBaseOptions['page_id'] = $pageId;
		}

		foreach ($subcategories as $category) {
			$table = ES::table('ClusterCategory');
			$table->load($category->id);

			$theme->set('category', $table);
			$theme->set('categoryRouteBaseOptions', $categoryRouteBaseOptions);
			$html .= $theme->output('site/clusters/create/category.item');
		}

		return $this->ajax->resolve($html);
	}
}
