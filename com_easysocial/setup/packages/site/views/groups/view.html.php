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

class EasySocialViewGroups extends EasySocialSiteView
{
	/**
	 * Checks if this feature should be enabled or not.
	 *
	 * @since	1.2
	 * @access	private
	 */
	private function checkFeature()
	{
		// Do not allow user to access groups if it's not enabled
		if (!$this->config->get('groups.enabled')) {
			$this->setMessage('COM_EASYSOCIAL_GROUPS_DISABLED', SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());
			$this->redirect(ESR::dashboard(array(), false));
		}
	}

	/**
	 * Renders the about view for groups
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function about($group)
	{
		$groupsModel = ES::model('Groups');
		$steps = $groupsModel->getAbout($group);

		$this->set('layout', 'info');
		$this->set('steps', $steps);

		return parent::display('site/groups/about/default');
	}

	/**
	 * Renders the app view for groups
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function app($group, $app)
	{
		// Ensure that the group really can access the app
		if (!$app->hasAccess($group->category_id)) {

			$this->setMessage(JText::_('COM_EASYSOCIAL_GROUP_DOES_NOT_HAVE_ACCESS'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			$redirect = ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false);

			return $this->redirect($redirect);
		}

		$app->loadCss();

		$this->page->title($group->getName() . ' - ' . $app->_('title'));
		$this->page->breadcrumb($app->_('title'));

		// to allow 3rd party app to support custom views,
		// we need to get from customView instead of hardcode the view to groups
		// when the site enable sef mode.
		$appView = $this->input->get('customView', 'groups', 'default');

		// Load the library.
		$lib = ES::apps();
		$contents = $lib->renderView(SOCIAL_APPS_VIEW_TYPE_EMBED, $appView, $app, array('groupId' => $group->id));

		$layout = 'apps.' . $app->element;

		// For members we need to update the correct layout
		if ($app->element == 'members') {
			$layout = 'members';
		}

		$this->set('layout', $layout);
		$this->set('contents', $contents);

		return parent::display('site/groups/app/default');
	}

	/**
	 * Default method to display the all groups page.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Check if the group features are available on the site
		$this->checkFeature();

		ES::checkCompleteProfile();
		ES::setMeta();

		$helper = $this->getHelper('List');

		// normalize the canonical link
		$canonicalOptions = ES::normalizeCanonicalOptions(true);

		// Set document properties
		$this->page->title($helper->getPageTitle());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_GROUPS');
		$this->page->canonical(ESR::groups($canonicalOptions));

		$user = $helper->getActiveUser();
		$userid = $this->input->get('userid', null, 'int');

		// If the user is blocked, they should not be accessible #5069
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// make sure viewer can view user's groups listing page.
		if ($userid && !$helper->canUserView($user)) {
			return $this->restricted($user);
		}

		$browseView = $helper->isBrowseView();
		$filter = $helper->getCurrentFilter();

		$allowedFilters = array('all', 'invited', 'mine', 'featured', 'pending', 'nearby', 'created', 'participated', 'category', 'unpublished');

		if (!empty($filter) && !in_array($filter, $allowedFilters)) {
			return ES::raiseError(404, JText::_('COM_EASYSOCIAL_GROUPS_INVALID_GROUP_ID'));
		}

		$model = ES::model('Groups');
		$options = array('state' => SOCIAL_STATE_PUBLISHED);

		// Only exclude featured group when viewing all groups
		if ($browseView) {
			$options['featured'] = false;
		}

		// If user is site admin, they should be able to see everything.
		// except for my created groups. #3075
		$options['types'] = $this->my->isSiteAdmin() && $filter !== 'created' ? 'all' : 'user';

		// If in profile group listing, show only group created by that user
		// except in participated filter. because we need to show others' group as well
		if (!$browseView) {
			if ($filter != 'participated') {
				$options['uid'] = $user->id;
			} else {
				$options['userid'] = $user->id;
				$options['types'] = 'participated';
			}
		}

		$hasLocation = false;

		// Check if there is any location data
		$userLocation = JFactory::getSession()->get('groups.userlocation', array(), SOCIAL_SESSION_NAMESPACE);
		$distance = $this->config->get('groups.nearby.radius');
		$showDistance = false;
		$showDistanceSorting = false;

		// Default snackbar heading
		$heading = 'COM_EASYSOCIAL_GROUPS';

		// Filter by nearby location
		if ($filter === 'nearby') {
			$defaultDistanceRadius = $distance;
			$distance = $this->input->get('distance', $defaultDistanceRadius, 'int');

			if (!empty($distance) && $distance != $defaultDistanceRadius) {
				$routeOptions['distance'] = $distance;
			}

			$hasLocation = !empty($userLocation) && !empty($userLocation['latitude']) && !empty($userLocation['longitude']);

			// If there is no location, then we need to delay the event retrieval process
			$delayed = !$hasLocation ? true : false;

			// We do not want to display sorting by default

			if ($hasLocation) {
				$options['location'] = true;
				$options['distance'] = $distance;
				$options['latitude'] = $userLocation['latitude'];
				$options['longitude'] = $userLocation['longitude'];
				$options['range'] = '<=';

				$showDistance = true;
				$showDistanceSorting = true;

				$heading = JText::sprintf('COM_ES_GROUPS_IN_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));
			}
		}

		// Determine the pagination limit
		$limit = ES::getLimit('groups_limit');
		$options['limit'] = $limit;

		// Determine if this is filtering groups by category
		$activeCategory = $helper->getActiveCategory();

		if ($activeCategory) {
			$options['category'] = $activeCategory->id;

			// check if this category is a container or not
			if ($activeCategory->container) {
				// Get all child ids from this category
				$categoryModel = ES::model('ClusterCategory');
				$childs = $categoryModel->getChildCategories($activeCategory->id, array(), SOCIAL_TYPE_GROUP, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}

			$clusterCategoryTbl = ES::table('ClusterCategory');
			$clusterCategoryTbl->load($activeCategory->id);

			// normalize the canonical link
			$canonicalOptions = ES::normalizeCanonicalOptions();

			$this->page->description($helper->getPageCategoryDesc());
			$this->page->canonical($clusterCategoryTbl->getFilterPermalink(true, true, $canonicalOptions));
		}

		// Since not logged in users cannot filter by 'invited' or 'mine', they shouldn't be able to access these filters at all
		if ($this->my->guest && ($filter == 'invited' || $filter == 'mine' || $filter == 'unpublished')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// If the default filter is invited, we only want to fetch groups that the user has been
		// invited to.
		if ($filter == 'invited') {
			$options['invited'] = $this->my->id;
			$options['types'] = 'all';
			$options['featured'] = '';
		}

		// Filter by own groups
		if ($filter == 'mine') {
			// if we are fetching own groups, we just need to enabled featured flags, but we do not need to fetch the featured groups separately
			// so that when viewing, we will not see two sections.
			$options['featured'] = false;
			$options['state'] = SOCIAL_STATE_PUBLISHED;
			$options['uid'] = $this->my->id;
			$options['types'] = 'participated';
		}

		// Filter by user own created groups
		if ($filter == 'created' && $options['types'] == 'user' && !isset($options['uid']) && $this->my->id) {
			$options['uid'] = $this->my->id;
			$options['featured'] = '';
		}

		// Groups that pending user's review.
		if ($filter == 'pending') {
			$options['uid'] = $this->my->id;
			$options['state'] = SOCIAL_CLUSTER_DRAFT;
			$options['types'] = 'user';
		}

		if ($filter == 'unpublished') {
			// allow site admin to view all the unpublished post
			if (!$this->my->isSiteAdmin()) {
				$options['uid'] = $this->my->id;
			}

			$options['state'] = SOCIAL_STATE_UNPUBLISHED;
		}

		// Get ordering option if any
		$ordering = $this->input->get('ordering', $this->config->get('groups.layout.listingsort'), 'cmd');
		$options['ordering'] = $ordering;

		$groups = array();

		// Get a list of groups
		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		$groups = $model->getGroups($options);
		$pagination = $model->getPagination();

		if (!$browseView && $userid) {
			$user = ES::user($userid);
			$pagination->setVar('userid', $user->getAlias());
		}

		// Get a list of featured groups
		$featuredGroups = array();
		$showAllFeatured = false;

		if ($filter == 'all' || $filter == 'mine' || $activeCategory) {
			$options['featured'] = true;
			$featuredGroups	= $model->getGroups($options);
		}

		$sortItems = $helper->getSortables();
		$emptyText = $helper->getEmptyText();
		$filters = $helper->getFilters();
		$filtersAcl = $helper->getFiltersAcl();

		$this->set('filters', $filters);
		$this->set('filtersAcl', $filtersAcl);
		$this->set('browseView', $browseView);
		$this->set('heading', $heading);
		$this->set('sortItems', $sortItems);
		$this->set('activeCategory', $activeCategory);
		$this->set('pagination', $pagination);
		$this->set('featuredGroups', $featuredGroups);
		$this->set('groups', $groups);
		$this->set('filter', $filter);
		$this->set('user', $user);
		$this->set('ordering', $ordering);
		$this->set('emptyText', $emptyText);

		// Distance
		$this->set('distance', $distance);
		$this->set('distanceUnit', $this->config->get('general.location.proximity.unit'));
		$this->set('showDistance', $showDistance);
		$this->set('showDistanceSorting', $showDistanceSorting);
		$this->set('hasLocation', $hasLocation);
		$this->set('userLocation', $userLocation);

		return parent::display('site/groups/default/default');
	}

	/**
	 * Displays a restricted page
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function restricted($node)
	{
		$label = 'COM_ES_GROUPS_PRIVATE_GROUP_INFO';
		$text = 'COM_ES_GROUPS_PRIVATE_GROUP_INFO_DESC';

		if ($node instanceof SocialUser) {
			$label = 'COM_EASYSOCIAL_GROUPS_RESTRICTED';
			$text = 'COM_EASYSOCIAL_GROUPS_RESTRICTED_USER_DESC';
		}

		// Cluster types
		$this->set('node', $node);
		$this->set('label', $label);
		$this->set('text', $text);

		echo parent::display('site/groups/restricted/default');
	}

	/**
	 * Default method to display the group creation page.
	 * This is the first page that displays the category selection.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function create($tpl = null)
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		// Only users with valid account is allowed to create
		ES::requireLogin();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		ES::setMeta();

		if (!$this->my->canCreateGroups()) {
			$this->setMessage(JText::_('COM_ES_GROUPS_NOT_ALLOWED_TO_CREATE_GROUP'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::groups(array(), false));
		}

		// Detect for an existing create group session.
		$session = JFactory::getSession();

		$stepSession = ES::table('StepSession');

		// If user doesn't have a record in stepSession yet, we need to create this.
		if (!$stepSession->load($session->getId())) {
			$stepSession->set('session_id', $session->getId());
			$stepSession->set('created', ES::get('Date')->toMySQL());
			$stepSession->set('type', SOCIAL_TYPE_GROUP);

			if (!$stepSession->store()) {
				$this->setError($stepSession->getError());
				return false;
			}
		}

		$model = ES::model('Groups');

		// We want to get parent category only for the initial category selection
		$categories = $model->getCreatableCategories($this->my->getProfile()->id, true);

		// Include child categories
		$allCategories = $model->getCreatableCategories($this->my->getProfile()->id);

		// If there's only 1 category, we should just ignore this step and load the steps page.
		if (count($allCategories) == 1) {

			// For some reason the parent categories will be get restricted but the child category still can able to allow user create group
			if (!$categories) {
				$category = $allCategories[0];
			} else {
				$category = $categories[0];
			}

			// to integrate with 3rd party cluster submission app.
			$canCreateInCategory = true;
			$arguments = array(&$this->my, &$category, &$canCreateInCategory);

			$dispatcher = ES::dispatcher();
			$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onEasySocialSelectCategory', $arguments);

			if (!$canCreateInCategory) {
				$this->setMessage(JText::sprintf('COM_ES_GROUPS_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), SOCIAL_MSG_ERROR);
				$this->info->set($this->getMessage());

				return $this->redirect(ESR::groups(array(), false));
			}
			// integration end.

			// need to check if this clsuter category has creation limit based on user points or not.
			if (!$category->hasPointsToCreate($this->my->id)) {
				$requiredPoints = $category->getPointsToCreate($this->my->id);

				$this->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_INSUFFICIENT_POINTS', $requiredPoints), SOCIAL_MSG_ERROR);
				$this->info->set($this->getMessage());

				return $this->redirect(ESR::groups(array(), false));
			}

			// Store the category id into the session.
			$session->set('category_id', $category->id, SOCIAL_SESSION_NAMESPACE);

			// Set the current category id.
			$stepSession->uid = $category->id;
			$stepSession->type = SOCIAL_TYPE_GROUP;

			// When user accesses this page, the following will be the first page
			$stepSession->step = 1;

			// Add the first step into the accessible list.
			$stepSession->addStepAccess(1);

			// Let's save this into a temporary table to avoid missing data.
			$stepSession->store();

			$this->steps();
			return;
		}

		// Set the page title
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_SELECT_GROUP_CATEGORY');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_GROUPS', ESR::groups());
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_GROUPS');

		$categoryRouteBaseOptions = array('controller' => 'groups' , 'task' => 'selectCategory');

		$this->set('categoryRouteBaseOptions', $categoryRouteBaseOptions);
		$this->set('categories', $categories);
		$this->set('backId', 0);
		$this->set('clusterType', SOCIAL_TYPE_GROUPS);
		$this->set('profileId', $this->my->getProfile()->id);

		parent::display('site/clusters/create/default');
	}

	/**
	 * Post process after user withdraws application to join the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function withdraw($group)
	{
		// Check if this feature is enabled.
		$this->checkFeature();
		$this->info->set($this->getMessage());

		return $this->redirect(ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false));
	}

	/**
	 * Post process after a user leaves a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function leaveGroup($group)
	{
		// Check if this feature is enabled.
		$this->checkFeature();
		$this->info->set($this->getMessage());

		$redirect = ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false);

		if ($group->isInviteOnly()) {
			$redirect = ESR::groups(array(), false);
		}

		return $this->redirect($redirect);
	}

	/**
	 * The workflow for creating a new group.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function steps()
	{
		// Only users with a valid account is allowed here.
		ES::requireLogin();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Ensure that the user is really allowed to create groups
		if (!$this->my->canCreateGroups()) {
			return $this->exception('COM_EASYSOCIAL_GROUPS_NOT_ALLOWED_TO_CREATE_GROUP');
		}

		// Retrieve the user's session.
		$session = JFactory::getSession();
		$stepSession = ES::table('StepSession');
		$stepSession->load($session->getId());

		// If there's no registration info stored, the user must be a lost user.
		if (is_null($stepSession->step)) {
			return $this->exception('COM_EASYSOCIAL_GROUPS_UNABLE_TO_DETECT_ACTIVE_STEP');
		}

		// Get the category that is being selected
		$categoryId = $stepSession->uid;

		// Load up the category
		$category = ES::table('GroupCategory');
		$category->load($categoryId);

		// Check if there is any workflow.
		if (!$category->getWorkflow()->id) {
			return $this->exception(JText::sprintf('COM_ES_NO_WORKFLOW_DETECTED', SOCIAL_TYPE_GROUP));
		}

		// Check if user really has access to create groups from this category
		if (!$category->hasAccess('create', $this->my->getProfile()->id) && !$this->my->isSiteAdmin()) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_GROUPS_NOT_ALLOWED_TO_CREATE_GROUP_IN_CATEGORY', $category->getTitle()));
		}

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->setMessage(JText::sprintf('COM_ES_GROUPS_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::groups(array(), false));
		}
		// integration end.

		// Get the current step index
		$stepIndex = $this->input->get('step', 1, 'int');

		// Determine the sequence from the step
		$currentStep = $category->getSequenceFromIndex($stepIndex, SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Users should not be allowed to proceed to a future step if they didn't traverse their sibling steps.
		if (empty($stepSession->session_id) || ($stepIndex > 1 && !$stepSession->hasStepAccess($stepIndex))) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_GROUPS_PLEASE_COMPLETE_PREVIOUS_STEP_FIRST', $currentStep));
		}

		// Check if this is a valid step in the profile
		if (!$category->isValidStep($currentStep, SOCIAL_GROUPS_VIEW_REGISTRATION)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_GROUPS_NO_ACCESS_TO_THE_STEP', $currentStep));
		}

		// Remember current state of registration step
		$stepSession->set('step', $stepIndex);
		$stepSession->store();

		// Load the current workflow / step.
		$step = ES::table('FieldStep');
		$step->loadBySequence($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, $currentStep);

		// Determine the total steps for this profile.
		$totalSteps	= $category->getTotalSteps();

		// Try to retrieve any available errors from the current registration object.
		$errors = $stepSession->getErrors();

		// Try to remember the state of the user data that they have entered.
		$data = $stepSession->getValues();

		// Since they are bound to the respective groups, assign the fields into the appropriate groups.
		$args = array(&$data, &$stepSession);

		// Get fields library as we need to format them.
		$fields = ES::fields();
		$fields->init(array('privacy' => false));

		// Retrieve custom fields for the current step
		$fieldsModel = ES::model('Fields');
		$customFields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'visible' => SOCIAL_GROUPS_VIEW_REGISTRATION));

		// Set the breadcrumb
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_PAGE_TITLE_GROUPS'), ESR::groups());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_GROUPS_START_YOUR_GROUP'), ESR::groups(array('layout' => 'create')));
		$this->page->breadcrumb($step->_('title'));

		// Set the page title
		ES::document()->title($step->get('title'));

		// Set the callback for the triggered custom fields
		$callback = array($fields->getHandler(), 'getOutput');

		// Trigger onRegister for custom fields.
		if (!empty($customFields)) {
			$fields->trigger('onRegister', SOCIAL_FIELDS_GROUP_GROUP, $customFields, $args, $callback);
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

		// Pass in the steps for this profile type.
		$steps = $category->getSteps(SOCIAL_GROUPS_VIEW_REGISTRATION);

		// Get the total steps
		$totalSteps = $category->getTotalSteps(SOCIAL_PROFILES_VIEW_REGISTRATION);

		// Format the steps
		if ($steps) {
			$counter = 1;

			foreach ($steps as &$step) {
				$stepClass = $step->sequence == $currentStep || $currentStep > $step->sequence || $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? ' active' : '';
				$stepClass .= $step->sequence < $currentStep || $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? $stepClass . ' past' : '';

				$step->css = $stepClass;
				$step->permalink = 'javascript:void(0);';

				if ($stepSession->hasStepAccess($step->sequence) && $step->sequence != $currentStep) {
					$step->permalink = ESR::groups(array('layout' => 'steps', 'step' => $counter));
				}

				$counter++;
			}
		}

		$this->set('conditionalFields', $conditionalFields);
		$this->set('stepSession', $stepSession);
		$this->set('steps', $steps);
		$this->set('currentStep', $currentStep);
		$this->set('currentIndex', $stepIndex);
		$this->set('totalSteps', $totalSteps);
		$this->set('step', $step);
		$this->set('fields', $customFields);
		$this->set('errors', $errors);
		$this->set('category', $category);

		return parent::display('site/groups/steps/default');
	}

	/**
	 * Renders the edit group page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function edit($errors = false)
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		// Only users with a valid account is allowed here.
		ES::requireLogin();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Load the language file from the back end.
		JFactory::getLanguage()->load('com_easysocial', JPATH_ADMINISTRATOR);

		// If have errors, then we set it
		if (!empty($errors)) {
			$this->info->set($this->getMessage());
		}

		$helper = $this->getHelper('Edit');
		$group = $helper->getActiveGroup();

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		// Check if the user is allowed to edit this group
		if (!$group->isOwner() && !$group->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->setMessage(JText::_('COM_EASYSOCIAL_GROUPS_NO_ACCESS'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Set the breadcrumb
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_GROUP_PAGE_BREADCRUMB'), ESR::groups());
		$this->page->breadcrumb($group->getName(), $group->getPermalink());
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_GROUP_EDIT_PAGE_BREADCRUMB'));

		// Set the page title
		$this->page->title(JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_GROUPS_EDIT', $group->getName()));

		// Get the steps
		$steps = $helper->getGroupSteps();

		// Get fields model
		$fieldsModel = ES::model('Fields');

		// Get custom fields library.
		$fields = ES::fields();

		// Enforce privacy to be false for groups
		$fields->init(array('privacy' => false));

		// Set the callback for the triggered custom fields
		$callback = array($fields->getHandler(), 'getOutput');

		$conditionalFields = array();

		// Get the custom fields for each of the steps.
		foreach ($steps as &$step) {
			$step->fields 	= $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $group->id, 'dataType' => SOCIAL_TYPE_GROUP, 'visible' => 'edit'));

			// Trigger onEdit for custom fields.
			if (!empty($step->fields)) {
				$post = $this->input->post->getArray();
				$args = array(&$post, &$group, $errors);
				$fields->trigger('onEdit', SOCIAL_TYPE_GROUP, $step->fields, $args, $callback);

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

		// retrieve group's approval the rejected reason.
		$rejectedReasons = array();
		if ($group->isDraft()) {
			$rejectedReasons = $group->getRejectedReasons();
		}

		$this->set('conditionalFields', $conditionalFields);
		$this->set('group', $group);
		$this->set('steps', $steps);
		$this->set('rejectedReasons', $rejectedReasons);
		$this->set('activeStep', $activeStep);

		echo parent::display('site/groups/edit/default');
	}

	/**
	 * Method is invoked each time a step is saved. Responsible to redirect or show necessary info about the current step.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveStep($session, $completed = false)
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		$info = ES::info();
		$config = ES::config();

		// Set any message that was passed from the controller.
		$info->set($this->getMessage());

		// If there's an error, redirect back user to the correct step and show the error.
		if ($this->hasErrors()) {
			return $this->redirect(ESR::groups(array('layout' => 'steps', 'step' => $session->step), false));
		}

		// Registration is not completed yet, redirect user to the appropriate step.
		return $this->redirect(ESR::groups(array('layout' => 'steps', 'step' => $session->step), false));
	}

	/**
	 * Displays the group item page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function item($tpl = null)
	{
		$this->checkFeature();

		ES::checkCompleteProfile();

		ES::setMeta();

		$helper = $this->getHelper('Item');
		$group = $helper->getActiveGroup();

		// If the user is not the owner and the user has been blocked by the group creator
		if ($this->my->id != $group->creator_uid && $this->my->isBlockedBy($group->creator_uid)) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_GROUPS_GROUP_NOT_FOUND'));
		}

		// Set the page properties
		$title = $group->getName();

		$this->page->title($title);
		$this->page->breadcrumb(JText::_('COM_EASYSOCIAL_GROUPS_PAGE_TITLE'), ESR::groups());

		// Display category in breadcrumbs
		$category = $group->getCategory();

		$this->page->breadcrumb($category->getTitle(), $category->getPermalink());
		$this->page->breadcrumb($title);

		$this->set('group', $group);

		// Display private group contents
		if (($group->isInviteOnly() || $group->isClosed()) && !$group->isMember() && !$this->my->isSiteAdmin()) {
			return $this->restricted($group);
		}

		$appId = $this->input->get('appId', 0, 'int');
		$hashtag = $this->input->get('tag', '', 'default');
		$hashtagAlias = $this->input->get('tag', '', 'default');
		$layout = $this->input->get('type', '', 'cmd');

		// Retrieve user's filter preference.
		$defaultDisplay = $group->getFilterPreferences();

		// If it is a hashtag view, let the timeline be the default display
		if (!$appId && !$layout && !$hashtag) {
			$layout = $defaultDisplay->filter;
		}

		$group->hit();
		$group->renderHeaders();

		// Render about on group pages
		if ($layout == 'info') {
			return $this->about($group);
		}

		// Render apps on group pages
		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($group, $app);
		}

		// Initiate stream lib
		$stream = ES::stream();

		// Determines if we should display the stream moderation
		$moderation = $group->getParams()->get('stream_moderation', null);
		$showPendingPostFilter = false;

		if ($moderation !== null && $moderation) {
			$showPendingPostFilter = true;
		}

		// Set the canonical data to the current group
		$this->page->canonical($group->getPermalink(false, true));

		// Get the timeline link
		$aboutPermalink = $helper->getAboutPermalink();

		// Retrieve story form for group
		$story = ES::get('Story', SOCIAL_TYPE_GROUP);
		$story->setCluster($group->id, SOCIAL_TYPE_GROUP);
		$story->showPrivacy(false);

		// Set storyPanelFilter
		$story->setFilter($layout);

		if ($hashtag) {
			$story->setHashtags(array($hashtag));
		}

		// Only group members allowed to post story updates on group page.
		if ($this->my->canPostClusterStory(SOCIAL_TYPE_GROUP, $group->id)) {

			// Set the story data on the stream
			$stream->story = $story;
		}

		// lets get the sticky posts 1st
		// Scheduled post shouldn't be allowed to be sticky. No need to search for this.
		if ($layout != 'scheduled') {
			$stickies = $stream->getStickies(array('clusterId' => $group->id, 'clusterType' => SOCIAL_TYPE_GROUP, 'limit' => 0));
			if ($stickies) {
				$stream->stickies = $stickies;
			}
		}

		// lets get stream items for this group
		$options = array('clusterId' => $group->id, 'clusterType' 	=> SOCIAL_TYPE_GROUP, 'nosticky' => true);
		$displayOptions = array();

		// stream filter id
		$filterId = $this->input->get('filterId', 0, 'int');

		// Respect what is set by the user if needed to
		if ($defaultDisplay->filter == 'custom') {
			$filterId = $defaultDisplay->id;
		}

		$customFilter = false;

		if ($defaultDisplay->filter == 'custom' && $filterId) {
			$customFilter = ES::table('StreamFilter');
			$customFilter->load($filterId);

			$hashtags = $customFilter->getHashTag();
			$tags = explode(',', $hashtags);

			if ($tags) {
				$options['tag'] = $tags;

				$story->setHashtags($tags);

				$hashtagRule = $this->config->get('stream.filter.hashtag', '');

				if ($hashtagRule == 'and') {
					$options['matchAllTags'] = true;
				}
			}
		}

		// Show stream items that is associated with the hashtag
		if ($hashtag) {
			$options['tag'] = array($hashtag);
		}

		if ($layout == 'moderation') {
			$options['onlyModerated'] = true;
			$options['nosticky'] = true;

			unset($stream->story);
			unset($stream->stickies);
		}

		if ($layout == 'scheduled') {
			$options['isScheduled'] = true;
			$options['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

			$displayOptions['disableActions'] = true;

			unset($stream->stickies);
		}

		$stream->get($options, $displayOptions);

		$this->addRss(ESR::groups(array('id' => $group->getAlias(), 'layout' => 'item'), false));

		// Retrieve custom filters in the group
		$customFilters = $group->getFilters();

		$model = ES::model('Stream') ;
		$appFilters = $model->getAppFilters(SOCIAL_TYPE_GROUP, $group->id);

		// Activity stream filter
		$streamFilter = ES::streamFilter(SOCIAL_TYPE_GROUP, $group->canCreateStreamFilter());
		$streamFilter->setAppFilters($appFilters);
		$streamFilter->setActiveFilter($filterId ? 'custom' : $layout, $filterId);
		$streamFilter->setCustomFilters($customFilters);
		$streamFilter->setActiveHashtag($hashtag);
		$streamFilter->setCluster($group);

		$this->set('customFilter', $customFilter);
		$this->set('aboutPermalink', $aboutPermalink);
		$this->set('appId', $appId);
		$this->set('title', $title);
		$this->set('showPendingPostFilter', $showPendingPostFilter);
		$this->set('layout', $layout);
		$this->set('hashtag', $hashtag);
		$this->set('stream', $stream);
		$this->set('rssLink', $this->rssLink);
		$this->set('stream', $stream);
		$this->set('hashtag', $hashtag);
		$this->set('hashtagAlias', $hashtagAlias);
		$this->set('streamFilter', $streamFilter);

		return parent::display('site/groups/item/default');
	}

	/**
	 * Post process after a group is created
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function complete($group)
	{
		$this->info->set($this->getMessage());

		$url = ESR::groups(array(), false);

		if ($group->isPublished()) {
			$url = ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false);
		}

		$this->redirect($url);
	}

	/**
	 * Displays information from groups within a particular category
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function category()
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		ES::setMeta();

		$helper = $this->getHelper('Category');

		// Validate for the current group category id
		$category = $helper->getActiveGroupCategory();

		// Load backend language file
		ES::language()->loadAdmin();

		// Set the page title to this category
		$this->page->title($category->get('title'));
		$this->page->description($category->getDescription());

		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_GROUPS', ESR::groups());
		$this->page->breadcrumb($category->_('title'));

		$this->page->canonical($category->getPermalink(true, true));

		// Retrieve a list of groups under this category
		$groups = $helper->getGroups();

		// Get random members from this category
		$randomMembers = $helper->getRandomCategoryMembers();

		// Get total groups within a category
		$totalGroups = $helper->getTotalGroups();

		// Get total albums within a category
		$totalAlbums = $helper->getTotalAlbums();

		// Get random albums for groups in this category
		$randomAlbums = $helper->getRandomAlbums();

		// Get the stream for this group
		$stream = $helper->getStreamData();

		$this->set('randomAlbums', $randomAlbums);
		$this->set('stream', $stream);
		$this->set('totalGroups', $totalGroups);
		$this->set('randomMembers', $randomMembers);
		$this->set('groups', $groups);
		$this->set('category', $category);
		$this->set('totalAlbums', $totalAlbums);

		return parent::display('site/groups/category/default');
	}

	/**
	 * Post process after a user is rejected to join the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function reject($group)
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		$permalink = $group->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post process after the group avatar is removed
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeAvatar(SocialGroup $group)
	{
		ES::info()->set($this->getMessage());

		$permalink 	= $group->getPermalink(false);

		$this->redirect($permalink);
	}

	/**
	 * Post process after a user is deleted from the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeMember(SocialGroup $group)
	{
		// Determines if we need to redirect
		$returnUrl = $this->getReturnUrl($group->getPermalink(false));

		// Set the necessary message
		$this->info->set($this->getMessage());

		return $this->redirect($returnUrl);
	}

	/**
	 * Post process after a user is approved to join the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function approve($group = null)
	{
		$this->info->set($this->getMessage());

		// Default redirect
		$redirect = ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false);

		$redirect = $this->getReturnUrl($redirect);

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a user has been promoted to be admin of the group
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function promote(SocialGroup $group)
	{
		// Determines if we need to redirect
		$returnUrl = $this->getReturnUrl($group->getPermalink(false));

		// Set the message
		$this->info->set($this->getMessage());

		return $this->redirect($returnUrl);
	}

	/**
	 * Post process after a user has been demoted
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function demote(SocialGroup $group)
	{
		// Determines if we need to redirect
		$returnUrl = $this->getReturnUrl($group->getPermalink(false));

		// Set the message
		$this->info->set($this->getMessage());

		return $this->redirect($returnUrl);
	}

	/**
	 * Post process after a user is invited to join the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function invite(SocialGroup $group)
	{
		$this->info->set($this->getMessage());

		$redirect = $this->getReturnUrl($group->getPermalink(false));

		$this->redirect($redirect);
	}

	/**
	 * Post process after a group is published
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function approveGroup()
	{
		ES::info()->set($this->getMessage());

		$this->redirect(ESR::groups(array(), false));
	}

	/**
	 * Post process after a group is rejected
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function rejectGroup()
	{
		ES::info()->set($this->getMessage());

		$this->redirect(ESR::groups(array(), false));
	}


	/**
	 * Post process after a group is set as featured
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function setFeatured($group)
	{
		$this->info->set($this->getMessage());

		$permalink = $group->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post process after a group is removed from being featured
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeFeatured($group)
	{
		$this->info->set($this->getMessage());

		$permalink = $group->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post process after category has been selected
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function selectCategory()
	{
		// Set message data.
		ES::info()->set($this->getMessage());

		// @task: Check for errors.
		if ($this->hasErrors()) {
			return $this->redirect(ESR::groups(array(), false));
		}

		// @task: We always know that after selecting the profile type, the next step would always be the first step.
		$url = ESR::groups(array('layout' => 'steps', 'step' => 1), false);

		return $this->redirect($url);
	}

	/**
	 * Post process when a group is deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete()
	{
		ES::info()->set($this->getMessage());

		$this->redirect(ESR::groups(array(), false));
	}

	/**
	 * Post process when a group is unpublished
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function unpublish()
	{
		ES::info()->set($this->getMessage());

		$this->redirect(ESR::groups(array(), false));
	}

	/**
	 * Post process when a group is published
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publish()
	{
		ES::info()->set($this->getMessage());

		$this->redirect(ESR::groups(array(), false));
	}

	/**
	 * Post process after saving group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function update($group)
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		ES::info()->set($this->getMessage());

		$url = '';
		if ($group->isPending()) {
			$url = ESR::groups(array(), false);
		} else {
			$url = $group->getPermalink(false);
		}

		return $this->redirect($url);
	}

	/**
	 * Post process after a user response to the invitation.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function respondInvitation($group, $action)
	{
		$this->info->set($this->getMessage());

		if ($action == 'reject') {
			$redirect = ESR::groups(array('filter' => 'invited'), false);
			return $this->redirect($redirect);
		}

		$redirect = ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false);

		// if accept from email n not logged in,
		// we should redirect to dashboard
		if (!$group->canAccess()) {
			$redirect = ESR::dashboard();
		}

		return $this->redirect($redirect);
	}


	/**
	 * Post process after saving group filter
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function saveFilter($filter, $groupId)
	{
		// Unauthorized users should not be allowed to access this page.
		ES::requireLogin();

		ES::info()->set($this->getMessage());

		$group = ES::group($groupId);

		$this->redirect(ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false));
	}

	/**
	 * Post process after adding group filter
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function addFilter($filter, $groupId)
	{
		$this->saveFilter($filter, $groupId);
	}

	/**
	 * Allows viewer to download a file from the group
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function download()
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		// Get the file id from the request
		$fileId = $this->input->get('fileid', 0, 'int');

		$file = ES::table('File');
		$file->load($fileId);

		if (!$file->id || !$fileId) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Load up the group
		$group = ES::group($file->uid);

		// Ensure that the user can really view this group
		if (!$group->canViewItem()) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$file->download();
		exit;
	}

	/**
	 * Allows viewer to download a conversation file
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function preview()
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		// Get the file id from the request
		$fileId = $this->input->get('fileid', null, 'int');

		$file = ES::table('File');
		$file->load($fileId);

		if (!$file->id || !$fileId) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		// Load up the group
		$group = ES::group($file->uid);

		// Ensure that the user can really view this group
		if (!$group->canViewItem()) {
			$this->redirect(ESR::dashboard(array(), false));
			$this->close();
		}

		$file->preview();
		exit;
	}

	/**
	 * Post processing after inviting a friend
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function sendInvites($group)
	{
		ES::info()->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::friends(array('layout' => 'invite', 'cluster_id' => $group->id), false));
		}

		return $this->redirect(ESR::groups(array('layout' => 'item', 'id' => $group->getAlias()), false));
	}

	/**
	 * Retrieve the stream contents for a group
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getStream(SocialStream $stream, SocialGroup $group, $streamFilter, $type)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$this->addRss(ESR::groups(array('id' => $group->getAlias(), 'layout' => 'item'), false));

		// Get the contents of the stream
		$theme = ES::themes();
		$theme->set('rssLink', $this->rssLink);
		$theme->set('stream', $stream);
		$theme->set('group', $group);
		$theme->set('type', $type);
		$theme->set('customFilter', $streamFilter);
		$contents = $theme->output('site/groups/item/feeds');

		$data = new stdClass();
		$data->contents = $contents;

		echo json_encode($data);exit;
	}
}
