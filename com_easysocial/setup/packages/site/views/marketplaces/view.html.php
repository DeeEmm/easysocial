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

ES::import('site:/views/views');

class EasySocialViewMarketplaces extends EasySocialSiteView
{
	/**
	 * Checks if this feature should be enabled or not.
	 *
	 * @since	3.3
	 * @access	private
	 */
	private function checkFeature()
	{
		// Do not allow user to access marketplaces if it's not enabled
		if (!$this->config->get('marketplaces.enabled')) {
			$this->setMessage('COM_ES_MARKETPLACES_DISABLED', SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());
			$this->redirect(ESR::dashboard(array(), false));
		}
	}

	/**
	 * Renders the all marketplace items
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Check if the marketplace features are available on the site
		$this->checkFeature();

		ES::checkCompleteProfile();
		ES::setMeta();

		// Default page title
		$title = 'COM_ES_PAGE_TITLE_MARKETPLACE';

		// Get the user's id
		// Here means we are viewing the user's listing
		$userid = $this->input->get('userid', null, 'int');
		$sort = $this->input->get('ordering', 'latest', 'word');

		// this checking is to prevent user from entering the invalid valid which might cause php fatal error on later processing.
		if (!$sort) {
			$sort = 'latest';
		}

		$user = ES::user($userid);

		// Determines the current filter being viewed
		$helper = $this->getHelper('List');

		// See if the user can view this
		if ($userid && !$helper->canUserView($user)) {
			return $this->restricted($user);
		}

		// see if this from cluster's marketplace
		$cluster = $helper->getCluster();

		$this->set('cluster', $cluster);

		// Check if the cluster is private
		// If yes, we show restricted page instead
		if ($cluster && !$cluster->canViewMarketplace()) {
			return $this->restricted($cluster);
		}

		$filter = $helper->getCurrentFilter();
		$activeCategory = $helper->getActiveCategory();
		$uid = $helper->getUid();
		$type = $helper->getType();
		$from = $helper->getFrom();
		$browseView = $helper->isBrowseView();


		// make sure the user is not blocked. #5069
		if ($type == SOCIAL_TYPE_USER) {
			$activeUser = ES::user($uid);
			if ($activeUser->isBlock() && !$this->my->isSiteAdmin()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
			}
		}


		// Prepare the options
		$options = [
			'filter' => $filter,
			'featured' => false,
			'sort' => $sort ? $sort : '',
			'uid' => $cluster ? $cluster->id : null,
			'type' => $cluster ? $cluster->getType() : null
		];

		if ($activeCategory) {
			$options['category'] = $activeCategory->id;

			// check if this category is a container or not
			if ($activeCategory->container) {
				// Get all child ids from this category
				$categoryModel = ES::model('MarketplaceCategories');
				$childs = $categoryModel->getChildCategories($activeCategory->id, array(), array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}

			$this->page->description($helper->getPageCategoryDesc());
		}

		// Only for clusters
		if ($cluster) {
			$title = JText::_('COM_ES_PAGE_TITLE_MARKETPLACE') . ' - ' . $cluster->getTitle();
			$cluster->renderPageTitle(null, 'marketplaces');
			$cluster->hit();
		}

		$listingOwner = false;

		if ($type == SOCIAL_TYPE_USER && $filter != 'pending') {

			// If user is viewing their own listings, we should use filter = mine
			$options['filter'] = SOCIAL_TYPE_USER;

			if ($uid != $this->my->id) {
				$options['userid'] = $uid;
			}

			if ($uid == $this->my->id) {
				$options['filter'] = 'created';
				$options['featured'] = false;
			}

			$options['uid'] = $uid;
			$options['type'] = $type;

			$listingOwner = ES::user($uid);
		}

		// this checking used in normal listings to include the featured listings when 'featured' filter clicked.
		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		if ($filter == 'created' || ($filter == 'all' && !$browseView)) {
			$options['includeFeatured'] = true;
		}

		// Check if there is any location data
		$userLocation = JFactory::getSession()->get('marketplaces.userlocation', array(), SOCIAL_SESSION_NAMESPACE);
		$hasLocation = false;
		$showDistance = false;
		$showDistanceSorting = false;
		$distance = $this->config->get('marketplaces.nearby.radius');

		// If there is no location, then we need to delay the listing retrieval process
		$delayed = !$hasLocation ? true : false;

		// Filter by nearby location
		if ($filter === 'nearby') {
			$defaultDistanceRadius = $distance;
			$distance = $this->input->get('distance', $defaultDistanceRadius, 'int');

			if (!empty($distance) && $distance != $defaultDistanceRadius) {
				$routeOptions['distance'] = $distance;
			}

			$hasLocation = !empty($userLocation) && !empty($userLocation['latitude']) && !empty($userLocation['longitude']);

			if ($hasLocation) {
				$options['location'] = true;
				$options['distance'] = $distance;
				$options['latitude'] = $userLocation['latitude'];
				$options['longitude'] = $userLocation['longitude'];

				// $options['distance'] = 10;//$distance;
				// $options['latitude'] = '3.1200770657722';//$userLocation['latitude'];
				// $options['longitude'] = '101.67886640971751'; //$userLocation['longitude'];
				$options['range'] = '<=';

				$showDistance = true;
				$showDistanceSorting = true;

				$title = JText::sprintf('COM_ES_MARKETPLACES_IN_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));
			}
		}

		$options['limit'] = ES::getLimit('marketplace_limit', 20);

		// Get a list of listings from the site
		$model = ES::model('Marketplaces');
		$listings = $model->getListings($options);
		$pagination = $model->getPagination();

		// Process the author for this listing. Only process if this is cluster view
		if ($cluster) {
			$listings = $this->processAuthor($listings, $cluster);
		}

		// Featured listings
		$featuredListings = array();

		// Process featured listing
		if (($filter === 'all' && ($browseView || ($cluster && $cluster->id)))) {

			$featuredOptions = array('featured' => true, 'state' => SOCIAL_STATE_PUBLISHED);

			if ($cluster) {
				$featuredOptions['type'] = $cluster->type;
				$featuredOptions['uid'] = $cluster->id;
			}

			if ($listingOwner) {
				$options['type'] = SOCIAL_TYPE_USER;
				$featuredOptions['uid'] = $listingOwner->id;
			}

			if ($activeCategory) {
				$featuredOptions['category'] = $options['category'];
			}

			$featuredListings = $model->getListings($featuredOptions);
		}

		$pageTitle = $helper->getPageTitle();

		if ($pageTitle) {
			$this->page->title($pageTitle);
		}

		$canonicalUrl = $helper->getCanonicalUrl();
		$this->page->canonical($canonicalUrl);

		$sortItems = $helper->getSortables();
		$returnUrl = $helper->getReturnUrl();

		// Generate empty text here
		$emptyText = 'COM_ES_MARKETPLACES_EMPTY_' . strtoupper($filter);

		if ($cluster) {
			$emptyText = 'COM_ES_CLUSTER_MARKETPLACES_EMPTY';
		}

		// If not browse view, we default the filter to 'created'
		if (!$browseView) {

			$filter = $filter != 'all' ? $filter : 'created';

			// If this is viewing profile's event, we display a different empty text
			$emptyText = 'COM_ES_MARKETPLACES_EMPTY_' . strtoupper($filter);

			if (!$user->isViewer()) {
				$emptyText = 'COM_ES_MARKETPLACES_USER_EMPTY_' . strtoupper($filter);
			}
		}

		$filters = $helper->getFiltersLink();
		$createUrl = $helper->getCreateUrl();

		$this->set('title', $title);
		$this->set('browseView', $browseView);
		$this->set('returnUrl', $returnUrl);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('cluster', $cluster);
		$this->set('featuredListings', $featuredListings);
		$this->set('activeCategory', $activeCategory);
		$this->set('filter', $filter);
		$this->set('filters', $filters);
		$this->set('listings', $listings);
		$this->set('sort', $sort);
		$this->set('pagination', $pagination);
		$this->set('sortItems', $sortItems);
		$this->set('from', $from);
		$this->set('activeUser', $user);
		$this->set('listingOwner', $listingOwner);
		$this->set('emptyText', $emptyText);
		$this->set('createUrl', $createUrl);
		$this->set('userLocation', $userLocation);
		$this->set('hasLocation', $hasLocation);
		$this->set('delayed', $delayed);

		// Distance
		$this->set('distance', $distance);
		$this->set('distanceUnit', $this->config->get('general.location.proximity.unit'));
		$this->set('showDistance', $showDistance);
		$this->set('showDistanceSorting', $showDistanceSorting);

		parent::display('site/marketplaces/default/default');
	}

	/**
	 * Process the listing author
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function processAuthor($listings, $cluster)
	{
		foreach ($listings as &$listing) {
			$listing->creator = $listing->getListingCreator($cluster);
		}

		return $listings;
	}

	/**
	 * Displays a restricted listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function restricted($node)
	{
		$label = 'COM_ES_MARKETPLACES_RESTRICTED';
		$text = 'COM_ES_MARKETPLACES_RESTRICTED_' . strtoupper($node->getType()) . '_DESC';

		if ($node instanceof SocialUser) {
			$text = 'COM_ES_MARKETPLACES_RESTRICTED_USER_DESC';
		}

		// Cluster types
		$this->set('node', $node);
		$this->set('label', $label);
		$this->set('text', $text);

		echo parent::display('site/marketplaces/restricted/default');
	}

	/**
	 * Displays the single listing item
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function item()
	{
		$this->checkFeature();

		ES::setMeta();

		$helper = $this->getHelper('Item');
		$listing = $helper->getActiveListing();

		// Ensure that the viewer can really view the listing
		if (!$listing->canViewItem()) {
			return $this->restricted($listing);
		}

		$from = $this->input->get('from', '', 'default');

		// Add canonical tags
		$this->page->canonical($listing->getPermalink(true, null, null, false, true));

		// Set the page title
		$this->page->title($listing->getTitle());

		// Add oembed tag
		$this->page->oembed($listing->getExternalPermalink('oembed'));

		// Set the page attributes
		$title = $listing->getTitle();

		if ($listing->isClusterListing()) {
			$cluster = $listing->getCluster();

			$this->page->breadcrumb($cluster->getTitle(), $cluster->getPermalink());
		}

		if (!$listing->isClusterListing()) {
			$this->page->breadcrumb('COM_ES_PAGE_TITLE_MARKETPLACE', ESR::marketplaces());
		}

		$this->page->breadcrumb($title);

		// Whenever a viewer visits a listing, increment the hit counter
		$listing->hit();

		// Retrieve the reports library
		$reports = $listing->getReports();

		$streamId = $listing->getStreamId('create');

		// Retrieve the comments library
		$comments = $listing->getComments('create', $streamId);

		// Retrieve the likes library
		$likes = $listing->getLikes('create', $streamId);

		// Retrieve the repost data
		$listingClusterId = ($listing->type != SOCIAL_APPS_GROUP_USER) ? $listing->uid : '0';
		$listingClusterType = ($listing->type != SOCIAL_APPS_GROUP_USER) ? $listing->type : '';

		$repost = $listing->reposts($listingClusterId, $listingClusterType);

		// Retrieve the privacy library
		$privacyButton = $listing->getPrivacyButton();

		// Retrieve the cluster associated with the listing
		$cluster = $listing->getCluster();

		// Build user alias
		$creator = $listing->getListingCreator($cluster);

		// Render meta headers
		$listing->renderHeaders();

		// Get random listings from the same category
		$otherListings = array();

		// Get display other listing type
		$otherListingType = $this->config->get('marketplaces.layout.item.recent');

		$model = ES::model('Marketplaces');

		// Do not skip this if set to any type
		if ($otherListingType) {

			$options = array('exclusion' => $listing->id, 'limit' => $this->config->get('marketplaces.layout.item.total'));

			if ($otherListingType == SOCIAL_MARKETPLACE_OTHER_CATEGORY) {
				$options['category'] = $listing->category_id;
			}

			$otherListings = $model->getListings($options);
		}

		// Update the back link if there is an "uid" or "type" in the url
		$uid = $this->input->get('uid', '', 'int');
		$type = $this->input->get('type', '', 'string');
		$backLink = ESR::marketplaces();

		if (!$uid && !$type) {
			// we will try to get from the current active menu item.
			$menu = $this->app->getMenu();
			if ($menu) {
				$activeMenu = $menu->getActive();

				$xQuery = $activeMenu->query;
				$xView = isset($xQuery['view']) ? $xQuery['view'] : '';
				$xLayout = isset($xQuery['layout']) ? $xQuery['layout'] : '';
				$xId = isset($xQuery['id']) ? (int) $xQuery['id'] : '';

				if ($xView == 'marketplaces' && $xLayout == 'item' && $xId == $listing->id) {
					if ($cluster) {
						$uid = $listing->uid;
						$type = $listing->type;
					}
				}
			}
		}

		$backLinkText = JText::_('COM_ES_MARKETPLACES_BACK_TO_MARKETPLACE');

		if ($from != 'listing' && $uid) {
			$backLink = $listing->getAllListingsLink();
			$backLinkText = JText::_('COM_ES_MARKETPLACES_BACK_' . strtoupper($listing->type) . '_MARKETPLACE');
		}

		// Generate a return url
		$returnUrl = base64_encode($listing->getPermalink());

		$model = ES::model('Marketplaces');
		$steps = $model->getInfo($listing);

		$returnUrl = base64_encode(ES::getURI(true));

		// Get photos
		$photos = $listing->getPhotos();

		$useConverseKit = ES::conversekit()->exists('marketplaces');

		$this->set('useConverseKit', $useConverseKit);
		$this->set('otherListings', $otherListings);
		$this->set('backLink', $backLink);
		$this->set('backLinkText', $backLinkText);
		$this->set('reports', $reports);
		$this->set('comments', $comments);
		$this->set('likes', $likes);
		$this->set('privacyButton', $privacyButton);
		$this->set('listing', $listing);
		$this->set('creator', $creator);
		$this->set('cluster', $cluster);
		$this->set('uid', $uid);
		$this->set('type', $type);
		$this->set('repost', $repost);
		$this->set('steps', $steps);
		$this->set('returnUrl', $returnUrl);
		$this->set('photos', $photos);

		echo parent::display('site/marketplaces/item/default');
	}

	/**
	 * Default method to display the listing creation page.
	 * This is the first page that displays the category selection.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function create($tpl = null)
	{
		// Check if this feature is enabled.
		$this->checkFeature();

		// Only users with valid account is allowed to create
		ES::requireLogin();

		ES::setMeta();

		$groupId = $this->input->getInt('group_id', 0);
		$pageId = $this->input->getInt('page_id', 0);

		if (!$pageId && !$groupId && !$this->my->canCreateListing()) {
			$this->setMessage(JText::_('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_LISTING'), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::marketplaces(array(), false));
		}

		$categoryRouteBaseOptions = array('controller' => 'marketplaces' , 'task' => 'selectCategory');

		if (!empty($groupId)) {
			$group = ES::group($groupId);

			if (!$group->canCreateListing()) {
				$this->info->set(false, JText::_('COM_ES_GROUPS_MARKETPLACES_NO_PERMISSION_TO_CREATE_LISTING'), SOCIAL_MSG_ERROR);

				return $this->redirect($group->getPermalink());
			}

			$categoryRouteBaseOptions['group_id'] = $groupId;

			$this->set('group', $group);
		}

		if (!empty($pageId)) {
			$page = ES::page($pageId);

			if (!$page->canCreateListing()) {
				$this->info->set(false, JText::_('COM_ES_PAGES_MARKETPLACES_NO_PERMISSION_TO_CREATE_LISTING'), SOCIAL_MSG_ERROR);

				return $this->redirect($page->getPermalink());
			}

			$categoryRouteBaseOptions['page_id'] = $pageId;

			$this->set('page', $page);
		}

		$this->set('categoryRouteBaseOptions', $categoryRouteBaseOptions);

		// Detect for an existing create listing session.
		$session = JFactory::getSession();

		$stepSession = ES::table('StepSession');

		// If user doesn't have a record in stepSession yet, we need to create this.
		if (!$stepSession->load($session->getId())) {
			$stepSession->set('session_id', $session->getId());
			$stepSession->set('created', ES::get('Date')->toMySQL());
			$stepSession->set('type', SOCIAL_TYPE_MARKETPLACE);

			if (!$stepSession->store()) {
				$this->setError($stepSession->getError());
				return false;
			}
		}

		$model = ES::model('Marketplaces');

		// We want to get parent category only for the initial category selection
		$categories = $model->getCreatableCategories($this->my->getProfile()->id, true);

		// Include child categories
		$allCategories = $model->getCreatableCategories($this->my->getProfile()->id);

		// If there's only 1 category, we should just ignore this step and load the steps page.
		if (count($allCategories) == 1) {

			// For some reason the parent categories will be get restricted but the child category still can able to allow user create listing
			if (!$categories) {
				$category = $allCategories[0];
			} else {
				$category = $categories[0];
			}

			// to integrate with 3rd party cluster submission app.
			$canCreateInCategory = true;
			$arguments = array(&$this->my, &$category, &$canCreateInCategory);

			$dispatcher = ES::dispatcher();
			$dispatcher->trigger(SOCIAL_TYPE_MARKETPLACE, 'onEasySocialSelectCategory', $arguments);

			if (!$canCreateInCategory) {
				$this->setMessage(JText::sprintf('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), SOCIAL_MSG_ERROR);
				$this->info->set($this->getMessage());

				return $this->redirect(ESR::marketplaces(array(), false));
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
			$stepSession->type = SOCIAL_TYPE_MARKETPLACE;

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

		// Set the page title
		$this->page->title('COM_ES_PAGE_TITLE_SELECT_MARKETPLACE_CATEGORY');
		$this->page->breadcrumb('COM_ES_PAGE_TITLE_MARKETPLACE', ESR::marketplaces());
		$this->page->breadcrumb('COM_ES_PAGE_TITLE_MARKETPLACE');

		$this->set('categories', $categories);
		$this->set('backId', 0);
		$this->set('profileId', $this->my->getProfile()->id);

		parent::display('site/marketplaces/create/default');
	}

	/**
	 * The workflow for creating a new marketplace listing.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function steps()
	{
		// Only users with a valid account is allowed here.
		ES::requireLogin();

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
		$category = ES::table('MarketplaceCategory');
		$category->load($categoryId);

		// Check if there is any workflow.
		if (!$category->getWorkflow()->id) {
			return $this->exception(JText::sprintf('COM_ES_NO_WORKFLOW_DETECTED', SOCIAL_TYPE_MARKETPLACE));
		}

		// Check if user really has access to create marketplaces from this category
		// if (!$category->hasAccess('create', $this->my->getProfile()->id) && !$this->my->isSiteAdmin()) {
		// 	return $this->exception(JText::sprintf('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_LISTING_IN_CATEGORY', $category->getTitle()));
		// }

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_MARKETPLACE, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->setMessage(JText::sprintf('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_LISTING_IN_CATEGORY', $category->getTitle()), SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::marketplaces(array(), false));
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
		$step->loadBySequence($category->getWorkflow()->id, SOCIAL_TYPE_MARKETPLACES, $currentStep);

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
		$this->page->breadcrumb(JText::_('COM_ES_PAGE_TITLE_MARKETPLACE'), ESR::marketplaces());
		$this->page->breadcrumb(JText::_('COM_ES_MARKETPLACES_NEW_LISTING'), ESR::marketplaces(array('layout' => 'create')));
		$this->page->breadcrumb($step->_('title'));

		// Set the page title
		ES::document()->title($step->get('title'));

		// Set the callback for the triggered custom fields
		$callback = array($fields->getHandler(), 'getOutput');

		// Trigger onRegister for custom fields.
		if (!empty($customFields)) {
			$fields->trigger('onRegister', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args, $callback);
		}
// dump($customFields);
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
					$step->permalink = ESR::marketplaces(array('layout' => 'steps', 'step' => $counter));
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

		return parent::display('site/marketplaces/steps/default');
	}



	/**
	 * Post process after a video is stored
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function save(SocialVideo $video, $isNew, $file)
	{
		// If there's an error, redirect them back to the form
		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());

			$options = array('layout' => 'form');

			if (!$video->isNew()) {
				$options['id'] = $video->id;
			}

			if ($video->isCreatedInCluster()) {
				$options['uid'] = $video->uid;
				$options['type'] = $video->type;
			}

			$url = FRoute::videos($options, false);

			return $this->app->redirect($url);
		}

		$message = 'COM_EASYSOCIAL_VIDEOS_ADDED_SUCCESS';

		if (!$isNew) {
			$message = 'COM_EASYSOCIAL_VIDEOS_UPDATED_SUCCESS';
		}

		// If this is a video link, we should just redirect to the video page.
		if ($video->isLink()) {

			$url = $video->getPermalink(false);

			$this->setMessage($message, SOCIAL_MSG_SUCCESS);
			$this->info->set($this->getMessage());

			return $this->app->redirect($url);
		}


		// Should we redirect the user to the progress page or redirect to the pending video page
		$options = array('id' => $video->getAlias());

		if ($isNew && $file || !$isNew && $file) {
			// If video will be processed by cronjob, do not redirect to the process page
			if (!$this->config->get('video.autoencode')) {
				$options = array('filter' => 'pending');

				if ($isNew) {
					$message = 'COM_EASYSOCIAL_VIDEOS_UPLOAD_SUCCESS_AWAIT_PROCESSING';
				}
			} else {
				$options['layout'] = 'process';
				$message = 'COM_EASYSOCIAL_VIDEOS_UPLOAD_SUCCESS_PROCESSING_VIDEO_NOW';
			}
		}

		if (!$isNew && !$file && $video->isPublished()) {
			$options['layout'] = 'item';
		}

		$this->setMessage($message, SOCIAL_MSG_SUCCESS);
		$this->info->set($this->getMessage());

		if ($video->isCreatedInCluster()) {
			$options['uid'] = $video->uid;
			$options['type'] = $video->type;
		}

		$url = ESR::videos($options, false);
		return $this->app->redirect($url);
	}

	/**
	 * Post processing after tag filters is saved
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFilter($uid, $clusterType)
	{
		$video = ES::video($uid, $clusterType);

		$this->info->set($this->getMessage());

		$redirect = $video->getAllVideosLink();
		$redirect = $this->getReturnUrl($redirect);

		return $this->app->redirect($redirect);
	}

	/**
	 * Displays the edit marketplace page.
	 *
	 * @since  3.3
	 * @access public
	 */
	public function edit($errors = null)
	{
		$this->checkFeature();

		ES::requireLogin();

		ES::checkCompleteProfile();

		$info = $this->info;

		if (!empty($errors)) {
			$info->set($this->getMessage());
		}

		$my = ES::user();

		$helper = $this->getHelper('Edit');
		$listing = $helper->getActiveListing();

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		if (!$listing->isOwner() && !$my->isSiteAdmin()) {

			$info->set(false, JText::_('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_EDIT_LISTING'), SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::marketplaces());
		}

		ES::language()->loadAdmin();

		$this->page->breadcrumb(JText::_('COM_ES_PAGE_TITLE_MARKETPLACE'), ESR::marketplaces());
		$this->page->breadcrumb($listing->getTitle(), $listing->getPermalink());
		$this->page->breadcrumb(JText::_('COM_ES_PAGE_TITLE_EDIT_LISTING'));

		$this->page->title(JText::sprintf('COM_ES_PAGE_TITLE_EDIT_LISTING_TITLE', $listing->getTitle()));

		$steps = $helper->getListingSteps();

		$fieldsModel = ES::model('Fields');

		$fieldsLib = ES::fields();

		// Enforce privacy to be false for listing
		$fieldsLib->init(array('privacy' => false));

		$callback = array($fieldsLib->getHandler(), 'getOutput');

		$conditionalFields = array();

		foreach ($steps as &$step) {
			$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $listing->id, 'dataType' => SOCIAL_TYPE_MARKETPLACE, 'visible' => SOCIAL_EVENT_VIEW_EDIT));

			if (!empty($step->fields)) {
				$post = $this->input->post->getArray();
				$args = array(&$post, &$listing, $errors);
				$fieldsLib->trigger('onEdit', SOCIAL_TYPE_MARKETPLACE, $step->fields, $args, $callback);

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

		// retrieve listing's approval the rejected reason.
		// $rejectedReasons = array();
		// if ($listing->isDraft()) {
		// 	$rejectedReasons = $listing->getRejectedReasons();
		// }

		$this->set('conditionalFields', $conditionalFields);
		$this->set('listing', $listing);
		$this->set('steps', $steps);
		// $this->set('rejectedReasons', $rejectedReasons);
		$this->set('activeStep', $activeStep);

		echo parent::display('site/marketplaces/edit/default');
	}

	/**
	 * Post action after selecting a category for creation to redirect to steps.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function selectCategory($container = null)
	{
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {

			// Support for group marketplaces
			// If there is a group id, we redirect back to the group instead
			$groupId = $this->input->getInt('group_id');
			if (!empty($groupId)) {
				$group = ES::group($groupId);

				return $this->redirect($group->getPermalink());
			}

			// Support for page marketplace
			$pageId = $this->input->getInt('page_id');
			if (!empty($pageId)) {
				$page = ES::page($pageId);

				return $this->redirect($page->getPermalink());
			}

			if ($container) {
				return $this->redirect(ESR::marketplaces(array('layout' => 'create'), false));
			}

			return $this->redirect(ESR::marketplaces(array(), false));
		}

		$url = ESR::marketplaces(array('layout' => 'steps', 'step' => 1), false);

		return $this->redirect($url);
	}

	/**
	 * Post action after completing an listing creation
	 *
	 * @since  4.0
	 * @access public
	 */
	public function complete($listing)
	{
		$this->info->set($this->getMessage());

		if ($listing->isPublished()) {
			$options = array('layout' => 'item', 'id' => $listing->getAlias());

			if ($listing->isClusterListing()) {
				$cluster = $listing->getCluster();

				$options['uid'] = $cluster->getAlias();
				$options['type'] = $cluster->getType();
			}

			return $this->redirect(ESR::marketplaces($options, false));
		}

		return $this->redirect(ESR::marketplaces(array(), false));
	}

	/**
	 * Post processing after feature/unfeature an listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function feature(SocialMarketplace $listing)
	{
		$this->info->set($this->getMessage());

		$permalink = $listing->getPermalink(false);

		$returnUrl = $this->getReturnUrl($permalink);

		$this->redirect($returnUrl);
	}

	/**
	 * Post processing after publish/unpublish/delete/sold a listing
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function publish($listing)
	{
		$this->info->set($this->getMessage());

		// Get the redirection link
		$options = array();

		if ($listing->isClusterListing()) {
			$cluster = $listing->getCluster();
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $cluster->getType();
		}

		$redirect = ESR::marketplaces($options, false);

		return $this->redirect($redirect);
	}

	/**
	 * Post action after updating a marketplace listing to redirect to appropriately.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function update($listing = null, $isNew = 0)
	{
		$this->info->set($this->getMessage());

		if ($this->hasErrors() || empty($listing)) {
			return $this->redirect(ESR::marketplaces());
		}

		$url = '';
		if ($listing->isPending()) {
			$url = ESR::marketplaces(array(), false);
		} else {
			$url = $listing->getPermalink(false);
		}

		return $this->redirect($url);
	}

	/**
	 * Post action for saving a step during listing creation to redirect either to the next step or the complete page.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function saveStep($stepSession = null)
	{
		// Set any messages
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			if (!empty($stepSession)) {
				return $this->redirect(ESR::marketplaces(array('layout' => 'steps', 'step' => $stepSession->step), false));
			} else {
				return $this->redirect(ESR::marketplaces(array('layout' => 'steps', 'step' => 1), false));
			}
		}

		return $this->redirect(ESR::marketplaces(array('layout' => 'steps', 'step' => $stepSession->step), false));
	}
}
