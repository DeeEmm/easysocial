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

class EasySocialControllerMarketplaces extends EasySocialController
{
	/**
	 * Retrieves a list of marketplaces listing available on the site with a given filter
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function filter()
	{
		ES::checkToken();

		// Load up the model
		$model = ES::model('Marketplaces');

		// Get data from query
		$filter = $this->input->get('type', 'all', 'string');
		$categoryId = $this->input->get('categoryId', 0, 'int');
		$sorting = $this->input->get('sort', 'latest', 'word');
		$ordering = $this->input->get('ordering', 'start', 'word');
		$activeUserId = $this->input->get('activeUserId', null, 'int');
		$browseView = $this->input->get('browseView', false, 'bool');

		$activeUser = ES::user($activeUserId);

		// Pagination
		$limit = $this->input->getInt('limit', ES::getLimit('marketplaces_limit'));

		// $limit = 1;
		$limitstart = $this->input->getInt('limitstart', 0);

		// Default options
		$featuredListings = false;
		$activeCategory = false;

		$options = [
			'state' => SOCIAL_STATE_PUBLISHED,
			'sort' => $ordering,
			'type' => $this->my->isSiteAdmin() ? 'all' : 'user',
			'featured' => false,
			'limit' => $limit,
			'limitstart' => $limitstart,
			'filter' => $filter
		];

		// Support for page id
		$clusterId = $this->input->get('clusterId', '', 'string');
		$uid = $this->input->get('uid', '', 'string');
		$utype = $this->input->get('utype', '', 'string');

		$clusterType = false;
		$clusterOption = [];
		$cluster = false;

		if ($uid && $utype) {
			$options['uid'] = $uid;
			$options['type'] = $utype;

			if ($utype != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($utype, $uid);
				$clusterType = $cluster->getType();

				$clusterOption['type'] = $clusterType;
				$clusterOption['uid'] = $cluster->id;
			}
		}

		$distance = $this->config->get('marketplaces.nearby.radius');

		// Filter by nearby listings
		if ($filter === 'nearby') {
			$distance = $this->input->get('distance', $distance, 'int');

			$options['location'] = true;
			$options['distance'] = $distance;
			$options['latitude'] = $this->input->getString('latitude');
			$options['longitude'] = $this->input->getString('longitude');
			$options['range'] = '<=';

			$session = JFactory::getSession();

			$userLocation = $session->get('marketplaces.userlocation', [], SOCIAL_SESSION_NAMESPACE);

			$hasLocation = !empty($userLocation) && !empty($userLocation['latitude']) && !empty($userLocation['longitude']);

			if (!$hasLocation) {
				$userLocation['latitude'] = $options['latitude'];
				$userLocation['longitude'] = $options['longitude'];

				$session->set('marketplaces.userlocation', $userLocation, SOCIAL_SESSION_NAMESPACE);
			}

			// Need to get featured listing separately here
			$featuredOptions = array('state' => SOCIAL_STATE_PUBLISHED, 'featured' => true);

			if ($clusterType == SOCIAL_TYPE_PAGE || $clusterType == SOCIAL_TYPE_GROUP) {
				$featuredOptions = array_merge($featuredOptions, $clusterOption);
			}

			$featuredOptions['location'] = true;
			$featuredOptions['distance'] = $distance;
			$featuredOptions['latitude'] = $this->input->getString('latitude');
			$featuredOptions['longitude'] = $this->input->getString('longitude');
			$featuredOptions['range'] = '<=';

			$featuredListings = $model->getListings($featuredOptions);
		}

		// Filter by category
		if ($filter === 'category') {
			$category = ES::table('MarketplaceCategory');
			$category->load($categoryId);

			$activeCategory = $category;

			$options['category'] = $categoryId;

			// check if this category is a container or not
			if ($category->container) {
				// Get all child ids from this category
				$categoryModel = ES::model('MarketplaceCategories');
				$childs = $categoryModel->getChildCategories($category->id, [], ['state' => SOCIAL_STATE_PUBLISHED]);

				$childIds = [];

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}
		}

		if (($filter === 'all' || $filter == 'category') && $browseView) {
			// Need to get featured listings separately here
			$featuredOptions = ['state' => SOCIAL_STATE_PUBLISHED, 'featured' => true];

			if ($clusterType == SOCIAL_TYPE_PAGE || $clusterType == SOCIAL_TYPE_GROUP) {
				$featuredOptions = array_merge($featuredOptions, $clusterOption);
			}

			if ($activeCategory) {
				$featuredOptions['category'] = $options['category'];
			}

			$featuredListings = $model->getListings($featuredOptions);
		}

		if ($activeUserId && !$clusterType && !$browseView && $filter === 'created') {
			$options['user_id'] = $activeUserId;
			$options['type'] = 'user';
			$options['featured'] = 'all';
		}

		// Filtering by featured listing
		if ($filter === 'featured') {
			$options['featured'] = true;
		}

		// Filter listings by current logged in user as creator
		if ($filter == 'created' || (($filter == 'all' || $filter == 'category') && !$browseView)) {
			$options['includeFeatured'] = true;
		}

		if ($filter == 'unpublished') {
			$options['state'] = SOCIAL_STATE_UNPUBLISHED;
		}

		$listings = $model->getListings($options);
		$pagination = $model->getPagination();

		if (!$browseView && !$cluster) {
			$pagination->setVar('userid', $activeUser->getAlias());
		}

		if ($filter === 'nearby') {
			$distance = $this->input->get('distance', $distance, 'int');

			$pagination->setVar('distance', $distance);
		}

		if ($cluster) {
			$pagination->setVar('uid', $cluster->getAlias());
			$pagination->setVar('type', $cluster->cluster_type);
		}

		// Set the pagination if needed
		$Itemid = $this->input->getInt('Itemid', ESR::getItemId('marketplaces'));
		$pagination->setVar('Itemid', $Itemid);
		$pagination->setVar('view', 'marketplaces');

		// Router already include categories filter if category id is present
		if (!$activeCategory && $filter != 'category') {
			$pagination->setVar('filter', $filter);
		}

		$pagination->setVar('ordering', $ordering);

		if ($activeCategory) {
			$pagination->setVar('categoryid', $activeCategory->getAlias());
		}

		return $this->view->call(__FUNCTION__, $filter, $listings, $pagination, $activeCategory, $featuredListings, $browseView, $activeUserId);
	}

	/**
	 * Occurs when user tries to select marketplace category
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function selectCategory()
	{
		// Ensure that the user is logged in
		ES::requireLogin();

		// Get the category id
		$id = $this->input->get('category_id', 0, 'int');

		// Try to load the category
		$category = ES::table('MarketplaceCategory');
		$category->load($id);

		if (!$category->id || !$id) {
			$this->view->setMessage('COM_ES_MARKETPLACES_INVALID_CATEGORY_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check for category container
		if ($category->container) {
			$this->view->setMessage('COM_ES_MARKETPLACES_CONTAINER_NOT_ALLOWED', ES_ERROR);
			return $this->view->call(__FUNCTION__, true);
		}

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = [&$this->my, &$category, &$canCreateInCategory];

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_MARKETPLACE, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_LISTING_IN_CATEGORY', $category->getTitle()), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$session = JFactory::getSession();

		// Differentiate the id between group listing and page listing if exists
		$sessionId = $session->getId();

		// Get the group id to see if this is coming from group listing creation
		$groupId = $this->input->getInt('group_id');

		// Get the page id to see if this is coming from page listing creation
		$pageId = $this->input->getInt('page_id');

		// if this is not cluster's marketplace, we check user ACL
		if (empty($groupId) && empty($pageId)) {
			if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('marketplaces.sell')) {
				$this->view->setMessage('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_LISTING', ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}

			// Ensure that the user did not exceed his limits
			if (!$this->my->isSiteAdmin() && $this->my->getAccess()->intervalExceeded('marketplaces.limit', $this->my->id)) {
				$this->view->setMessage('COM_ES_MARKETPLACES_EXCEEDED_CREATE_LISTING_LIMIT', ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}
		}

		$session->set('category_id', $category->id, SOCIAL_SESSION_NAMESPACE);

		$stepSession = ES::table('StepSession');
		$stepSession->load(['session_id' => $sessionId, 'type' => SOCIAL_TYPE_MARKETPLACE]);

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
		$stepSession->type = SOCIAL_TYPE_MARKETPLACE;

		$stepSession->set('step', 1);
		$stepSession->addStepAccess(1);

		if (!empty($pageId)) {
			$page = ES::page($pageId);

			if (!$page->canCreateListing()){
				$this->view->setError(JText::_('COM_ES_PAGES_MARKETPLACES_NO_PERMISSION_TO_CREATE_LISTING'));
				return $this->view->call(__FUNCTION__);
			}

			$stepSession->setValue('page_id', $pageId);
		} else if (!empty($groupId)) {
			$group = ES::group($groupId);

			if (!$group->canCreateListing()) {
				$this->view->setError(JText::_('COM_ES_GROUPS_MARKETPLACES_NO_PERMISSION_TO_CREATE_LISTING'));
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
	 * Whenever user clicks on the next step during listing creation
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function saveStep()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the session data
		$session = JFactory::getSession();
		$stepSession = ES::table('StepSession');
		$stepSession->load(['session_id' => $session->getId(), 'type' => SOCIAL_TYPE_MARKETPLACE]);

		if (empty($stepSession->step)) {
			$this->view->setMessage('COM_EASYSOCIAL_EVENTS_UNABLE_TO_DETECT_CREATION_SESSION', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$registry = ES::registry();
		$registry->load($stepSession->values);

		$groupId = $registry->get('group_id');
		$pageId = $registry->get('page_id');

		if (empty($groupId) && empty($pageId)) {
			// Check if the user is allowed to create listing
			if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('marketplaces.sell')) {
				$this->view->setMessage('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_LISTING', ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}

			// Check if the user exceeds the limit
			if (!$this->my->isSiteAdmin() && $this->my->getAccess()->intervalExceeded('marketplaces.limit', $this->my->id) ) {
				$this->view->setMessage('COM_ES_MARKETPLACES_EXCEEDED_CREATE_LISTING_LIMIT', ES_ERROR);

				return $this->view->call(__FUNCTION__);
			}
		}

		$category = ES::table('MarketplaceCategory');
		$category->load($stepSession->uid);
		$sequence = $category->getSequenceFromIndex($stepSession->step, SOCIAL_EVENT_VIEW_REGISTRATION);

		if (empty($sequence)) {
			$this->view->setMessage('COM_ES_NO_VALID_CREATION_STEP', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load the steps and fields
		$step = ES::table('FieldStep');
		$step->load(['workflow_id' => $category->getWorkflow()->id, 'type' => SOCIAL_TYPE_MARKETPLACES, 'sequence' => $sequence]);

		// Get the fields
		$fieldsModel  = ES::model('Fields');
		$customFields = $fieldsModel->getCustomFields(['step_id' => $step->id, 'visible' => SOCIAL_EVENT_VIEW_REGISTRATION]);

		// Get from request
		$files = $this->input->files->getArray();
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

		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession];

		// Load up the fields library so we can trigger the field apps
		$fieldsLib = ES::fields();

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession];

		// some data need to be retrieved in raw value before store into the step session table
		$fieldsLib->trigger('onFormatData', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args);

		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession];

		$callback  = [$fieldsLib->getHandler(), 'validate'];

		$errors = $fieldsLib->trigger('onRegisterValidate', SOCIAL_FIELDS_GROUP_MARKETPLACE, $customFields, $args, $callback);

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
		$model = ES::model('Marketplaces');

		// Create the new listing
		$listing = $model->createListing($stepSession);

		if (!$listing->id) {
			$errors = $model->getError();

			$this->view->setMessage($errors, ES_ERROR);

			return $this->view->call(__FUNCTION__, $stepSession);
		}

		$stepSession->delete();

		if ($listing->isPublished()) {

			$options = [];

			// Special case for page. If this is listing page we need to assign the post actor
			// The post acor will  always be the page since non admin cant create listing in page.
			if ($listing->type == SOCIAL_TYPE_PAGE) {
				$options['postActor'] = SOCIAL_TYPE_PAGE;
			}

			// Create new stream item
			$listing->createStream($listing->user_id, 'create', $options);

			$this->view->setMessage('COM_ES_MARKETPLACES_CREATED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		} else {
			$this->view->setMessage('COM_ES_MARKETPLACES_CREATED_PENDING_APPROVAL', SOCIAL_MSG_INFO);
		}

		return $this->view->call('complete', $listing);
	}

	/**
	 * Update a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the listing data .
		$id = $this->input->get('id', 0, 'int');

		// Load the listing
		$listing = ES::marketplace($id);

		$isNew = empty($listing->id);

		if (empty($listing) || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->isPublished() && !$listing->isDraft()) {
			return $this->view->exception('COM_ES_MARKETPLACES_LISTING_UNAVAILABLE');
		}

		if (!$this->my->isSiteAdmin() && !$listing->isOwner() && !$listing->isClusterOwner()) {
			$this->view->setMessage('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_EDIT_LISTING', ES_ERROR);

			return $this->view->call(__FUNCTION__, $listing);
		}

		$post = $this->input->post->getArray();
		$data = [];

		$disallowed = [ES::token(), 'option', 'task', 'controller'];

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

		$fields = ES::model('Fields')->getCustomFields(['group' => SOCIAL_TYPE_MARKETPLACE, 'workflow_id' => $listing->getWorkflow()->id, 'visible' => SOCIAL_EVENT_VIEW_EDIT, 'data' => true, 'dataId' => $listing->id, 'dataType' => SOCIAL_TYPE_MARKETPLACE]);

		$data['isNew'] = $isNew;

		$fieldsLib = ES::fields();

		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$listing];

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_MARKETPLACE, $fields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$listing];

		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_MARKETPLACE, $fields, $args, [$fieldsLib->getHandler(), 'validate']);

		if (!empty($errors)) {
			$this->view->setMessage('COM_ES_ERRORS_IN_FORM', ES_ERROR);

			$this->input->set('view', 'marketplaces');
			$this->input->set('layout', 'edit');

			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_MARKETPLACE, $fields, $args, [$fieldsLib->getHandler(), 'beforeSave']);

		if (!empty($errors)) {
			$this->view->setMessage('COM_ES_ERRORS_IN_FORM', ES_ERROR);

			$this->input->set('view', 'marketplaces');
			$this->input->set('layout', 'edit');

			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		if ($listing->isDraft() || $this->my->getAccess()->get('marketplaces.moderate')) {
			$listing->state = SOCIAL_MARKETPLACE_UPDATE_PENDING;
		}

		if ($this->my->isSiteAdmin() || !$this->config->get('marketplaces.editmoderation')) {
			$listing->state = SOCIAL_STATE_PUBLISHED;
		}

		// Trigger events
		$dispatcher = ES::dispatcher();
		$triggerArgs = [&$listing, &$this->my, false];

		// @trigger: onMarketplaceBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onMarketplaceBeforeSave', $triggerArgs);

		$listing->save();

		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onMarketplaceAfterSave', $triggerArgs);

		$model = ES::model('Marketplaces');

		if ($this->my->getAccess()->get('marketplaces.moderate') || !$this->my->isSiteAdmin()) {
			$model->notifyAdmins($listing, true);
		}

		$fieldsLib->trigger('onEditAfterSave', SOCIAL_FIELDS_GROUP_MARKETPLACE, $fields, $args);

		$listing->bindCustomFields($data);

		$fieldsLib->trigger('onEditAfterSaveFields', SOCIAL_FIELDS_GROUP_MARKETPLACE, $fields, $args);

		if ($listing->isPublished()) {
			$listing->createStream($this->my->id, 'update');
		}

		$messageLang = $listing->isPending() ? 'COM_ES_MARKETPLACES_UPDATED_PENDING_APPROVAL' : 'COM_ES_MARKETPLACES_UPDATED_SUCCESSFULLY';

		$this->view->setMessage($messageLang, SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $listing, (int) $isNew);
	}

	public function approveListing()
	{
		$id = $this->input->getInt('id', 0);

		$listing = ES::marketplace($id);

		if (empty($listing) || empty($listing->id)) {
			$this->view->setMessage('COM_ES_MARKETPLACES_INVALID_LISTING_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check if this is moderation from frontend
		$isModerate = $this->input->get('moderate', false, 'bool');

		// Check the key
		$key = $this->input->getString('key');

		if (!$isModerate && $key != $listing->key) {
			$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING', $listing->getTitle()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$state = $listing->approve();

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_LISTING_APPROVE_FAILED', $listing->getTitle()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_LISTING_APPROVE_SUCCESS', $listing->getTitle()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $listing);
	}

	public function rejectListing()
	{
		$id = $this->input->getInt('id', 0);

		$listing = ES::marketplace($id);

		if (empty($listing) || empty($listing->id)) {
			$this->view->setMessage('COM_ES_MARKETPLACES_INVALID_LISTING_ID', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Check if this is moderation from frontend
		$isModerate = $this->input->get('moderate', false, 'bool');

		// Check the key
		$key = $this->input->getString('key');

		if (!$isModerate && $key != $listing->key) {
			$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING', $listing->getTitle()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$state = $listing->reject();

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_LISTING_REJECT_FAILED', $listing->getTitle()), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage(JText::sprintf('COM_ES_MARKETPLACES_LISTING_REJECT_SUCCESS', $listing->getTitle()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $listing);
	}

	/**
	 * Set an listing as featured
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function setFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the listing
		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canFeature()) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		// Set the listing to featured
		$listing->setFeatured();

		$this->view->setMessage('COM_ES_MARKETPLACES_LISTING_FEATURE_SUCCESS', SOCIAL_MSG_SUCCESS);
		return $this->view->call('feature', $listing);
	}

	/**
	 * Set an listing as featured
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function removeFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the listing
		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canUnfeature()) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		// Set the listing to featured
		$listing->removeFeatured();

		$this->view->setMessage('COM_ES_MARKETPLACES_LISTING_UNFEATURE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call('feature', $listing);
	}

	/**
	 * Unpublishes a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function unpublish()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canUnpublish($this->my->id)) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		// Unpublish the listing
		$listing->unpublish();

		$this->view->setMessage('COM_ES_MARKETPLACES_UNPUBLISH_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call('publish', $listing);
	}

	/**
	 * Publishes a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function publish()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canPublish($this->my->id)) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		// Unpublish the listing
		$listing->publish();

		$this->view->setMessage('COM_ES_MARKETPLACES_PUBLISH_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call('publish', $listing);
	}

	/**
	 * Mark a listing as sold
	 *
	 * @since   4.0.1
	 * @access  public
	 */
	public function markAsSold()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canMarkAsSold($this->my->id)) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		$listing->markAsSold();

		$this->view->setMessage('COM_ES_MARKETPLACES_MARK_SOLD_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call('publish', $listing);
	}

	/**
	 * Mark a listing as available
	 *
	 * @since   4.0.1
	 * @access  public
	 */
	public function markAvailable()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canMarkAvailable($this->my->id)) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		// it is the samee as publish where the state is 1
		$listing->publish();

		$this->view->setMessage('COM_ES_MARKETPLACES_MARK_AVAILABLE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call('publish', $listing);
	}

	/**
	 * Allows caller to delete a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->view->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		if (!$listing->canDelete($this->my->id)) {
			return $this->view->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		$listing->delete();

		$this->view->setMessage('COM_ES_MARKETPLACES_LISTING_DELETE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call('publish', $listing);
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

		$model = ES::model('MarketplaceCategories');
		$subcategories = $model->getImmediateChildCategories($parentId, SOCIAL_TYPE_MARKETPLACE, $profileId);

		$this->view->call(__FUNCTION__, $subcategories, $groupId, $pageId, $backId);
	}

	/**
	 * Uploading photos in marketplaces
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function uploadPhotos()
	{
		ES::requireLogin();
		ES::checkToken();

		$access = $this->my->getAccess();
		$limit = $access->get('photos.uploader.maxsize') . 'M';

		$options = ['name' => 'file', 'maxsize' => $limit];
		$inputName = $this->input->get('inputName', '', 'string');

		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialResponse) {
			$this->view->setMessage($file->message, ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load up the image library so we can get the appropriate extension
		$image 	= ES::image();
		$image->load($file['tmp_name']);

		// Copy this to temporary location first
		$date = ES::date();

		$session = JFactory::getSession();
		$uniquePath = md5($session->getId() . '_listing');
		$folder = 'tmp/' . $uniquePath . '_listing_photos';

		$tmpPath = SOCIAL_MEDIA . '/' . $folder;

		if (!JFolder::exists($tmpPath)) {
			ES::makeFolder($tmpPath);
		}

		$source = $file['tmp_name'];
		$tmpName = md5($file['name'] . 'listing' . $date->toSql()) . $image->getExtension();
		$target = $tmpPath . '/' . $tmpName;

		// Try to copy the file to the new location
		$state = JFile::copy($source, $target);

		// $state = $image->save($target);

		if (!$state) {
			$this->view->setMessage(JText::_('PLG_FIELDS_AVATAR_ERROR_UNABLE_TO_MOVE_FILE'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$relPath = $folder . '/' . $tmpName;

		// Generate a tempory url for the image
		$uri = rtrim(JURI::root() , '/') . '/media/com_easysocial/' . $relPath;

		return $this->view->call(__FUNCTION__, $uri, $relPath, $inputName);
	}

	/**
	 * Renders the marketplace creation form
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function loadStoryForm()
	{
		ES::checkToken();
		ES::requireLogin();

		ES::language()->loadAdmin();

		// Get the category
		$id = $this->input->get('id', 0, 'int');
		$category = ES::table('MarketplaceCategory');
		$category->load($id);

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onEasySocialSelectMarketplaceCategory', $arguments);

		if (!$canCreateInCategory) {
			return $this->ajax->reject(JText::sprintf('COM_ES_MARKETPLACES_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()));
		}
		// integration end.

		$model = ES::model('Marketplaces');
		$fields = $model->getStoryFormFields($category);

		$theme = ES::themes();
		$theme->set('category', $category);

		foreach ($fields as $row) {
			$field = ES::table('Field');
			$field->bind($row);

			$params = $field->getParams();

			if ($row->element === 'title') {
				// We need to cater for the title option such as; default value or readonly
				$theme->set('titleField', $field);
				$theme->set('titleReadOnly', $params->get('readonly'));
			}

			if ($row->element === 'description') {
				$descField = new stdClass();
				$descField->title = $field->get('title');
				$descField->required = $field->required;
				$descField->default = $field->default;
				$theme->set('descField', $descField);
				// $theme->set('descriptionPlaceholder', $field->get('description'));
			}

			if ($row->element === 'price') {
				$priceField = new stdClass();
				$priceField->title = $field->get('title');
				$priceField->required = $field->required;
				$theme->set('priceField', $priceField);
			}

			if ($row->element === 'condition') {
				$conditionField = new stdClass();
				$conditionField->title = $field->get('title');
				$conditionField->required = $field->required;
				$theme->set('conditionField', $conditionField);
			}

			if ($row->element === 'stock') {
				$stockField = new stdClass();
				$stockField->title = $field->get('title');
				$stockField->required = $field->required;
				$stockField->default = $field->default;
				$theme->set('stockField', $stockField);
			}
		}

		$theme->set('currencyLabel', ES::getCurrencyOptions());
		$theme->set('currencyDefault', $this->config->get('marketplaces.currency'));

		$output = $theme->output('site/story/marketplaces/panel.form');

		return $this->ajax->resolve($output);
	}
}
