<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerEvents extends EasySocialController
{
	/**
	 * Retrieves a list of events available on the site with a given filter
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function filter()
	{
		ES::checkToken();

		// Load up the model
		$model = ES::model('Events');

		// Get data from query
		$filter = $this->input->get('type', 'all', 'string');
		$categoryId = $this->input->get('categoryId', 0, 'int');
		$ordering = $this->input->get('ordering', 'start', 'word');
		$includePast = $this->input->get('includePast', null, 'bool');
		$hideRepetition = $this->input->get('hideRepetition', false, 'bool');
		$activeUserId = $this->input->get('activeUserId', null, 'int');
		$browseView = $this->input->get('browseView', false, 'bool');

		$activeUser = ES::user($activeUserId);

		// Pagination
		$limit = $this->input->getInt('limit', ES::getLimit('events_limit'));
		$limitstart = $this->input->getInt('limitstart', 0);

		// Default options
		$featuredEvents = false;
		$activeCategory = false;
		$options = [
			'state' => SOCIAL_STATE_PUBLISHED,
			'ordering' => $ordering,
			'type' => $this->my->isSiteAdmin() ? 'all' : 'user',
			'featured' => false,
			'limit' => $limit,
			'limitstart' => $limitstart
		];

		if ($options['ordering'] === 'recent' || $includePast) {
			$options['direction'] = 'desc';
		}

		// Support for page id
		$clusterId = $this->input->get('clusterId', '', 'string');
		$clusterType = false;
		$clusterOption = [];
		$cluster = false;

		if ($clusterId) {
			$cluster = ES::cluster($clusterId);
			$clusterType = $cluster->getType();

			if ($clusterType == SOCIAL_TYPE_PAGE) {
				$clusterOption['page_id'] = $cluster->id;
			}

			if ($clusterType == SOCIAL_TYPE_GROUP) {
				$clusterOption['group_id'] = $cluster->id;
			}

			$options = array_merge($options, $clusterOption);
		}

		// Explicitly display past event by default for mine and createdbyme filter
		if (is_null($includePast) && ($filter == 'mine' || $filter == 'createdbyme')) {
			$includePast = true;
		}

		// We do not want to include past events
		if (!$includePast) {
			$options['ongoing'] = true;
			$options['upcoming'] = true;
		}

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
				$childs = $categoryModel->getChildCategories($category->id, [], SOCIAL_TYPE_EVENT, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}
		}

		if (($filter === 'all' || $filter === 'category')) {
			// Need to get featured events separately here
			$featuredOptions = [
				'state' => SOCIAL_STATE_PUBLISHED,
				'featured' => true,
				'type' => [SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE],
				'ordering' => 'start'
			];

			if ($clusterType == SOCIAL_TYPE_PAGE || $clusterType == SOCIAL_TYPE_GROUP) {
				$featuredOptions = array_merge($featuredOptions, $clusterOption);
			}

			if ($activeCategory) {
				$featuredOptions['category'] = $category->id;
			}

			$featuredEvents = $model->getEvents($featuredOptions);
		}

		if ($activeUserId && !$clusterType && !$browseView && $filter === 'created') {
			$options['creator_uid'] = $activeUserId;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['type'] = 'all';
			$options['featured'] = 'all';
		}

		// Generate other common filter options
		$model->generateFilterOptions($filter, $options, $this->my->id);

		// Filter events by participation
		if ($filter === 'participated' && !$browseView) {
			$options['creator_uid'] = $activeUserId;
			$options['creator_type'] = SOCIAL_TYPE_USER;
			$options['creator_join'] = true;
			$options['type'] = 'all';
			$options['featured'] = 'all';
		}

		if ($filter === 'today') {
			// Get today's date
			$activeDate = ES::date();
			$start = $activeDate->format('Y-m-d 00:00:01');
			$end = $activeDate->format('Y-m-d 23:59:59');

			$options['dateRange'] = true;
			$options['range-start'] = $start;
			$options['range-end'] = $end;
			$options['featured'] = 'all';

			// Regardless of the include past option or not, we should just display the events since they are filtered by today
			if (isset($options['ongoing'])) {
				unset($options['ongoing']);
			}

			if (isset($options['upcoming'])) {
				unset($options['upcoming']);
			}

		}

		if ($filter === 'tomorrow') {
			// Get today's date
			$activeDate = ES::date('+1 day');
			$start = $activeDate->format('Y-m-d 00:00:00');
			$end = $activeDate->format('Y-m-d 23:59:59');

			$options['dateRange'] = true;
			$options['range-start'] = $start;
			$options['range-end'] = $end;
			$options['featured'] = 'all';
		}

		if ($filter === 'month') {
			$activeDate = ES::date('first day of this month');
			$start = $activeDate->format('Y-m-d 00:00:00');
			$end = ES::date('last day of this month')->format('Y-m-d 23:59:59');

			$options['start-after'] = $start . ' 00:00:00';
			$options['start-before'] = $end . ' 23:59:59';

			$currentDate = $activeDate->format('Y-m');

			// we should include past event as well for month and year filter
			if (isset($options['ongoing'])) {
				unset($options['ongoing']);
			}

			if (isset($options['upcoming'])) {
				unset($options['upcoming']);
			}

			$options['featured'] = 'all';
		}

		if ($filter === 'year') {
			$activeDate = ES::date('first day of January');
			$start = $activeDate->format('Y-m-d 00:00:00');
			$end = ES::date('last day of December')->format('Y-m-d 23:59:59');

			$options['start-after'] = $start;
			$options['start-before'] = $end;

			$currentDate = $activeDate->format('Y');

			// we should include past event as well for month and year filter
			if (isset($options['ongoing'])) {
				unset($options['ongoing']);
			}

			if (isset($options['upcoming'])) {
				unset($options['upcoming']);
			}

			$options['featured'] = 'all';
		}

		// Filter by dates
		if ($filter === 'date') {

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

			$segments = explode('-', $input);

			$start = $nowYMD;
			$end = $nowYMD;

			// Depending on the amount of segments
			// 1 = filter by year
			// 2 = filter by month
			// 3 = filter by day

			$mode = count($segments);

			if ($mode == 1) {
				$start = $segments[0] . '-01-01';
				$end = $segments[0] . '-12-31';
			}

			if ($mode == 2) {
				$start = $segments[0] . '-' . $segments[1] . '-01';
				// Need to get the month's maximum day
				$monthDate = ES::date($start, false);
				$maxDay = $monthDate->format('t');

				$end = $segments[0] . '-' . $segments[1] . '-' . str_pad($maxDay, 2, '0', STR_PAD_LEFT);
			}

			if ($mode == 3) {
				$start = $segments[0] . '-' . $segments[1] . '-' . $segments[2];
				$end = $segments[0] . '-' . $segments[1] . '-' . $segments[2];
			}

			$options['dateRange'] = true;
			$options['range-start'] = $start . ' 00:00:00';
			$options['range-end'] = $end . ' 23:59:59';
			$options['featured'] = 'all';

			// Regardless of the include past option or not, we should just display the events since they are filtered by date
			if (isset($options['ongoing'])) {
				unset($options['ongoing']);
			}

			if (isset($options['upcoming'])) {
				unset($options['upcoming']);
			}
		}

		if ($filter === 'week1') {
			$now = ES::date();
			$week1 = ES::date($now->toUnix() + 60*60*24*7);

			$options['start-after'] = $now->toSql();
			$options['start-before'] = $week1->toSql();
		}

		if ($filter === 'week2') {
			$now = ES::date();
			$week2 = ES::date($now->toUnix() + 60*60*24*14);

			$options['start-after'] = $now->toSql();
			$options['start-before'] = $week2->toSql();
		}

		$distance = $this->config->get('events.nearby.radius');

		// Filter by nearby events
		if ($filter === 'nearby') {
			$distance = $this->input->get('distance', $distance, 'int');

			$options['location'] = true;
			$options['distance'] = $distance;
			$options['latitude'] = $this->input->getString('latitude');
			$options['longitude'] = $this->input->getString('longitude');
			$options['range'] = '<=';

			$session = JFactory::getSession();

			$userLocation = $session->get('events.userlocation', array(), SOCIAL_SESSION_NAMESPACE);

			$hasLocation = !empty($userLocation) && !empty($userLocation['latitude']) && !empty($userLocation['longitude']);

			if (!$hasLocation) {
				$userLocation['latitude'] = $options['latitude'];
				$userLocation['longitude'] = $options['longitude'];

				$session->set('events.userlocation', $userLocation, SOCIAL_SESSION_NAMESPACE);
			}

			// Need to get featured events separately here
			$featuredOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'featured' => true);

			if ($clusterType == SOCIAL_TYPE_PAGE || $clusterType == SOCIAL_TYPE_GROUP) {
				$featuredOptions = array_merge($featuredOptions, $clusterOption);
			}

			$featuredOptions['location'] = true;
			$featuredOptions['distance'] = $distance;
			$featuredOptions['latitude'] = $this->input->getString('latitude');
			$featuredOptions['longitude'] = $this->input->getString('longitude');
			$featuredOptions['range'] = '<=';

			$featuredEvents = $model->getEvents($featuredOptions);
		}

		if ($filter === 'all' && $hideRepetition) {
			$options['nonrepetitive'] = true;
		}


		$events = $model->getEvents($options);
		$pagination = $model->getPagination();

		// We should not include distance if filter by date
		$includeDistance = true;

		if (in_array($filter, array('date', 'month', 'year'))) {
			$includeDistance = false;
		}

		if (!$browseView && !$cluster) {
			$pagination->setVar('userid', $activeUser->getAlias());
		}

		if ($cluster) {
			$pagination->setVar('uid', $cluster->getAlias());
			$pagination->setVar('type', $cluster->cluster_type);
		}

		// Set the pagination if needed
		$Itemid = $this->input->getInt('Itemid', FRoute::getItemId('events'));
		$pagination->setVar('Itemid',$Itemid);
		$pagination->setVar('view', 'events');


		if ($filter === 'year' || $filter === 'month' || $filter === 'today' || $filter === 'tomorrow') {

			$dateToUse = ES::date()->format('Y-m-d'); // default to today

			$pagination->setVar('filter', 'date');

			if ($filter == 'year') {
				$dateToUse = $activeDate->format('Y');
			}

			if ($filter == 'month') {
				$dateToUse = $activeDate->format('Y-m');
			}

			if ($filter == 'tomorrow') {
				$dateToUse = ES::date('+1 day')->format('Y-m-d');
			}

			$pagination->setVar('filter', 'date');
			$pagination->setVar('date', $dateToUse);

		} else {

			// Router already include categories filter if category id is present
			if (!$activeCategory && $filter != 'category') {
				$pagination->setVar('filter', $filter);
			}

			$dateInput = $this->input->get('date', '', 'default');
			if ($dateInput && $dateInput != 'false') {
				$pagination->setVar('date', $dateInput);
			}
		}

		if ($includePast) {
			$pagination->setVar('includePast', $includePast);
		}

		if (!$includePast && ($filter == 'mine' || $filter == 'createdbyme')) {
			$pagination->setVar('includePast', 0);
		}

		if ($hideRepetition) {
			$pagination->setVar('hideRepetition', $hideRepetition);
		}

		if ($ordering != 'start') {
			$pagination->setVar('ordering', $ordering);
		}

		if ($activeCategory) {
			$pagination->setVar('categoryid', $activeCategory->getAlias());
		}

		if ($filter === 'nearby') {
			$distance = $this->input->get('distance', $distance, 'int');

			$pagination->setVar('distance', $distance);
		}

		return $this->view->call(__FUNCTION__, $filter, $events, $pagination, $activeCategory, $featuredEvents, $browseView, $activeUserId);
	}

	/**
	 * Allows caller to invite other users
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function sendInvites()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$event = ES::event($id);

		// We should not allow anyone to send invites if it has been disabled.
		if (!$event->canInvite()) {
			die();
		}

		// Get the list of emails
		$emails = $this->input->get('emails', '', 'html');

		if (!$emails) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_PLEASE_ENTER_EMAILS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $event);
		}

		$emails = explode("\n", $emails);

		// Get the message
		$message = $this->input->get('message', '', 'default');

		$model = ES::model('Events');

		foreach ($emails as $email) {

			// Ensure that the e-mail is valid
			$email = trim($email);
			$valid = JMailHelper::isEmailAddress($email);

			if (!$valid) {
				$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_INVALID_EMAIL', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__, $event);
			}

			$table = ES::table('FriendInvite');

			// Check if this email has been invited by this user before
			$table->load(array('email' => $email, 'user_id' => $this->my->id, 'utype' => SOCIAL_TYPE_EVENT, 'uid' => $event->id));

			// Skip this if the user has already been invited before.
			if ($table->id) {
				continue;
			}

			// Check if the e-mail is already registered on the site
			$exists = $model->isEmailExists($email, $event->id);

			if ($exists) {
				$this->view->setMessage(JText::sprintf('COM_ES_FRIENDS_INVITE_EMAIL_EXISTS_IN_EVENT', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__, $event);
			}

			$table->email = $email;
			$table->user_id = $this->my->id;
			$table->message = $message;
			$table->utype = SOCIAL_TYPE_EVENT;
			$table->uid = $event->id;

			$table->store();
		}

		$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_SENT_INVITATIONS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Occurs when user tries to select an event category
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function selectCategory()
	{
		// Ensure that the user is logged in
		ES::requireLogin();

		// Ensure that the user really has access to create event
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('events.create')) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Ensure that the user did not exceed his limits
		if (!$this->my->isSiteAdmin() && $this->my->getAccess()->intervalExceeded('events.limit', $this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EXCEEDED_CREATE_EVENT_LIMIT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Get the category id
		$id = $this->input->get('category_id', 0, 'int');

		// Try to load the category
		$category = ES::table('EventCategory');
		$category->load($id);

		if (!$category->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_CATEGORY_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check for category container
		if ($category->container) {
			$this->view->setMessage('COM_ES_EVENTS_CONTAINER_NOT_ALLOWED', ES_ERROR);
			return $this->view->call(__FUNCTION__, true);
		}

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_EVENT, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->view->setMessage(JText::sprintf('COM_ES_EVENTS_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}
		// integration end.

		// Check if the category has point restrictions
		if (!$category->hasPointsToCreate($this->my->id)) {
			$requiredPoints = $category->getPointsToCreate($this->my->id);
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_INSUFFICIENT_POINTS', $requiredPoints), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$session = JFactory::getSession();

		// Differentiate the id between group event and page event if exists
		$sessionId = $session->getId();

		// Get the group id to see if this is coming from group event creation
		$groupId = $this->input->getInt('group_id');

		// Check the group access for event creation
		if (!empty($groupId)) {
			$group = ES::group($groupId);

			if (!$group->canCreateEvent()) {
				$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT', ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}
		}

		// Get the page id to see if this is coming from page event creation
		$pageId = $this->input->getInt('page_id');

		// Check the page access for event creation
		if (!empty($pageId)) {
			$page = ES::page($pageId);

			if (!$page->canCreateEvent()) {
				$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT', ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}
		}

		$session->set('category_id', $category->id, SOCIAL_SESSION_NAMESPACE);

		$stepSession = ES::table('StepSession');
		$stepSession->load(array('session_id' => $sessionId, 'type' => SOCIAL_TYPE_EVENT));

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

		$stepSession->session_id = $sessionId;
		$stepSession->uid = $category->id;
		$stepSession->type = SOCIAL_TYPE_EVENT;

		$stepSession->set('step', 1);
		$stepSession->addStepAccess(1);

		if (!empty($pageId)) {
			$page = ES::page($pageId);

			if (!$page->canCreateEvent()){
				$this->view->setError(JText::_('COM_EASYSOCIAL_PAGES_EVENTS_NO_PERMISSION_TO_CREATE_EVENT'));
				return $this->view->call(__FUNCTION__);
			}

			$stepSession->setValue('page_id', $pageId);
		} else if (!empty($groupId)) {
			$group = ES::group($groupId);

			if (!$group->canCreateEvent()) {
				$this->view->setError(JText::_('COM_EASYSOCIAL_GROUPS_EVENTS_NO_PERMISSION_TO_CREATE_EVENT'));
				return $this->view->call(__FUNCTION__);
			}

			$stepSession->setValue('group_id', $groupId);
		} else if (!empty($stepSession->values)) {
			// Check if there is a group/page id set in the session, if yes then remove it
			$value = ES::makeObject($stepSession->values);

			unset($value->group_id);
			unset($value->page_id);

			$stepSession->values = ES::json()->encode($value);
		}

		$stepSession->store();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Whenever user clicks on the next step during event creation
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function saveStep()
	{
		ES::requireLogin();
		ES::checkToken();

		// Check if the user is allowed to create events
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('events.create')) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_CREATE_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check if the user exceeds the limit
		if (!$this->my->isSiteAdmin() && $this->my->getAccess()->intervalExceeded('events.limit', $this->my->id) ) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EXCEEDED_CREATE_EVENT_LIMIT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Get the session data
		$session = JFactory::getSession();
		$stepSession = ES::table('StepSession');
		$stepSession->load(array('session_id' => $session->getId(), 'type' => SOCIAL_TYPE_EVENT));

		if (empty($stepSession->step)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_UNABLE_TO_DETECT_CREATION_SESSION', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$category = ES::table('EventCategory');
		$category->load($stepSession->uid);
		$sequence = $category->getSequenceFromIndex($stepSession->step, SOCIAL_EVENT_VIEW_REGISTRATION);

		if (empty($sequence)) {
			$this->view->setMessage('COM_ES_NO_VALID_CREATION_STEP', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Load the steps and fields
		$step = ES::table('FieldStep');
		$step->load(array('workflow_id' => $category->getWorkflow()->id, 'type' => SOCIAL_TYPE_CLUSTERS, 'sequence' => $sequence));

		$registry = ES::registry();
		$registry->load($stepSession->values);

		// Get the fields
		$fieldsModel  = ES::model('Fields');
		$customFields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'visible' => SOCIAL_EVENT_VIEW_REGISTRATION));

		// Get from request
		$post  = $this->input->post->getArray();
		$token = ES::token();

		foreach ($post as $key => $value) {
			if ($key == $token) {
				continue;
			}

			if (is_array($value)) {
				$value = json_encode($value);
			}

			$registry->set($key, $value);
		}

		$data = $registry->toArray();

		// Retrieve the recurring field id
		$recurringFieldId = $fieldsModel->getSpecificFieldId($category->getWorkflow()->id, SOCIAL_FIELDS_GROUP_EVENT, 'recurring');
		$hasRecurringFieldData = false;

		// Determine whether this workflow field got recurring field or not
		if ($recurringFieldId) {
			$recurringFieldPrefix = SOCIAL_FIELDS_PREFIX . $recurringFieldId;

			if (isset($data[$recurringFieldPrefix]) && $data[$recurringFieldPrefix]) {

				$recurringObj = json_decode($data[$recurringFieldPrefix]);
				$hasRecurringFieldData = true;

				// If the recurring field set to none
				if ($recurringObj->type == 'none') {
					$hasRecurringFieldData = false;
				}
			}
		}

		$data['hasRecurringFieldData'] = $hasRecurringFieldData;

		// Retrieve event start date time from the custom fields
		$startDatetime = isset($data['startDatetime']) ? $data['startDatetime'] : '00-00-00 00:00:00';

		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession);

		// Load up the fields library so we can trigger the field apps
		$fieldsLib = ES::fields();

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession);

		// some data need to be retrieved in raw value before store into the step session table
		$fieldsLib->trigger('onFormatData', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args);

		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession);

		$callback  = array($fieldsLib->getHandler(), 'validate');

		$errors = $fieldsLib->trigger('onRegisterValidate', SOCIAL_FIELDS_GROUP_EVENT, $customFields, $args, $callback);

		$stepSession->values = json_encode($data);

		$stepSession->store();

		if (!empty($errors)) {
			$stepSession->setErrors($errors);

			$stepSession->store();

			$this->view->setMessage('COM_ES_ERRORS_IN_FORM', ES_ERROR);

			return $this->view->call(__FUNCTION__, $stepSession);
		}

		$completed = $step->isFinalStep(SOCIAL_EVENT_VIEW_REGISTRATION);

		$stepSession->created = ES::date()->toSql();

		$nextStep = $step->getNextSequence(SOCIAL_EVENT_VIEW_REGISTRATION);

		if ($nextStep) {
			$nextIndex = $stepSession->step + 1;
			$stepSession->step = $nextIndex;
			$stepSession->addStepAccess($nextIndex);
		}

		$stepSession->store();

		// If there's still other steps, continue with the rest of the steps
		if (!$completed) {
			return $this->view->call(__FUNCTION__, $stepSession);
		}

		// Here we assume that the user completed all the steps
		$eventsModel = ES::model('Events');

		// Create the new event
		$event = $eventsModel->createEvent($stepSession);

		if (!$event->id) {
			$errors = $eventsModel->getError();

			$this->view->setMessage($errors, ES_ERROR);

			return $this->view->call(__FUNCTION__, $stepSession);
		}

		// If there is recurring data, then we back up the session->values and the recurring data in the the event params first before deleting step session
		if (!empty($event->recurringData)) {
			$clusterTable = ES::table('Cluster');
			$clusterTable->load($event->id);
			$eventParams = ES::makeObject($clusterTable->params);
			$eventParams->postdata = ES::makeObject($stepSession->values);
			$eventParams->recurringData = $event->recurringData;
			$clusterTable->params = json_encode($eventParams);
			$clusterTable->store();
		}

		$stepSession->delete();

		if ($event->isPublished()) {

			$options = array();

			// Special case for page. If this is event page we need to assign the post actor
			// The post acor will  always be the page since non admin cant create event in page.
			if ($event->isPageEvent()) {
				$options['postActor'] = SOCIAL_TYPE_PAGE;
			}

			// Create new stream item
			$event->createStream($event->creator_uid, 'create', $options);

			// Update social goals
			$this->my->updateGoals('joincluster');

			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_CREATED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		} else {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_CREATED_PENDING_APPROVAL', SOCIAL_MSG_INFO);
		}

		return $this->view->call('complete', $event, $startDatetime);
	}

	/**
	 * Update an event
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event data
		$id = $this->input->get('id', 0, 'int');
		$isLastRecurringEvent = $this->input->getInt('isLastRecurringEvent');

		// Load up the event
		$event = ES::event($id);
		$isNew = empty($event->id);

		if (empty($event) || empty($event->id)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->isPublished() && !$event->isDraft()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
		}

		$guest = $event->getGuest($this->my->id);

		if (!$this->my->isSiteAdmin() && !$guest->isOwner() && !$event->isAdmin() && !$event->isClusterOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NOT_ALLOWED_TO_EDIT_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__, $event);
		}

		$post = $this->input->post->getArray();
		$data = array();

		$disallowed = array(ES::token(), 'option', 'task', 'controller');

		foreach ($post as $key => $value) {
			if (in_array($key, $disallowed)) {
				continue;
			}

			if (is_array($value)) {
				$value = json_encode($value);
			}

			$data[$key] = $value;
		}

		$fieldsModel = ES::model('Fields');

		$fields = ES::model('Fields')->getCustomFields(array('group' => SOCIAL_TYPE_EVENT, 'workflow_id' => $event->getWorkflow()->id, 'visible' => SOCIAL_EVENT_VIEW_EDIT, 'data' => true, 'dataId' => $event->id, 'dataType' => SOCIAL_TYPE_EVENT));

		// Retrieve the recurring field id
		$recurringFieldId = $fieldsModel->getSpecificFieldId($event->getWorkflow()->id, SOCIAL_FIELDS_GROUP_EVENT, 'recurring');
		$hasRecurringFieldData = false;

		// Determine whether this workflow field got recurring field or not
		if ($recurringFieldId) {
			$recurringFieldPrefix = SOCIAL_FIELDS_PREFIX . $recurringFieldId;

			if (isset($data[$recurringFieldPrefix]) && $data[$recurringFieldPrefix]) {

				$recurringObj = json_decode($data[$recurringFieldPrefix]);
				$hasRecurringFieldData = true;

				// If the recurring field set to none
				if ($recurringObj->type == 'none') {
					$hasRecurringFieldData = false;
				}
			}
		}

		// Determine whether has recurring field or not
		$data['hasRecurringFieldData'] = $hasRecurringFieldData;

		// Determine if this recurring event is the last one
		$data['isLastRecurringEvent'] = $isLastRecurringEvent;
		$data['isNew'] = $isNew;

		$fieldsLib = ES::fields();

		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$event);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$event);

		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args, array($fieldsLib->getHandler(), 'validate'));

		if (!empty($errors)) {
			$this->view->setMessage('COM_ES_ERRORS_IN_FORM', ES_ERROR);

			$this->input->set('view', 'events');
			$this->input->set('layout', 'edit');

			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args, array($fieldsLib->getHandler(), 'beforeSave'));

		if (!empty($errors)) {
			$this->view->setMessage('COM_ES_ERRORS_IN_FORM', ES_ERROR);

			$this->input->set('view', 'events');
			$this->input->set('layout', 'edit');

			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		// If this event currently in draft state, mean this update is to submit to approval.
		// OR if event required to be moderated, unpublish it first.
		if ($event->isDraft() || $this->my->getAccess()->get('events.moderate')) {
			$event->state = SOCIAL_CLUSTER_UPDATE_PENDING;
		}

		if ($this->my->isSiteAdmin() || !$this->config->get('events.editmoderation')) {
			$event->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		// Trigger events
		$dispatcher = ES::dispatcher();
		$triggerArgs = array(&$event, &$this->my, false);

		// @trigger: onEventBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventBeforeSave', $triggerArgs);

		$event->save();

		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEventAfterSave', $triggerArgs);


		// once this is done, we no longer need this post data #5399
		unset($post['oriStartDatetime']);
		unset($post['oriEndDatetime']);

		$model = ES::model('Events');

		if ($event->state === SOCIAL_CLUSTER_UPDATE_PENDING || !$this->my->isSiteAdmin()) {
			$model->notifyAdmins($event, true);
		}

		$fieldsLib->trigger('onEditAfterSave', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);

		$event->bindCustomFields($data);

		$fieldsLib->trigger('onEditAfterSaveFields', SOCIAL_FIELDS_GROUP_EVENT, $fields, $args);

		// update all stream's cluster_access related to this cluster.
		$event->updateStreamClusterAccess();

		// Only create if applyRecurring is false or event is not a child
		// applyRecurring && parent = true
		// applyRecurring && child = false
		// !applyRecurring && parent = true
		// !applyRecurring && child = true
		if ((empty($data['applyRecurring']) || !$event->isRecurringEvent()) && $event->isPublished()) {
			$event->createStream($this->my->id, 'update');
		}

		$messageLang = $event->isPending() ? 'COM_EASYSOCIAL_EVENTS_UPDATED_PENDING_APPROVAL' : 'COM_EASYSOCIAL_EVENTS_UPDATED_SUCCESSFULLY';

		$this->view->setMessage($messageLang, SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event, (int) $isNew);
	}

	/**
	 * Allows caller to rsvp to an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function rsvp()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the current event id
		$id = $this->input->get('id', 0, 'int');

		// Load the event
		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Try to rsvp
		$task = $this->input->get('task', '', 'word');
		$guest = $event->rsvp($task);

		if ($guest === false) {
			return $this->view->call('rsvpFailed', $event->getError());
		}

		return $this->view->call(__FUNCTION__, $event, $guest);
	}

	/**
	 * Allows caller to filter event's content on item page
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getStream()
	{
		ES::checkToken();

		// Get the event
		$id = $this->input->get('eventId', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->isPublished()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
		}

		if (!$event->canViewItem()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Get the filter type
		$filter = $this->input->get('filter', 'feeds', 'string');
		$filterId = $this->input->get('id', '', 'int');

		$stream = ES::stream();

		// Checks if the user is allowed to post updates on event stream
		if ($this->my->canPostClusterStory(SOCIAL_TYPE_EVENT, $event->id)) {
			$story = ES::get('Story', $event->cluster_type);
			$story->setCluster($event->id, $event->cluster_type);
			$story->showPrivacy(false);

			$story->setFilter($filter);

			$stream->story = $story;
		}

		// lets get the sticky posts 1st
		// Scheduled post shouldn't be allowed to be sticky. No need to search for this.
		if ($filter != 'scheduled') {
			$stickies = $stream->getStickies(array('clusterId' => $event->id, 'clusterType' => SOCIAL_TYPE_EVENT, 'limit' => 0));

			if ($stickies) {
				$stream->stickies = $stickies;
			}
		}

		// Default stream options
		$streamOptions = array('clusterId' => $event->id, 'clusterType' => $event->cluster_type, 'nosticky' => true);

		$streamFilter = '';

		// Retrieve items related to the particular stream filter only.
		if ($filter == 'filters' && $filterId) {
			$streamFilter = ES::table('StreamFilter');
			$streamFilter->load($filterId);

			// Get a list of hashtags
			$hashtags = $streamFilter->getHashTag();
			$tags = explode(',', $hashtags);

			if ($tags) {
				$streamOptions['tag'] = $tags;

				$hashtagRule = $this->config->get('stream.filter.hashtag', '');
				if ($hashtagRule == 'and') {
					$streamOptions['matchAllTags'] = true;
				}

				if (isset($stream->story)) {
					$stream->story->setHashtags($tags);
				}
			}
		}

		if ($filter == 'scheduled') {
			$streamOptions['isScheduled'] = true;
			$streamOptions['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

			$displayOptions['disableActions'] = true;

			unset($stream->stickies);
		}

		$postTypes = $this->input->get('postTypes', array(), 'word');

		if ($postTypes) {
			$streamOptions['context'] = $postTypes;
		}

		$stream->get($streamOptions, array('perspective' => 'events'));

		$event->saveFilterPreferences($filter, $filterId);

		return $this->view->call(__FUNCTION__, $event, $stream, $streamFilter);
	}

	/**
	 * Retrieves info about an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getInfo()
	{
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->isPublished()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
		}

		if (!$event->canViewItem()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Get the current active step
		$activeStep = $this->input->get('step', 1, 'int');

		// Get the entire "about" for the group
		$model = ES::model('Events');
		$fields = $model->getAbout($event, $activeStep);

		return $this->view->call(__FUNCTION__, $event, $fields);
	}

	/**
	 * Retrieves the dashboard contents.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getAppContents()
	{
		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->isPublished()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
		}

		if (!$event->canViewItem()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Get the application
		$appId = $this->input->get('appId', 0, 'int');
		$app = ES::table('App');
		$state = $app->load($appId);

		// If application id is not valid, throw an error.
		if (!$appId || !$state) {
			return $this->view->exception('COM_EASYSOCIAL_APPS_INVALID_APP_ID_PROVIDED');
		}

		return $this->view->call(__FUNCTION__, $event, $app);
	}

	/**
	 * Make a user an admin of an event
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function promoteGuest()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		// Get the guest object
		$uid = $this->input->get('uid', 0, 'int');

		// Get the event object
		$eventId = $this->input->get('id', 0, 'int');
		$event = ES::event($eventId);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__, $event);
		}

		if (!$event->canPromoteMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$state = $event->promoteUser($uid);

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);
			return $this->view->call(__FUNCTION__, $event);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_GUEST_PROMOTE_SUCCESS', ES::user($uid)->getName()), SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Revokes a user's admin rights.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function demoteGuest()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		// Get the guest object
		$uid = $this->input->get('uid', 0, 'int');
		$guest = ES::table('EventGuest');
		$state = $guest->load($uid);

		// Get the event object
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$state || empty($guest->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_GUEST_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__, $event);
		}

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__, $event);
		}

		$myGuest = $event->getGuest();

		if (($this->my->isSiteAdmin() || $myGuest->isOwner()) && $guest->isStrictlyAdmin()) {
			$guest->revokeAdmin();
		} else {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_ADMIN_REVOKE_SUCCESSFULLY', ES::user($guest->uid)->getName()), SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Allows event owner to remove the event avatar
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function removeAvatar()
	{
		// Check for request forgeries
		ES::checkToken();

		// Load the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		// Only allow event admins to remove avatar
		if (!$event->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__, $event);
		}

		// Try to remove the avatar from the event now
		$event->removeAvatar();

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_AVATAR_REMOVED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Remove a guest from an event.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function removeGuest()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		// Get the guest object
		// this is cluster node id
		$id = $this->input->getInt('id');

		$guest = ES::table('EventGuest');
		$state = $guest->load($id);

		if (!$state || !$guest->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_GUEST_ID');
		}

		// Get the event object
		$event = ES::event($guest->cluster_id);

		if (empty($event) || empty($event->id)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		// Ensure that the user can really remove the guest
		if (!$event->canRemoveGuest($guest->uid, $this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Remove the guest now
		$guest->remove();
		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_GUEST_REMOVAL_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Approve a guest.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function approveGuest()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$userIds = $this->input->get('userId', array(), 'array');

		// Get the event object
		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__, $event);
		}

		$myGuest = $event->getGuest();

		if ($userIds) {
			foreach ($userIds as $userId) {
				// Get the guest object
				$guest = ES::table('EventGuest');
				$state = $guest->load($userId);

				if (!$state || empty($guest->id)) {
					$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_GUEST_ID', ES_ERROR);

					return $this->view->call(__FUNCTION__, $event);
				}

				if ((!$this->my->isSiteAdmin() && !$myGuest->isAdmin()) || !$guest->isPending()) {
					 $this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);

					 return $this->view->call(__FUNCTION__, $event);
				}

				$guest->approve();
			}
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_GUEST_APPROVED_SUCCESS', $guest->getName()), SOCIAL_MSG_SUCCESS);

		if (count($userIds) > 1) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_GUESTS_APPROVED_SUCCESS'), SOCIAL_MSG_SUCCESS);
		}

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Reject a guest.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function rejectGuest()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$userIds = $this->input->get('userId', array(), 'array');

		// Get the event object
		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__, $event);
		}

		$myGuest = $event->getGuest();


		if ($userIds) {
			foreach ($userIds as $userId) {
				// Get the guest object
				$guest = ES::table('EventGuest');
				$state = $guest->load($userId);

				if (!$state || empty($guest->id)) {
					$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_GUEST_ID', ES_ERROR);

					return $this->view->call(__FUNCTION__, $event);
				}

				if ((!$this->my->isSiteAdmin() && !$myGuest->isAdmin()) || !$guest->isPending()) {
					$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);

					return $this->view->call(__FUNCTION__, $event);
				}

				$guest->reject();
			}
		}

		$this->view->setMessage(JText::_('COM_EASYSOCIAL_EVENTS_REJECTED_GUEST_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Allows caller to send invitations
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function invite()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->isPublished()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE');
		}

		if (!$event->canViewItem() || !$event->canInvite()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Get the user ids
		$ids = $this->input->get('uid', array(), 'var');

		if (!$ids) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_USER_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		foreach ($ids as $id) {
			$event->invite($id, $this->my->id);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_FRIENDS_INVITED_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	public function approveEvent()
	{
		$id = $this->input->getInt('id', 0);

		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check if this is moderation from frontend
		$isModerate = $this->input->get('moderate', false, 'bool');

		// Check the key
		$key = $this->input->getString('key');

		if (!$isModerate && $key != $event->key) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', $event->getName()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$state = $event->approve();

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_EVENT_APPROVE_FAILED', $event->getName()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_EVENT_APPROVE_SUCCESS', $event->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	public function rejectEvent()
	{
		$id = $this->input->getInt('id', 0);

		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check if this is moderation from frontend
		$isModerate = $this->input->get('moderate', false, 'bool');

		// Check the key
		$key = $this->input->getString('key');

		if (!$isModerate && $key != $event->key) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', $event->getName()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$state = $event->reject();

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_EVENT_REJECT_FAILED', $event->getName()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_EVENT_REJECT_SUCCESS', $event->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Set an event as featured
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->canFeature()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Set the event to featured
		$event->setFeatured();

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EVENT_FEATURE_SUCCESS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Set an event as featured
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function removeFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->canFeature()) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Set the event to featured
		$event->removeFeatured();

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EVENT_UNFEATURE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Unpublishes an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function unpublish()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		// check user is allowed to unpublish an event
		if (!$event->canUnpublish($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Unpublish the event
		$event->unpublish();

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EVENT_UNPUBLISH_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Publishes an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function publish()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		// check user is allowed to unpublish an event
		if (!$event->canPublish($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Unpublish the event
		$event->publish();

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EVENT_PUBLISH_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Allows caller to delete an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the event
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		if (!$event || !$event->id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID');
		}

		if (!$event->canDelete($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT');
		}

		// Unpublish the event
		$event->delete();

		$this->view->setMessage('COM_EASYSOCIAL_EVENTS_EVENT_DELETE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	public function itemAction()
	{
		$id = $this->input->getInt('id', 0);

		$event = ES::event($id);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$guest = $event->getGuest();

		// Support for group events
		if (!$this->my->isSiteAdmin() && !$guest->isAdmin() && !$guest->isOwner() && !$event->isClusterOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$action = $this->input->getString('action');

		// For delete actions, the user has to be an admin or owner
		if ($action == 'delete' && !$this->my->isSiteAdmin() && !$guest->isOwner() && !$event->isClusterOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Recurring support
		$mode = $this->input->getString('deleteMode', 'this');

		// Special handling needed for delete and if delete all
		if ($action == 'delete' && $mode == 'all' && ($event->isRecurringEvent() || $event->hasRecurringEvents())) {

			$parentId = $id;

			// Check if event is a parent event
			if ($event->isRecurringEvent()) {
				$parentId = $event->parent_id;
			}

			$parent = ES::event($parentId);
			$parent->delete($mode);
		} else {
			$event->$action($mode);
		}

		// COM_EASYSOCIAL_EVENTS_EVENT_FEATURE_SUCCESS
		// COM_EASYSOCIAL_EVENTS_EVENT_DELETE_SUCCESS
		// COM_EASYSOCIAL_EVENTS_EVENT_UNFEATURE_SUCCESS
		// COM_EASYSOCIAL_EVENTS_EVENT_UNPUBLISH_SUCCESS
		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_EVENTS_EVENT_' . strtoupper($action) . '_SUCCESS', $event->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $event);
	}

	/**
	 * Service Hook for explorer
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function explorer()
	{
		ES::checkToken();
		ES::requireLogin();

		$eventId = $this->input->getint('uid');
		$event = ES::event($eventId);

		// Determines if the current user is a guest of this event
		$guest = $event->getGuest($this->my->id);

		if (!$this->my->isSiteAdmin() && $event->isInviteOnly() && !$guest->isInvited() && !$guest->isGuest()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up the explorer library
		$explorer = ES::explorer($event->id, SOCIAL_TYPE_EVENT, true);
		$hook = $this->input->get('hook', '', 'cmd');

		$result = $explorer->hook($hook);
		$exception = ES::response('Folder retrieval successful', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $exception, $result);
	}

	/**
	 * Allows caller to delete an event filter
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function deleteFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$eventId = $this->input->get('uid', 0, 'int');

		if (!$id) {
			$this->view->setMessage('Invalid filter id - ' . $id, 'error');
			return $this->view->call(__FUNCTION__);
		}

		$filter = ES::table('StreamFilter');
		$filter->load(array('id' => $id, 'uid' => $eventId, 'utype' => SOCIAL_TYPE_EVENT));

		if (!$filter->id) {
			$this->view->setMessage('Filter not found. Action aborted.', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$filter->deleteItem();
		$filter->delete();

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_DELETED', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $eventId);
	}

	public function createRecurring()
	{
		ES::requireLogin();
		ES::checkToken();

		$eventId = $this->input->getInt('eventId');
		$schedule = $this->input->getString('datetime');
		$isLastRecurringEvent = $this->input->getInt('isLastRecurringEvent');

		$parentEvent = ES::event($eventId);
		$duration = $parentEvent->hasEventEnd() ? $parentEvent->getEventEnd()->toUnix() - $parentEvent->getEventStart()->toUnix() : false;

		// Get the data from the event params
		$data = ES::makeArray($parentEvent->getParams()->get('postdata'));

		$isClusterEvent = $parentEvent->isClusterEvent();

		// Determine whether this parent event created from the clusters or not
		if ((!isset($data['group_id']) || !isset($data['page_id'])) && $isClusterEvent) {

			if ($parentEvent->isGroupEvent()) {
				$data['group_id'] = $parentEvent->meta->group_id;
			}

			if ($parentEvent->isPageEvent()) {
				$data['page_id'] = $parentEvent->meta->page_id;
			}
		}

		// If applyRecurring return true mean this process is coming from editing while apply to all the child events
		// Use this applyRecurring for determine this coming from editing or new creation action
		$isEdit = $this->input->get('applyAllRecurringEvent', 0, 'int');

		// Mark the data as createRecurring
		$data['createRecurring'] = true;

		// Manually change the start end time
		$data['startDatetime'] = ES::date($schedule)->toSql();

		// Determine if this recurring event is the last one
		$data['isLastRecurringEvent'] = $isLastRecurringEvent;

		if ($duration) {
			$data['endDatetime'] = ES::date($schedule + $duration)->toSql();
		} else {
			unset($data['endDatetime']);
		}

		$event = ES::model('Events')->createRecurringEvent($data, $parentEvent, $isEdit);

		// Duplicate nodes from parent
		ES::model('Events')->duplicateGuests($parentEvent->id, $event->id);

		return $this->view->call(__FUNCTION__);
	}

	public function deleteRecurring()
	{
		ES::requireLogin();

		// Check for request forgeries.
		ES::checkToken();

		$eventId = $this->input->getInt('eventId');

		$event = ES::event($eventId);

		if (empty($event) || empty($event->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$guest = $event->getGuest();

		$guest = $event->getGuest();

		// Support for group events
		if (!$this->my->isSiteAdmin() && !$guest->isOwner() && !$event->isClusterOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		ES::model('Events')->deleteRecurringEvents($event->id);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allow caller to retrieve subcategories
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSubcategories()
	{
		$parentId = $this->input->get('parentId', 0, 'int');
		$groupId = $this->input->get('groupId', 0, 'int');
		$pageId = $this->input->get('pageId', 0, 'int');
		$backId = $this->input->get('backId', 0, 'int');

		// Retrieve current logged in user profile type id
		$profileId = $this->my->getProfile()->id;

		$model = ES::model('ClusterCategory');
		$subcategories = $model->getImmediateChildCategories($parentId, SOCIAL_TYPE_EVENT, $profileId);

		$this->view->call(__FUNCTION__, $subcategories, $groupId, $pageId, $backId);
	}
}
