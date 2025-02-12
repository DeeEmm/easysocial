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

jimport('joomla.filesystem.file');

class EasySocialControllerPages extends EasySocialController
{
	/**
	 * Retrieves the page's stream items
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getStream()
	{
		ES::checkToken();

		// Load up the page
		$id = $this->input->get('pageId', 0, 'int');
		$page = ES::page($id);

		// Get the filter type
		$type = $this->input->get('filter', '', 'word');

		// Check if the page can be seen by this user
		if ($page->isClosed() && !$page->isMember() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Retrieve the stream
		$stream = ES::stream();

		// lets get the sticky posts 1st
		// Scheduled post shouldn't be allowed to be sticky. No need to search for this.
		if ($type != 'scheduled') {
			// Get the stickies item
			$stickies = $stream->getStickies(array('clusterId' => $page->id, 'clusterType' => SOCIAL_TYPE_PAGE, 'limit' => 0), array('perspective' => 'pages'));

			if ($stickies) {
				$stream->stickies = $stickies;
			}
		}

		// Determines if the user should see the story form
		if ($page->canViewStoryForm($this->my)) {
			$story = ES::story(SOCIAL_TYPE_PAGE);
			$story->setCluster($page->id, SOCIAL_TYPE_PAGE);
			$story->showPrivacy(false);

			if ($page->isAdmin()) {
				$story->showPostAs(true);
			}

			$story->setFilter($type);

			$stream->story = $story;

			// get the page params
			$params = $page->getParams();

			// Ensure the user has permission to see the story form.
			$permissions = $params->get('stream_permissions', null);

			// If the permissions is configured before
			if (!is_null($permissions)) {
				// If the user is not an admin, ensure that permissions has follower
				if (!$page->isAdmin() && !in_array('member', $permissions)) {
					unset($stream->story);
				}

				// If the user is an admin, ensure that permissions has admin
				if ($page->isAdmin() && !in_array('admin', $permissions) && !$page->isOwner()) {
					unset($stream->story);
				}
			}
		}

		// lets get stream items for this page
		$options = array('clusterId' => $page->id, 'clusterType' => SOCIAL_TYPE_PAGE, 'nosticky' => true);

		// Set the stream display options for this page
		$displayOptions = array('perspective' => 'pages');

		// Determines if we should only display moderated stream items
		if ($type == 'moderation') {
			$options['onlyModerated'] = true;
			$options['nosticky'] = true;

			unset($stream->story);
			unset($stream->stickies);
		}

		if ($type == 'scheduled') {
			$options['isScheduled'] = true;
			$options['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

			$displayOptions['disableActions'] = true;

			unset($stream->stickies);
		}

		$streamFilter = '';
		$filterId = $this->input->get('id', 0, 'int');

		// Retrieve the items related to the particular stream filter only
		if ($type == 'filters' && $filterId) {
			$streamFilter = ES::table('StreamFilter');
			$streamFilter->load($filterId);

			// Get the list of hashtags
			$hashtags = $streamFilter->getHashTag();
			$tags = explode(',', $hashtags);

			if ($tags) {
				$options['tag'] = $tags;

				$hashtagRule = $this->config->get('stream.filter.hashtag', '');
				if ($hashtagRule == 'and') {
					$options['matchAllTags'] = true;
				}

				if (isset($stream->story)) {
					$stream->story->setHashtags($tags);
				}
			}
		}

		$postTypes = $this->input->get('postTypes', array(), 'word');
		if ($postTypes) {
			$options['context'] = $postTypes;
		}

		$model = ES::model('Pages');

		$stream->get($options, $displayOptions);

		$page->saveFilterPreferences($type, $filterId);

		return $this->view->call(__FUNCTION__, $stream, $page, $streamFilter, $type);
	}

	/**
	 * Allows caller to trigger the delete method
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Only page owner and site admins are allowed to delete the page
		if (!$this->my->isSiteAdmin() && !$page->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to delete the page
		$page->delete();

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_PAGE_DELETED_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Delete the page's hashtag filter.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$pageId = $this->input->get('uid', 0, 'int');

		if (!$id) {
			$this->view->setMessage('Invalid filter id', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$filter = ES::table('StreamFilter');

		// make sure the user is the filter owner before we delete.
		$filter->load(array('id' => $id, 'uid' => $pageId, 'utype' => SOCIAL_TYPE_PAGE));

		if (!$filter->id) {
			$this->view->setMessage('Filter not found', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$filter->deleteItem();
		$filter->delete();

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_DELETED');

		return $this->view->call(__FUNCTION__, $pageId);
	}

	/**
	 * Unpublishes a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unpublish()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$page = ES::page($id);

		if (!$id || !$page->id) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED');
		}

		if (!$page->canUnpublish($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_NOT_ALLOWED_TO_UNPUBLISH_PAGE');
		}

		// Try to unpublish the page now
		$state = $page->unpublish();

		if (!$state) {
			return $this->view->exception($page->getError());
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_UNPUBLISHED_SUCCESS');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Publishes a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function publish()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$page = ES::page($id);

		if (!$id || !$page->id) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED');
		}

		if (!$page->canPublish($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_NOT_ALLOWED_TO_PUBLISH_PAGE');
		}

		// Try to unpublish the page now
		$state = $page->publish();

		if (!$state) {
			return $this->view->exception($page->getError());
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_PUBLISHED_SUCCESS');

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to set a page as a featured page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$id || !$page->id) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED');
		}

		if (!$page->canFeature()) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_NO_ACCESS');
		}

		$page->setFeatured();
		$this->view->setMessage('COM_EASYSOCIAL_PAGES_PAGE_FEATURE_SUCCESS');

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Allows caller to set a page as a featured page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$id || !$page->id) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED');
		}

		if (!$page->canFeature()) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_NO_ACCESS');
		}

		// Set it as featured
		$page->removeFeatured();
		$this->view->setMessage('COM_EASYSOCIAL_PAGES_PAGE_UNFEATURE_SUCCESS');

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Allows caller to response to invitation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function respondInvitation()
	{
		// If the user clicks on respond invitation via email, we do not want to check for tokens.
		$email = $this->input->get('email', '', 'default');
		$userId = $this->input->get('userId', 0, 'int');
		$inviterId = $this->input->get('inviterId', 0, 'int');
		$key = $this->input->get('key', '', 'default');

		if (!$email) {
			// Check for request forgeries
			ES::requireLogin();
			ES::checkToken();
		}

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$id || !$page) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($email && $userId && $page->key == $key) {
			$uid = $userId;
		} else {
			$uid = $this->my->id;
		}

		// Load the follower
		$follower = ES::table('PageMember');
		$follower->load(array('cluster_id' => $page->id, 'uid' => $uid));

		if (!$follower->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NOT_INVITED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the response action
		$action = $this->input->get('action', '', 'word');

		// If user rejected, just delete the invitation record.
		if ($action == 'reject') {
			$follower->delete();
			$message = JText::sprintf('COM_EASYSOCIAL_PAGES_REJECT_RESPONSE_SUCCESS', $page->getName());
		}

		if ($action == 'accept') {
			$follower->state = SOCIAL_PAGES_MEMBER_PUBLISHED;
			$follower->store();

			// Create stream when user accepts the invitation
			$page->createStream($this->my->id, 'like');

			ES::apps()->load(SOCIAL_TYPE_PAGE);
			$dispatcher = ES::dispatcher();

			// Trigger: onLikePage
			$dispatcher->trigger('user', 'onLikePage', array($this->my->id, $page));

			// @points: pages.like
			// Add points when user likes a page
			$points = ES::points();
			$points->assign('pages.like', 'com_easysocial', $this->my->id);

			// There is no need to notify other user if someone likes the page
			//$page->notifyMembers('like', array('userId' => $this->my->id));
			$message = JText::sprintf('COM_EASYSOCIAL_PAGES_ACCEPT_RESPONSE_SUCCESS', $page->getName());

			$page->inviteToEvents($this->my->id, $inviterId);
		}

		$this->view->setMessage($message);

		return $this->view->call(__FUNCTION__, $page, $action);
	}

	/**
	 * Allows user to unlike the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unlike()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page id
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that this is not the page owner.
		if ($page->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_PAGE_OWNER_NOT_ALLOWED_TO_UNLIKE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Remove the user from the page.
		$page->unlike($this->my->id);
		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_UNLIKE_PAGE_SUCCESS', $page->getName()));

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Allows user to like a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function like()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page id
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if (!$this->my->canLikePages()) {
			return $this->view->call('exceededLike');
		}

		// Create a member record for the page
		$page->createMember($this->my->id);

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Selects a category
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function selectCategory()
	{
		// Only logged in users are allowed to use this.
		ES::requireLogin();

		// Check if the user really has access to create pages
		if (!$this->my->getAccess()->allowed('pages.create') && !$this->my->isSiteAdmin()) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_NO_ACCESS_CREATE_PAGE');
		}

		// Ensure that the user did not exceed their page creation limit
		if ($this->my->getAccess()->intervalExceeded('pages.limit', $this->my->id) && !$this->my->isSiteAdmin()) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_EXCEEDED_LIMIT');
		}

		// Get the category id from request
		$id = $this->input->get('category_id', 0, 'int');

		$category = ES::table('PageCategory');
		$category->load($id);

		// If there's no profile id selected, throw an error.
		if (!$id || !$category->id) {
			return $this->view->exception('COM_EASYSOCIAL_PAGES_INVALID_PAGE_ID');
		}

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_PAGE, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->view->setMessage(JText::sprintf('COM_ES_PAGES_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}
		// integration end.


		// need to check if this clsuter category has creation limit based on user points or not.
		if (! $category->hasPointsToCreate($this->my->id)) {
			$requiredPoints = $category->getPointsToCreate($this->my->id);
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_INSUFFICIENT_POINTS', $requiredPoints), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// @task: Let's set some info about the profile into the session.
		$session = JFactory::getSession();
		$session->set('category_id', $id, SOCIAL_SESSION_NAMESPACE);

		// @task: Try to load more information about the current registration procedure.
		$stepSession = ES::table('StepSession');
		$stepSession->load(array('session_id' => $session->getId(), 'type' => SOCIAL_TYPE_PAGE));

		if (!$stepSession->session_id) {
			$stepSession->session_id = $session->getId();
		}

		$stepSession->uid = $category->id;
		$stepSession->type = SOCIAL_TYPE_PAGE;

		// When user accesses this page, the following will be the first page
		$stepSession->set('step', 1);

		// Add the first step into the accessible list.
		$stepSession->addStepAccess(1);
		$stepSession->store();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Creates a new page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->my->getAccess()->allowed('pages.create') && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS_CREATE_PAGE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that the user did not exceed their page creation limit
		if ($this->my->getAccess()->intervalExceeded('pages.limit', $this->my->id) && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_EXCEEDED_LIMIT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get current user's info
		$session = JFactory::getSession();

		// Get necessary info about the current registration process.
		$stepSession = ES::table('StepSession');
		$stepSession->load($session->getId());

		// Load the page category
		$category = ES::table('PageCategory');
		$category->load($stepSession->uid);

		$sequence = $category->getSequenceFromIndex($stepSession->step, SOCIAL_PAGES_VIEW_REGISTRATION);

		// Load the current step.
		$step = ES::table('FieldStep');
		$step->load(array('workflow_id' => $category->getWorkflow()->id, 'type' => SOCIAL_TYPE_CLUSTERS, 'sequence' => $sequence));

		// Merge the post values
		$registry = ES::get('Registry');
		$registry->load($stepSession->values);

		// Load up pages model
		$pagesModel = ES::model('Pages');

		// Get all published fields apps that are available in the current form to perform validations
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'visible' => SOCIAL_PAGES_VIEW_REGISTRATION));

		// Load json library.
		$json = ES::json();

		// Retrieve all file objects if needed
		$files = $this->input->files->getArray();
		$post = $this->input->post->getArray();
		$token = ES::token();

		$disallow = array($token, 'option', 'cid', 'controller', 'task', 'option', 'currentStep');

		// Process $_POST vars
		foreach($post as $key => $value) {
			if (!in_array($key, $disallow)) {

				if (is_array($value)) {
					$value = ES::json()->encode($value);
				}

				$registry->set($key, $value);
			}
		}

		// Convert the values into an array.
		$data = $registry->toArray();

		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession);

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		// @trigger onRegisterValidate
		$fieldsLib = ES::fields();

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession);

		// Some data need to be retrieved in raw value. let fire another trigger. #730
		$fieldsLib->trigger('onFormatData', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession);

		// Get the trigger handler
		$handler = $fieldsLib->getHandler();

		// Get error messages
		$errors = $fieldsLib->trigger('onRegisterValidate', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args, array($handler, 'validate'));

		// The values needs to be stored in a JSON notation.
		$stepSession->values = $json->encode($data);

		// Store registration into the temporary table.
		$stepSession->store();

		// Get the current step (before saving)
		$currentStep = $stepSession->step;

		// Add the current step into the accessible list
		$stepSession->addStepAccess($currentStep);

		// Bind any errors into the registration object
		$stepSession->setErrors($errors);

		// Saving was intercepted by one of the field applications.
		if (is_array($errors) && count($errors) > 0) {
			// @rule: If there are any errors on the current step, remove access to future steps to avoid any bypass
			$stepSession->removeAccess($currentStep);

			// @rule: Reset steps to the current step
			$stepSession->step = $currentStep;
			$stepSession->store();

			$this->view->setMessage('COM_EASYSOCIAL_REGISTRATION_SOME_ERRORS_IN_THE_REGISTRATION_FORM', ES_ERROR);

			return $this->view->call('saveStep', $stepSession);
		}

		// Determine if this is the last step.
		$completed = $step->isFinalStep(SOCIAL_PAGES_VIEW_REGISTRATION);

		// Update creation date
		$stepSession->created = ES::date()->toMySQL();

		// Since user has already came through this step, add the step access
		$nextStep = $step->getNextSequence(SOCIAL_PAGES_VIEW_REGISTRATION);

		if ($nextStep !== false) {
			$nextIndex = $stepSession->step + 1;
			$stepSession->addStepAccess($nextIndex);
			$stepSession->step = $nextIndex;
		}

		// Save the temporary data.
		$stepSession->store();

		// If this is the last step, we try to save all user's data and create the necessary values.
		if ($completed) {

			$page = $pagesModel->createPage($stepSession);

			// If there's no id, we know that there's some errors.
			if (!$page->id) {
				$errors = $pagesModel->getError();
				$this->view->setMessage($errors, ES_ERROR);

				return $this->view->call('saveStep', $stepSession);
			}

			// Get the registration data
			$sessionData = ES::registry($stepSession->values);

			// Clear existing session objects once the creation is completed.
			$stepSession->delete();

			// Default message
			$message = JText::_('COM_EASYSOCIAL_PAGES_CREATED_PENDING_APPROVAL');

			// If the page is published, we need to perform other activities
			if ($page->state == SOCIAL_STATE_PUBLISHED) {

				$message = JText::_('COM_EASYSOCIAL_PAGES_CREATED_SUCCESSFULLY');

				// Add activity logging when a user creates a new page.
				$stream = ES::stream();
				$streamTemplate = $stream->getTemplate();

				// Set the actor
				$streamTemplate->setActor($this->my->id, SOCIAL_TYPE_USER);

				// Set the context
				$streamTemplate->setContext($page->id, SOCIAL_TYPE_PAGES);

				// [Page] - For profile stream exclusion
				$streamTemplate->setPostAs(SOCIAL_TYPE_PAGE);

				$streamTemplate->setVerb('create');
				$streamTemplate->setSiteWide();
				$streamTemplate->setAccess('core.view');
				$streamTemplate->setCluster($page->id, SOCIAL_TYPE_PAGE, $page->type);

				// Set the params to cache the page data
				$registry = ES::registry();
				$registry->set('page', $page);

				// Set the params to cache the page data
				$streamTemplate->setParams($registry);

				// Add stream template.
				$stream->add($streamTemplate);

				// Update social goals
				$this->my->updateGoals('joincluster');
			}

			$this->view->setMessage($message);

			// Render the view now
			return $this->view->call('complete', $page);
		}

		return $this->view->saveStep($stepSession, $completed);
	}

	/**
	 * Service Hook for explorer
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function explorer()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page object
		$pageId = $this->input->get('uid', 0, 'int');
		$page = ES::page($pageId);

		// Determine if the viewer can really view items
		if (!$page->canViewItem()) {
			return $this->view->call(__FUNCTION__);
		}

		// Load up the explorer library
		$explorer = ES::explorer($page->id, SOCIAL_TYPE_PAGE);
		$hook = $this->input->get('hook', '', 'cmd');

		$result = $explorer->hook($hook);

		$exception = ES::response('Folder retrieval successful');

		return $this->view->call(__FUNCTION__, $exception, $result);
	}

	/**
	 * Updates the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Only allow user to edit if they have access
		if (!$page->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		$post = $this->input->post->getArray();
		$fieldsModel = ES::model('Fields');

		// Only fetch relevant fields for this user.
		$options = array('group' => SOCIAL_TYPE_PAGE, 'workflow_id' => $page->getWorkflow()->id, 'data' => true, 'dataId' => $page->id, 'dataType' => SOCIAL_TYPE_PAGE, 'visible' => SOCIAL_PAGES_VIEW_EDIT);
		$fields = $fieldsModel->getCustomFields($options);

		// Initialize default registry
		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array(ES::token(), 'option', 'task', 'controller');

		// Process $_POST vars
		foreach ($post as $key => $value) {
			if (!in_array($key, $disallowed)) {
				if (is_array($value)) {
					$value = json_encode($value);
				}
				$registry->set($key, $value);
			}
		}

		// Convert the values into an array.
		$data = $registry->toArray();

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		// @trigger onRegisterValidate
		$fieldsLib = ES::fields();

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$page);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$page);

		// Ensure that there is no errors.
		// @trigger onEditValidate
		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args, array($handler, 'validate'));

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_PROFILE_SAVE_ERRORS', ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'pages');
			$this->input->set('layout', 'edit');

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('edit', $errors, $data);
		}

		// @trigger onEditBeforeSave
		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args, array($handler, 'beforeSave'));

		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGE_ERRORS_IN_FORM', ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'pages');
			$this->input->set('layout', 'edit');

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		// if this page currently in draft state, mean this update is to submit to approval.
		// OR if pages required to be moderated, unpublish it first.
		if ($page->isDraft() || $this->my->getAccess()->get('pages.moderate')) {
			$page->state = SOCIAL_CLUSTER_UPDATE_PENDING;
		}

		if ($this->my->isSiteAdmin() || !$this->config->get('pages.editmoderation')) {
			$page->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		// Save the page now
		$page->save();

		$model = ES::model('Pages');

		// Send e-mail notification to site admin to approve / reject the page.
		if ($this->my->getAccess()->get('pages.moderate') && !$this->my->isSiteAdmin()) {
			$model->notifyAdminsModeration($page, true);
		}

		// Reconstruct args
		$args = array(&$data, &$page);

		// @trigger onEditAfterSave
		$fieldsLib->trigger('onEditAfterSave', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// Bind custom fields for the user.
		$page->bindCustomFields($data);

		// Reconstruct args
		$args = array(&$data, &$page);

		// @trigger onEditAfterSaveFields
		$fieldsLib->trigger('onEditAfterSaveFields', SOCIAL_FIELDS_GROUP_PAGE, $fields, $args);

		// update all stream's cluster_access related to this cluster.
		$page->updateStreamClusterAccess();

		if ($page->isPublished()) {
			// Add stream item to notify the world that this user updated their profile.
			$page->createStream($this->my->id, 'update', array('postActor' => SOCIAL_TYPE_PAGE));
		}

		$messageLang = $page->isPending() ? 'COM_EASYSOCIAL_PAGES_UPDATED_PENDING_APPROVAL' : 'COM_EASYSOCIAL_PAGES_PAGE_UPDATED_SUCCESSFULLY';

		$this->view->setMessage($messageLang);

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Retrieves the dashboard contents.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAppContents()
	{
		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$id || !$page) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if (!$page->canViewItem()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the app id.
		$appId = $this->input->get('appId', 0, 'int');
		$app = ES::table('App');
		$state = $app->load($appId);

		// If application id is not valid, throw an error.
		if (!$appId || !$state) {
			return $this->view->setMessage('COM_EASYSOCIAL_APPS_INVALID_APP_ID_PROVIDED', ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $page, $app);
	}

	/**
	 * Allows caller to invite other users to like the page.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function invite()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$id || !$page) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Determine if the user is a follower of the page
		if (!$page->isMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NEED_TO_BE_FOLLOWER_TO_INVITE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Get the list of followers that are invited
		$ids = $this->input->get('uid');

		if (!$ids) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_ENTER_FRIENDS_NAME_TO_INVITE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Flag if there is a user that has been invited before
		$invited = false;

		foreach ($ids as $id) {
			if (!$page->isInvited($id)) {
				$page->invite($id, $this->my->id);
			} else {
				$invited = true;
			}
		}

		$message = 'COM_EASYSOCIAL_PAGES_FRIENDS_INVITED_SUCCESS';
		$class = SOCIAL_MSG_SUCCESS;

		if ($invited) {
			$message = 'COM_EASYSOCIAL_PAGES_FRIENDS_HAS_BEEN_INVITED';
			$class = SOCIAL_MSG_INFO;
		}

		$this->view->setMessage($message, $class);

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Allows user to withdraw application to like a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function withdraw()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page id
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$page->deleteMember($this->my->id);

		return $this->view->call(__FUNCTION__, $page);
	}


	/**
	 * Approves a page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function approvePage()
	{
		// Get the page id from the request
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$page->approve();

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_PAGE_PUBLISHED_SUCCESSFULLY', $page->getName()));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Rejects a page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function rejectPage()
	{
		// Get the page id from the request
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$page->reject();

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_PAGE_REJECTED_SUCCESSFULLY', $page->getName()));
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Approves user to like a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve()
	{
		// Get the user's id
		$userIds = $this->input->get('userId', array(), 'array');

		if (!$userIds) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the page
		$id = $this->input->get('id', 0, 'int');

		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If there's a key provided, match it with the page
		$key = $this->input->get('key', '', 'default');

		// Ensure that the current user is the admin of the page
		if (!$page->isAdmin() && $page->key != $key) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($userIds) {
			foreach ($userIds as $userId) {
				$user = ES::user($userId);

				if (!$user->id) {
					$this->view->setMessage('COM_ES_INVALID_USER_PROVIDED', ES_ERROR);
					return $this->view->call(__FUNCTION__, $page);
				}

				// Approve the follower
				$page->approveUser($user->id);
			}
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_FOLLOWER_APPROVED_SUCCESS', $user->getName()));

		if (count($userIds) > 1) {
			$this->view->setMessage(JText::_('COM_ES_PAGES_FOLLOWERS_APPROVED_SUCCESS'), SOCIAL_MSG_SUCCESS);
		}

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Allows admin of a page to remove follower from the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeFollower()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page id
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Check if the user that is deleting is an admin of the page
		if (!$page->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Get the target user that needs to be removed
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$page->deleteMember($user->id, true);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_REMOVED_USER_SUCCESS', $user->getName()));
		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Rejects user from like the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function reject()
	{
		// Get the page id
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Ensure that the current user is the admin of the page
		if (!$page->canModerateLikeRequests()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Get the user's id
		$userIds = $this->input->get('userId', array(), 'array');

		if ($userIds) {
			foreach ($userIds as $userId) {
				$user = ES::user($userId);

				if (!$user->id) {
					$this->view->setMessage('COM_ES_INVALID_USER_PROVIDED', ES_ERROR);
					return $this->view->call(__FUNCTION__, $page);
				}

				// Reject the member
				$page->rejectUser($user->id);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_REJECTED_USER_SUCCESS');

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Cancel user invitation from page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancelInvite()
	{
		// Get the page id
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that the current user is the admin of the page
		if (!$page->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the user id
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		// Cancel member invitation
		$page->cancelInvitation($user->id);

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_REJECTED_USER_SUCCESS');
		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Stores the page's hashtag filter.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$pageId = $this->input->get('uid', 0, 'int');
		$post = $this->input->post->getArray();

		// Load the filter table
		$filter = ES::table('StreamFilter');

		if (!trim($post['title'])) {
			$this->view->setError('COM_EASYSOCIAL_PAGE_STREAM_FILTER_WARNING_TITLE_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__, $filter);
		}

		if (!trim($post['hashtag'])) {
			$this->view->setError('COM_EASYSOCIAL_PAGE_STREAM_FILTER_WARNING_HASHTAG_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__, $filter);
		}

		if ($id) {
			$filter->load($id);
		}

		$filter->title = $post['title'];
		$filter->uid = $pageId;
		$filter->utype = SOCIAL_TYPE_PAGE;
		$filter->user_id = $this->my->id;
		$filter->store();

		// now we save the filter type and content.
		if ($post['hashtag']) {
			$hashtag = trim($post[ 'hashtag' ]);
			$hashtag = str_replace('#', '', $hashtag);
			$hashtag = str_replace(' ', '', $hashtag);


			$filterItem = ES::table('StreamFilterItem');
			$filterItem->load(array('filter_id' => $filter->id, 'type' => 'hashtag'));

			$filterItem->filter_id = $filter->id;
			$filterItem->type = 'hashtag';
			$filterItem->content = $hashtag;

			$filterItem->store();
		} else {
			$filter->deleteItem('hashtag');
		}

		$this->view->setMessage('COM_EASYSOCIAL_PAGE_STREAM_FILTER_SAVED');
		return $this->view->call(__FUNCTION__, $filter, $pageId);
	}

	/**
	 * Allow callers to filter page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function filter()
	{
		ES::checkToken();

		$activeUserId = $this->input->get('userId', null, 'int');

		// If there is a user id, means we are viewing profile's page listing
		// $browseView: is a listing view of all page.
		$browseView = true;

		if ($activeUserId) {
			$browseView = false;
		}

		// Check if the caller passed us a category id
		$categoryId = $this->input->get('categoryId', 0, 'int');

		// Load the page model
		$model = ES::model('Pages');

		// Get the Filter
		$filter = $this->input->get('filter', '', 'cmd');

		// Get the sorting
		$sort = $this->input->get('ordering', 'latest', 'cmd');
		$isSortingRequest = $this->input->get('sort', '', 'boolean');

		// Build the options
		$options = array('state' => SOCIAL_CLUSTER_PUBLISHED, 'types' => $this->my->isSiteAdmin() ? 'all' : 'user');

		// Define the default values
		$options['category'] = false;
		$pages = array();
		$featuredPages = array();
		$category = null;

		if ($activeUserId && ($filter != 'participated' && $filter != 'invited')) {
			$options['uid'] = $activeUserId;
		}

		// Determine the pagination limit
		$limit = ES::getLimit('pages_limit');
		$options['limit'] = $limit;

		// Only exclude featured page when viewing all pages
		if ($browseView) {
			$options['featured'] = false;
		}

		if ($filter == 'mine') {
			$options['uid'] = $this->my->id;
			$options['types'] = 'all';
			$options['featured'] = '';
		}

		if ($filter == 'invited') {
			$options['invited'] = $this->my->id;
			$options['types'] = 'all';
			$options['featured'] = '';
		}

		if ($filter == 'pending') {
			// Pages that pending user's review.
			$options['uid'] = $this->my->id;
			$options['state'] = SOCIAL_CLUSTER_DRAFT;
			$options['types'] = 'user';
		}

		if ($filter == 'featured') {
			// featured page only.
			$options['featured'] = true;
		}

		if ($filter == 'liked') {
			// Pages that user has liked
			$options['liked'] = $this->my->id;
			$options['featured'] = '';
		}

		if ($filter == 'participated' && $activeUserId) {
			$options['liked'] = $activeUserId;
		}

		if ($filter == 'unpublished') {
			// allow site admin to view all the unpublished post
			if (!$this->my->isSiteAdmin()) {
				$options['uid'] = $this->my->id;
			}

			$options['state'] = SOCIAL_STATE_UNPUBLISHED;
		}

		if ($categoryId) {
			$category = ES::table('PageCategory');
			$category->load($categoryId);

			$options['category'] = $categoryId;

			// check if this category is a container or not
			if ($category->container) {
				// Get all child ids from this category
				$categoryModel = ES::model('ClusterCategory');
				$childs = $categoryModel->getChildCategories($category->id, array(), SOCIAL_TYPE_PAGE, array('state' => SOCIAL_STATE_PUBLISHED));

				$childIds = array();

				foreach ($childs as $child) {
					$childIds[] = $child->id;
				}

				if (!empty($childIds)) {
					$options['category'] = $childIds;
				}
			}
		}

		if ($sort) {
			$options['ordering'] = $sort;
		}

		$pages = $model->getPages($options);

		// Build the pagination
		$pagination = $model->getPagination();

		// Define the query strings
		$pagination->setVar('Itemid', ESR::getItemId('pages'));
		$pagination->setVar('view', 'pages');
		$pagination->setVar('filter', $filter);
		$pagination->setVar('ordering', $sort);

		if ($categoryId) {
			$pagination->setVar('categoryid', $category->getAlias());
			$pagination->setVar('filter', 'all');
		}

		if (!$browseView && $activeUserId) {
			$user = ES::User($activeUserId);
			$pagination->setVar('userid', $user->getAlias());
		}

		// We also need to get the featured pages based in the current options
		$featuredPages = array();
		if (($filter == 'all' || $categoryId) && !$isSortingRequest) {
			$options['featured'] = true;
			$featuredPages = $model->getPages($options);
		}

		return $this->view->call(__FUNCTION__, $filter, $sort, $pages, $pagination, $featuredPages, $category, $isSortingRequest, $activeUserId);

	}

	/**
	 * Retrieves the page's About info
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getInfo()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the page object
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		// Ensure that the ID provided is valid
		if (!$page || !$page->id) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_INVALID_PAGE_ID');
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that the user has access to view the page's item
		if (!$page->canViewItem()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the current active step
		$activeStep = $this->input->get('step', 1, 'int');

		// Get the entire About for the page
		$model = ES::model('Pages');
		$steps = $model->getAbout($page, $activeStep);

		return $this->view->call(__FUNCTION__, $steps);
	}

	/**
	 * Revoke the admin access of the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function demote()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->canPromoteMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$page->demoteUser($userId);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_DEMOTED_USER_SUCCESS', $user->getName()));
		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Promote the user to be admin of the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function promote()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$page->canPromoteMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$page->promoteUser($userId);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PAGES_PROMOTED_USER_SUCCESS', $user->getName()));
		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Suggest a list of pages to the user
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function suggest()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the search query
		$search = $this->input->get('search', '', 'word');

		// Get the exclusion list
		$exclusion = $this->input->get('exclusion', array(), 'array');

		// Determines if the user is an admin
		$options = array('unpublished' => false, 'exclusion' => $exclusion);

		if ($this->my->isSiteAdmin()) {
			$options['unpublished'] = true;
		}

		$model = ES::model('Pages');
		$pages = $model->search($search, $options);

		return $this->view->call(__FUNCTION__, $pages);
	}

	/**
	 * Allows caller to remove page avatar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeAvatar()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the current page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		// Only allow page admins to remove avatar
		if (!$page->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_PAGES_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		// Try to remove the avatar from the page now
		$page->removeAvatar();

		$this->view->setMessage('COM_EASYSOCIAL_PAGES_AVATAR_REMOVED_SUCCESSFULLY');

		return $this->view->call(__FUNCTION__, $page);
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
		$backId = $this->input->get('backId', 0, 'int');

		// Retrieve current logged in user profile type id
		$profileId = $this->my->getProfile()->id;

		$model = ES::model('ClusterCategory');
		$subcategories = $model->getImmediateChildCategories($parentId, SOCIAL_TYPE_PAGE, $profileId);

		$this->view->call(__FUNCTION__, $subcategories, $backId);
	}

	/**
	 * Allows caller to invite other users
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function sendInvites()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$page = ES::page($id);

		// We should not allow anyone to send invites if it has been disabled.
		if (!$page->canInvite()) {
			die();
		}

		// Get the list of emails
		$emails = $this->input->get('emails', '', 'html');

		if (!$emails) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_PLEASE_ENTER_EMAILS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $page);
		}

		$emails = explode("\n", $emails);

		// Get the message
		$message = $this->input->get('message', '', 'default');

		$model = ES::model('Pages');

		foreach ($emails as $email) {

			// Ensure that the e-mail is valid
			$email = trim($email);
			$valid = JMailHelper::isEmailAddress($email);

			if (!$valid) {
				$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_INVALID_EMAIL', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__, $page);
			}

			$table = ES::table('FriendInvite');

			// Check if this email has been invited by this user before
			$table->load(array('email' => $email, 'user_id' => $this->my->id, 'utype' => SOCIAL_TYPE_PAGE, 'uid' => $page->id));

			// Skip this if the user has already been invited before.
			if ($table->id) {
				continue;
			}

			// Check if the e-mail is already registered on the site
			$exists = $model->isEmailExists($email, $page->id);

			if ($exists) {
				$this->view->setMessage(JText::sprintf('COM_ES_FRIENDS_INVITE_EMAIL_EXISTS_IN_PAGE', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__, $page);
			}

			$table->email = $email;
			$table->user_id = $this->my->id;
			$table->message = $message;
			$table->utype = SOCIAL_TYPE_PAGE;
			$table->uid = $page->id;

			$table->store();
		}

		$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_SENT_INVITATIONS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $page);
	}
}
