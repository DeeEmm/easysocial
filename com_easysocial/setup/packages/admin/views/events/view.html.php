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

require_once(dirname(__DIR__) . '/abstracts/workflows.php');

class EasySocialViewEvents extends EasySocialViewWorkflowsAbstract
{
	protected $workflowType = SOCIAL_TYPE_EVENT;

	/**
	 * Displays the listings of events at the back end
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_EVENTS_TITLE', 'COM_EASYSOCIAL_EVENTS_DESCRIPTION');

		JToolbarHelper::addNew('create', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::custom('makeFeatured', '', '', JText::_('COM_ES_FEATURE'));
		JToolbarHelper::custom('removeFeatured', '', '', JText::_('COM_ES_UNFEATURE'));
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));


		$model = ES::model('Events', array('initState' => true, 'namespace' => 'events.listing'));

		$search = $model->getState('search');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$state = $model->getState('state');
		$type = $model->getState('type');
		$limit = $model->getState('limit');
		$tmpl = $this->input->getVar('tmpl');
		$category = $this->input->get('category', $model->getState('category'), 'int');
		$multiple = $this->input->get('multiple', true, 'bool');

		$events = $model->getItems();
		$pagination = $model->getPagination();

		$this->set('multiple', $multiple);
		$this->set('events', $events);
		$this->set('pagination', $pagination);
		$this->set('category', $category);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('type', $type);
		$this->set('limit', $limit);
		$this->set('tmpl', $tmpl);

		parent::display('admin/events/default/default');
	}

	/**
	 * Display function for creating an event.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function form($errors = array())
	{
		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		ES::language()->loadSite();

		$id = $this->input->get('id', 0, 'int');

		$event = ES::event($id);

		$category = ES::table('EventCategory');

		$isNew = empty($event->id);

		$this->setHeading('COM_EASYSOCIAL_EVENTS_CREATE_EVENT_TITLE');
		$this->setDescription('COM_EASYSOCIAL_EVENTS_CREATE_EVENT_DESCRIPTION');

		// Set the structure heading here.
		if (!$isNew) {
			$this->setHeading($event->title);
			$this->setDescription('COM_EASYSOCIAL_EVENTS_EDIT_EVENT_DESCRIPTION');

			$category->load($event->category_id);
		} else {
			// By default the published state should be published.
			$event->state = SOCIAL_STATE_PUBLISHED;

			$categoryId = $this->input->get('category_id', 0, 'int');
			$category->load($categoryId);
		}

		$stepsModel = ES::model('steps');
		$steps = $stepsModel->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS);

		$fieldsLib = ES::fields();
		$fieldsModel = ES::model('Fields');

		$post = $this->input->post->getArray();
		$args = array(&$post, &$event, &$errors);

		$conditionalFields = array();

		foreach ($steps as &$step) {

			$options = array('step_id' => $step->id);

			if (!$isNew) {
				$options['data'] = true;
				$options['dataId'] = $event->id;
				$options['dataType'] = SOCIAL_TYPE_EVENT;
			}

			$step->fields = $fieldsModel->getCustomFields($options);

			if (!empty($step->fields)) {
				$fieldsLib->trigger('onAdminEdit', SOCIAL_FIELDS_GROUP_EVENT, $step->fields, $args);
			}

			foreach ($step->fields as $field) {
				if ($field->isConditional()) {
					$conditionalFields[$field->id] = false;
				}
			}
		}

		if ($conditionalFields) {
			$conditionalFields = json_encode($conditionalFields);
		} else {
			$conditionalFields = false;
		}

		$this->set('conditionalFields', $conditionalFields);
		$this->set('event', $event);
		$this->set('steps', $steps);
		$this->set('category', $category);

		$guestModel = ES::model('EventGuests', array('initState' => true, 'namespace' => 'events.guests'));
		$guests = $guestModel->getItems(array('eventid' => $event->id, 'includeBlockUser' => true));

		$this->set('guests', $guests);
		$this->set('ordering', $guestModel->getState('ordering'));
		$this->set('direction', $guestModel->getState('direction'));
		$this->set('limit', $guestModel->getState('limit'));
		$this->set('pagination', $guestModel->getPagination());


		$activeTab = $this->input->get('activeTab', 'event', 'word');
		$this->set('activeTab', $activeTab);

		$this->set('isNew', $isNew);

		return parent::display('admin/events/form/default');
	}

	/**
	 * Post action after storing an event to redirect to the appropriate page according to the task.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function store($task, $event, $isNew = 0)
	{
		// Recurring support
		// If applies to all, we need to show a "progress update" page to update all childs through ajax.
		$applyAll = $event->hasRecurringEvents() && $this->input->getInt('applyRecurring');

		// Check if need to create recurring event
		$createRecurring = !empty($event->recurringData);

		if (!$applyAll && !$createRecurring) {
			$this->info->set($this->getMessage());

			if ($task === 'apply') {
				$activeTab = $this->input->get('activeTab', 'event', 'word');
				return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $event->id, 'activeTab' => $activeTab)));
			}

			if ($task === 'savenew') {
				return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'category_id' => $event->category_id)));
			}

			return $this->redirect(ESR::url(array('view' => 'events')));
		}

		$this->setHeading('COM_EASYSOCIAL_EVENTS_APPLYING_RECURRING_EVENT_CHANGES');
		$this->setDescription('COM_EASYSOCIAL_EVENTS_APPLYING_RECURRING_EVENT_CHANGES_DESCRIPTION');

		$post = $this->input->post->getArray([], null, 'raw');

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
		$totalRecurringEvents = 0;

		if ($createRecurring) {

			$eventDate = ES::date($event->getMeta('start'), false);

			// Get the recurring schedule
			$schedule = ES::model('Events')->getRecurringSchedule(array(
				'eventStart' => $eventDate,
				'end' => $event->recurringData->end,
				'type' => $event->recurringData->type,
				'daily' => $event->recurringData->daily
			));

			// count total of recurring events
			$totalRecurringEvents = count($schedule);
		}

		$this->set('schedule', $json->encode($schedule));
		$this->set('task', $task);
		$this->set('totalRecurringEvents', $totalRecurringEvents);

		// isNew value have to use 1 or 0 instead of true or false
		$this->set('isNew', $isNew);

		return parent::display('admin/events/store');
	}

	/**
	 * Post action of delete to redirect to event listing.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function delete()
	{
		$this->info->set($this->getMessage());

		$layout = $this->input->get('layout', '', 'string');

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => $layout)));
	}

	/**
	 * Renders the list of pending events
	 *
	 * @since  2.0
	 * @access public
	 */
	public function pending($tpl = null)
	{
		// Check access
		if (!$this->authorise('easysocial.access.events')) {
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR') , 'error');
		}

		$this->setHeading('COM_EASYSOCIAL_PENDING_EVENTS_TITLE', 'COM_EASYSOCIAL_PENDING_EVENTS_DESCRIPTION');

		JToolbarHelper::custom('approve', 'publish', 'social-publish-hover', JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'), true);
		JToolbarHelper::custom('reject', 'unpublish', 'social-unpublish-hover', JText::_('COM_EASYSOCIAL_REJECT_BUTTON'), true);
		JToolbarHelper::deleteList('', 'delete', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		$model = ES::model('Events', array('initState' => true, 'namespace' => 'events.pending'));

		$model->setState('state', SOCIAL_CLUSTER_PENDING);

		$events = $model->getItems();

		// Recurring support
		// Check if event is recurring event to add in the flag
		foreach ($events as $event) {
			$event->isRecurring = $event->getParams()->exists('recurringData');
		}

		$pagination = $model->getPagination();

		$this->set('events', $events);
		$this->set('pagination', $pagination);

		$search = $model->getState('search');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$state = $model->getState('state');
		$type = $model->getState('type');
		$limit = $model->getState('limit');

		$callback = $this->input->get('callback', '', 'default');

		$this->set('callback', $callback);
		$this->set('search', $search);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('type', $type);
		$this->set('limit', $limit);

		echo parent::display('admin/events/pending/default');
	}

	/**
	 * Display function for event categories listing page.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function categories($tpl = null)
	{
		// Check access
		if (!$this->authorise('easysocial.access.events')) {
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR') , 'error');
		}

		$this->setHeading('COM_EASYSOCIAL_EVENT_CATEGORIES_TITLE');
		$this->setDescription('COM_EASYSOCIAL_EVENT_CATEGORIES_DESCRIPTION');

		JToolbarHelper::addNew('categoryForm', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_NEW'), false);
		JToolbarHelper::divider();
		JToolbarHelper::publishList('publishCategory');
		JToolbarHelper::unpublishList('unpublishCategory');
		JToolbarHelper::divider();
		JToolbarHelper::deleteList('', 'deleteCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_DELETE'));

		$model = ES::model('EventCategories', array('initState' => true, 'events.categories'));

		$categories = $model->getItems();

		$pagination = $model->getPagination();

		$ordering = array();

		foreach ($categories as $category) {
			$ordering[$category->parent_id][] = $category->id;
		}

		$this->set('categories', $categories);
		$this->set('pagination', $pagination);

		$search = $model->getState('search');
		$order = $model->getState('ordering', 'lft');
		$direction = $model->getState('direction', 'asc');
		$state = $model->getState('state');
		$limit = $model->getState('limit');

		// Changing order only allowed when ordered by lft and asc
		$saveOrder = $order == 'lft' && $direction == 'asc';

		$this->set('search', $search);
		$this->set('order', $order);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('saveOrder', $saveOrder);

		$this->set('simple', $this->input->getString('tmpl') == 'component');

		echo parent::display('admin/events/categories/default');
	}

	/**
	 * Display function for event category form.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function categoryForm($tpl = null)
	{
		// Check access
		if (!$this->authorise('easysocial.access.events')) {
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR') , 'error');
		}

		$id = $this->input->get('id', 0, 'int');

		$category = ES::table('EventCategory');
		$category->load($id);

		// Set the structure heading here.
		if ($category->id) {
			$this->setHeading($category->get('title'), 'COM_EASYSOCIAL_EVENT_CATEGORY_EDIT_DESCRIPTION');
		} else {
			$this->setHeading('COM_EASYSOCIAL_EVENT_CATEGORY_CREATE_TITLE', 'COM_EASYSOCIAL_EVENT_CATEGORY_CREATE_DESCRIPTION');

			// By default the published state should be published.
			$category->state = SOCIAL_STATE_PUBLISHED;
		}

		JToolbarHelper::apply('applyCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('saveCategory', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('saveCategoryNew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($id) {
			JToolbarHelper::save2copy('saveCategoryCopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$activeTab = $this->input->get('activeTab', 'settings', 'default');
		$createAccess = '';

		if ($category->id) {
			$createAccess = $category->getAccess('create');
		}

		$childTabs = array('files', 'videos', 'photos', 'events', 'points', 'announcements', 'polls', 'tasks', 'discussions', 'files');
		if (in_array($activeTab, $childTabs)) {
			$activeTab = 'access';
		}

		// Set properties for the template.
		$this->set('activeTab', $activeTab);
		$this->set('category', $category);

		if ($category->id) {
			ES::language()->loadSite();

			$accessModel = ES::model('Access');
			$accessForm = $accessModel->getForm($category->id, SOCIAL_TYPE_EVENT, 'access');

			$this->set('accessForm' , $accessForm);
		}

		$exclusion = [];

		if ($category->id) {
			$exclusion[] = $category->id;
		}

		// We try to get the parent list
		$parentList = ES::populateCategories('parent_id', $category->parent_id, $exclusion, SOCIAL_TYPE_EVENT);
		$this->set('parentList', $parentList);

		$this->set('createAccess', $createAccess);
		$this->set('clusterType', SOCIAL_TYPE_EVENT);
		$this->set('controller', 'events');

		parent::display('admin/clusters/category.form/default');
	}

	/**
	 * Post process for the task applyCategory, saveCategoryNew and saveCategory to redirect to the corresponding page.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function saveCategory($category)
	{
		$activeTab = $this->input->getString('activeTab', 'settings');

		if ($this->hasErrors()) {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'categoryForm', 'id' => $category->id, 'activeTab' => $activeTab)));
		}

		$task = $this->input->get('task', '', 'default');

		if ($task === 'applyCategory') {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'categoryForm', 'id' => $category->id, 'activeTab' => $activeTab)));
		}

		if ($task === 'saveCategoryNew') {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'categoryForm', 'activeTab' => $activeTab)));
		}

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'categories', 'activeTab' => $activeTab)));
	}

	/**
	 * Post action for deleteCategory to redirect to event category listing.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function deleteCategory()
	{
		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'categories')));
	}

	/**
	 * Post action after publishing or unpublishing events to redirect to event listing.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function togglePublish()
	{
		return $this->redirect(ESR::url(array('view' => 'events')));
	}

	/**
	 * Post action after publishing or unpublishing event category to redirect to event listing.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function togglePublishCategory()
	{
		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'categories')));
	}

	/**
	 * Post action after approving an event to redirect back to the pending listing.
	 * @since  1.3
	 * @access public
	 */
	public function approve($ids = array())
	{
		if (empty($ids)) {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'pending')));
		}

		$schedules = array();
		$postdatas = array();
		$eventids = array();

		foreach ($ids as $id) {
			$event = ES::event($id);

			$params = $event->getParams();

			if ($params->exists('recurringData')) {

				$eventDate = ES::date($event->getMeta('start'), false);

				$schedule = ES::model('Events')->getRecurringSchedule(array(
					'eventStart' => $eventDate,
					'end' => $params->get('recurringData')->end,
					'type' => $params->get('recurringData')->type,
					'daily' => $params->get('recurringData')->daily
				));

				if (!empty($schedule)) {
					$eventids[] = $event->id;
					$schedules[$event->id] = $schedule;
					$postdatas[$event->id] = ES::makeObject($params->get('postdata'));
				}
			}
		}

		if (empty($schedules)) {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'pending')));
		}

		$this->setHeading('COM_EASYSOCIAL_EVENTS_APPLYING_RECURRING_EVENT_CHANGES', 'COM_EASYSOCIAL_EVENTS_APPLYING_RECURRING_EVENT_CHANGES_DESCRIPTION');

		$schedules = json_encode($schedules);
		$postdatas = json_encode($postdatas);
		$eventids = json_encode($eventids);

		$this->set('schedules', $schedules);
		$this->set('postdatas', $postdatas);
		$this->set('eventids', $eventids);

		parent::display('admin/events/approve.recurring');
	}

	public function approveRecurringSuccess()
	{
		$eventids = $this->input->getString('ids');
		$eventids = ES::makeArray($eventids);

		foreach ($eventids as $id) {
			// Get the table
			$clusterTable = ES::table('Cluster');
			$clusterTable->load($id);

			// Get the event params
			$eventParams = ES::makeObject($clusterTable->params);
			unset($eventParams->postdata);

			$clusterTable->params = ES::json()->encode($eventParams);
			$clusterTable->store();
		}

		$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENT_APPROVE_SUCCESS'), SOCIAL_MSG_SUCCESS);

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'pending')));
	}

	/**
	 * Post action after rejecting an event to redirect back to the pending listing.
	 * @since  1.3
	 * @access public
	 */
	public function reject()
	{
		$this->info->set($this->getMessage());

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'pending')));
	}

	/**
	 * Post action after inviting guests to an event to redirect back to the event form.
	 * @since  1.3
	 * @access public
	 */
	public function inviteGuests()
	{
		$this->info->set($this->getMessage());

		$id = $this->input->get('id', 0, 'int');

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $id, 'activeTab' => 'guests')));
	}

	/**
	 * Post action after approving guests to redirect back to the event form.
	 * @since  1.3
	 * @access public
	 */
	public function approveGuests()
	{
		$this->info->set($this->getMessage());

		$id = $this->input->get('id', 0, 'int');

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $id, 'activeTab' => 'guests')));
	}

	/**
	 * Post action after rejecting guests to redirect back to the event form.
	 * @since  1.3
	 * @access public
	 */
	public function removeGuests()
	{
		$this->info->set($this->getMessage());

		$id = $this->input->get('id', 0, 'int');

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $id, 'activeTab' => 'guests')));
	}

	/**
	 * Post action after switching an event's owner.
	 * @since  1.3
	 * @access public
	 */
	public function switchOwner()
	{
		return $this->redirect(ESR::url(array('view' => 'events')));
	}

	/**
	 * Post action after promoting guests to redirect back to the event form.
	 * @since  1.3
	 * @access public
	 */
	public function promoteGuests()
	{
		$id = $this->input->get('id', 0, 'int');

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $id, 'activeTab' => 'guests')));
	}

	/**
	 * Post action after removing guests admin role to redirect back to the event form.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function demoteGuests()
	{
		$id = $this->input->get('id', 0, 'int');

		return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $id, 'activeTab' => 'guests')));
	}

	/**
	 * Post process after a group is marked as featured
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function toggleDefault()
	{
		return $this->redirect('index.php?option=com_easysocial&view=events');
	}

	/**
	 * Post process after moving an event or event category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function move($layout = null)
	{
		return $this->redirect('index.php?option=com_easysocial&view=events&layout=' . $layout);
	}

	/**
	 * Post processing after switching event's category
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function switchCategory()
	{
		return $this->redirect('index.php?option=com_easysocial&view=events');
	}

	public function updateRecurringSuccess()
	{
		$this->info->set(false, JText::_('COM_EASYSOCIAL_EVENTS_FORM_UPDATE_SUCCESS'), SOCIAL_MSG_SUCCESS);

		$task = $this->input->getString('task');

		$eventId = $this->input->getInt('id');

		$event = ES::event($eventId);

		// Remove the post data from params
		$clusterTable = ES::table('Cluster');
		$clusterTable->load($event->id);
		$eventParams = ES::makeObject($clusterTable->params);
		unset($eventParams->postdata);
		$clusterTable->params = ES::json()->encode($eventParams);
		$clusterTable->store();

		if ($task === 'apply') {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $event->id, 'activeTab' => 'event')));
		}

		if ($task === 'savenew') {
			return $this->redirect(ESR::url(array('view' => 'events', 'layout' => 'form', 'category_id' => $event->category_id)));
		}

		return $this->redirect(ESR::url(array('view' => 'events')));
	}

	/**
	 * Post processing for updating ordering.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveorder()
	{
		return $this->redirect('index.php?option=com_easysocial&view=events&layout=categories');
	}
}
