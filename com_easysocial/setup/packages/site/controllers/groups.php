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

class EasySocialControllerGroups extends EasySocialController
{
	/**
	 * Selects a category
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function selectCategory()
	{
		// Only logged in users are allowed to use this.
		ES::requireLogin();

		// Check if the user really has access to create groups
		if (!$this->my->getAccess()->allowed('groups.create') && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS_CREATE_GROUP', ES_ERROR);
			return $this->view->call( __FUNCTION__ );
		}

		// Ensure that the user did not exceed their group creation limit
		if ($this->my->getAccess()->intervalExceeded('groups.limit', $this->my->id) && !$this->my->isSiteAdmin()) {

			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_EXCEEDED_LIMIT', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Get the category id from request
		$id = $this->input->get('category_id', 0, 'int');

		$category = ES::table('GroupCategory');
		$category->load($id);

		// If there's no profile id selected, throw an error.
		if (!$id || !$category->id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_GROUP_ID', ES_ERROR);
			return $this->view->call( __FUNCTION__ );
		}

		// to integrate with 3rd party cluster submission app.
		$canCreateInCategory = true;
		$arguments = array(&$this->my, &$category, &$canCreateInCategory);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onEasySocialSelectCategory', $arguments);

		if (!$canCreateInCategory) {
			$this->view->setMessage(JText::sprintf('COM_ES_GROUPS_NOT_ALLOWED_TO_CREATE_UNDER_CATEGORY', $category->getTitle()), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}
		// integration end.

		// need to check if this clsuter category has creation limit based on user points or not.
		if (!$category->hasPointsToCreate($this->my->id)) {
			$requiredPoints = $category->getPointsToCreate($this->my->id);
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_INSUFFICIENT_POINTS', $requiredPoints), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$session = JFactory::getSession();
		$session->set('category_id', $id, SOCIAL_SESSION_NAMESPACE);

		$stepSession = ES::table('StepSession');
		$stepSession->load(array('session_id' => $session->getId(), 'type' => SOCIAL_TYPE_GROUP));

		if (!$stepSession->session_id) {
			$stepSession->session_id = $session->getId();
		}

		$stepSession->uid = $category->id;
		$stepSession->type = SOCIAL_TYPE_GROUP;

		// When user accesses this page, the following will be the first page
		$stepSession->set('step', 1);

		// Add the first step into the accessible list.
		$stepSession->addStepAccess(1);
		$stepSession->store();

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
		$backId = $this->input->get('backId', 0, 'int');

		// Retrieve current logged in user profile type id
		$profileId = $this->my->getProfile()->id;

		$model = ES::model('ClusterCategory');
		$subcategories = $model->getImmediateChildCategories($parentId, SOCIAL_TYPE_GROUP, $profileId);

		$this->view->call(__FUNCTION__, $subcategories, $backId);
	}

	/**
	 * Allows user to remove his avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeAvatar()
	{
		ES::checkToken();

		// Get the current group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Only allow group admins to remove avatar
		if (!$group->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);

			return $this->view->call(__FUNCTION__, $group);
		}

		// Try to remove the avatar from the group now
		$group->removeAvatar();

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_AVATAR_REMOVED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Creates a new group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function store()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->my->getAccess()->allowed('groups.create') && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS_CREATE_GROUP', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that the user did not exceed their group creation limit
		if ($this->my->getAccess()->intervalExceeded('groups.limit', $this->my->id) && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_EXCEEDED_LIMIT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get current user's info
		$session = JFactory::getSession();

		// Get necessary info about the current registration process.
		$stepSession = ES::table('StepSession');
		$stepSession->load($session->getId());

		// Load the group category
		$category = ES::table('GroupCategory');
		$category->load($stepSession->uid);

		$sequence = $category->getSequenceFromIndex($stepSession->step, SOCIAL_GROUPS_VIEW_REGISTRATION);

		// Load the current step.
		$step = ES::table('FieldStep');
		$step->load(array('workflow_id' => $category->getWorkflow()->id, 'type' => SOCIAL_TYPE_CLUSTERS, 'sequence' => $sequence));

		// Merge the post values
		$registry = ES::get('Registry');
		$registry->load($stepSession->values);

		// Load up groups model
		$groupsModel = ES::model('Groups');

		// Get all published fields apps that are available in the current form to perform validations
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'visible' => SOCIAL_GROUPS_VIEW_REGISTRATION));

		// Temporarily need to supress errors from Joomla's input as this seems to be a bug in Joomla
		$files = @$this->input->files->getArray();
		$post = $this->input->post->getArray();

		$token = ES::token();

		$disallow = [$token, 'option', 'cid', 'controller', 'task', 'option', 'currentStep'];

		// Process $_POST vars
		foreach ($post as $key => $value) {
			if (!in_array($key, $disallow)) {
				if (is_array($value)) {
					$value = json_encode($value);
				}

				$registry->set($key, $value);
			}
		}


		// Convert the values into an array.
		$data = $registry->toArray();

		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession];

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		// @trigger onRegisterValidate
		$fieldsLib = ES::fields();

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args);

		// Rebuild the arguments since the data is already changed previously.
		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession];

		//some data need to be retrieved in raw value. let fire another trigger. #730
		$fieldsLib->trigger('onFormatData', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args);

		// Get the trigger handler
		$handler = $fieldsLib->getHandler();

		// Rebuild the arguments since the data is already changed previously.
		$args = [&$data, 'conditionalRequired' => $data['conditionalRequired'], &$stepSession];

		// Get error messages
		$errors = $fieldsLib->trigger('onRegisterValidate', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args, array($handler, 'validate'));

		// The values needs to be stored in a JSON notation.
		$stepSession->values = json_encode($data);

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
		$completed = $step->isFinalStep(SOCIAL_GROUPS_VIEW_REGISTRATION);

		// Update creation date
		$stepSession->created = ES::date()->toMySQL();

		// Since user has already came through this step, add the step access
		$nextStep = $step->getNextSequence(SOCIAL_GROUPS_VIEW_REGISTRATION);

		if ($nextStep !== false) {
			$nextIndex = $stepSession->step + 1;
			$stepSession->addStepAccess($nextIndex);
			$stepSession->step = $nextIndex;
		}

		// Save the temporary data.
		$stepSession->store();

		// If this is the last step, we try to save all user's data and create the necessary values.
		if ($completed) {
			// Create the group now.
			$group = $groupsModel->createGroup($stepSession);

			// If there's no id, we know that there's some errors.
			if (!$group->id) {
				$errors = $groupsModel->getError();

				$this->view->setMessage($errors, ES_ERROR);

				return $this->view->call('saveStep', $stepSession);
			}

			// Get the registration data
			$sessionData = ES::registry($stepSession->values);

			// Clear existing session objects once the creation is completed.
			$stepSession->delete();

			// Default message
			$message = JText::_('COM_EASYSOCIAL_GROUPS_CREATED_PENDING_APPROVAL');

			// If the group is published, we need to perform other activities
			if ($group->state == SOCIAL_STATE_PUBLISHED) {
				$message = JText::_('COM_EASYSOCIAL_GROUPS_CREATED_SUCCESSFULLY');

				// Add activity logging when a user creates a new group.
				$stream = ES::stream();
				$streamTemplate = $stream->getTemplate();

				// Set the actor
				$streamTemplate->setActor($this->my->id, SOCIAL_TYPE_USER);

				// Set the context
				$streamTemplate->setContext($group->id, SOCIAL_TYPE_GROUPS);

				$streamTemplate->setVerb('create');
				$streamTemplate->setSiteWide();
				$streamTemplate->setAccess('core.view');
				$streamTemplate->setCluster($group->id, SOCIAL_TYPE_GROUP, $group->type);

				// Set the params to cache the group data
				$registry = ES::registry();
				$registry->set('group', $group);

				// Set the params to cache the group data
				$streamTemplate->setParams($registry);

				// Add stream template.
				$stream->add($streamTemplate);

				// Update social goals
				$this->my->updateGoals('joincluster');
			}

			$this->view->setMessage($message, SOCIAL_MSG_SUCCESS);

			// Render the view now
			return $this->view->call('complete', $group);
		}

		return $this->view->saveStep($stepSession, $completed);
	}

	/**
	 * Allows caller to trigger the delete method
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		// Only group owner and site admins are allowed to delete the group
		if (!$this->my->isSiteAdmin() && !$group->isOwner()) {

			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to delete the group
		$group->delete();

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_GROUP_DELETED_SUCCESS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Updates the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Only allow user to edit if they have access
		if (!$group->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Get post data.
		$post = $this->input->post->getArray();

		$fieldsModel = ES::model('Fields');

		// Only fetch relevant fields for this user.
		$options = array('group' => SOCIAL_TYPE_GROUP , 'workflow_id' => $group->getWorkflow()->id , 'data' => true, 'dataId' => $group->id, 'dataType' => SOCIAL_TYPE_GROUP , 'visible' => SOCIAL_PROFILES_VIEW_EDIT);
		$fields = $fieldsModel->getCustomFields($options);

		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array( ES::token() , 'option' , 'task' , 'controller' );

		// Process $_POST vars
		foreach ($post as $key => $value) {
			if (!in_array( $key , $disallowed)) {
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
		$fieldsLib	= ES::fields();

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$group);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$group);

		// Ensure that there is no errors.
		// @trigger onEditValidate
		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args, array($handler, 'validate'));

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_PROFILE_SAVE_ERRORS', ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'groups');
			$this->input->set('layout', 'edit');

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('edit', $errors, $data);
		}

		// @trigger onEditBeforeSave
		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_GROUP, $fields , $args, array($handler, 'beforeSave'));

		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage('COM_EASYSOCIAL_PROFILE_ERRORS_IN_FORM', ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'groups');
			$this->input->set('layout', 'edit');

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		// if this group currently in draft state, mean this update is to submit to approval.
		// OR if groups required to be moderated, unpublish it first.
		// lets update the state.
		if ($group->isDraft() || $this->my->getAccess()->get('groups.moderate')) {
			$group->state = SOCIAL_CLUSTER_UPDATE_PENDING;
		}

		if ($this->my->isSiteAdmin() || !$this->config->get('groups.editmoderation')) {
			$group->state = SOCIAL_CLUSTER_PUBLISHED;
		}

		// Save the group now
		$group->save();

		$model = ES::model('Groups');

		// Send e-mail notification to site admin to approve / reject the group.
		if ($this->my->getAccess()->get('groups.moderate') && !$this->my->isSiteAdmin()) {
			$model->notifyAdminsModeration($group, true);
		}

		// Reconstruct args
		$args = array(&$data, &$group);

		// @trigger onEditAfterSave
		$fieldsLib->trigger('onEditAfterSave', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args);

		// Bind custom fields for the user.
		$group->bindCustomFields($data);

		// Reconstruct args
		$args = array(&$data, &$group);

		// @trigger onEditAfterSaveFields
		$fieldsLib->trigger('onEditAfterSaveFields', SOCIAL_FIELDS_GROUP_GROUP, $fields, $args);

		// update all stream's cluster_access related to this cluster.
		$group->updateStreamClusterAccess();

		// Add stream item to notify the world that this user updated their profile.
		if ($group->isPublished()) {
			$group->createStream($this->my->id, 'update');
		}

		$messageLang = $group->isPending() ? 'COM_EASYSOCIAL_GROUPS_UPDATED_PENDING_APPROVAL' : 'COM_EASYSOCIAL_GROUPS_PROFILE_UPDATED_SUCCESSFULLY';

		$this->view->setMessage($messageLang, SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Approves user to join a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function approve()
	{
		// Get the user's id
		$userIds = $this->input->get('userId', array(), 'array');

		if (!$userIds) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the group
		$id = $this->input->get('id', 0, 'int');

		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// If there's a key provided, match it with the group
		$key = $this->input->get('key', '', 'default');

		// Ensure that the current user is the admin of the group
		if (!$group->isAdmin() && $group->key != $key) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($userIds) {

			foreach ($userIds as $userId) {

				$user = ES::user($userId);

				if (!$user->id) {
					$this->view->setMessage('COM_ES_INVALID_USER_PROVIDED', ES_ERROR);
					return $this->view->call(__FUNCTION__, $group);
				}

				$group->approveUser($user->id);
			}
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_MEMBER_APPROVED_SUCCESS', $user->getName()), SOCIAL_MSG_SUCCESS);

		if (count($userIds) > 1) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_GROUPS_MEMBERS_APPROVED_SUCCESS'), SOCIAL_MSG_SUCCESS);
		}

		return $this->view->call(__FUNCTION__, $group);

	}

	/**
	 * Approves a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function approveGroup()
	{
		// Get the group id from the request
		$id = $this->input->get('id', 0, 'int');

		// Try to load the group object
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$group->approve();

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_GROUP_PUBLISHED_SUCCESSFULLY', $group->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Rejects a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function rejectGroup()
	{
		// Get the group id from the request
		$id = $this->input->get('id', 0, 'int');

		// Try to load the group object
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call( __FUNCTION__ );
		}

		$group->reject();

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_GROUP_REJECTED_SUCCESSFULLY', $group->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Rejects the user's request to join a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function reject()
	{
		// Get the group id
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Ensure that the current user is the admin of the group
		if (!$group->canModerateJoinRequests()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Get the user id
		$userIds = $this->input->get('userId', array(), 'array');

		if ($userIds) {

			foreach ($userIds as $userId) {
				$user = ES::user($userId);
				$group->rejectUser($user->id);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_REJECTED_USER_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Allows caller to cancel invites sent out to users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancelInvite()
	{
		// Get the group id
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that the current user is the admin of the group
		if ($group->isOwner() || $group->isAdmin() || $this->my->isSiteAdmin()) {

			// Get the user id
			$userId = $this->input->get('userId', 0, 'int');
			$user = ES::user($userId);

			// Cancel member invitation
			$group->cancelInvitation($user->id);
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_REJECTED_USER_SUCCESS', SOCIAL_MSG_SUCCESS);

			return $this->view->call(__FUNCTION__, $group);

		} else {

			$this->view->setMessage(JText::_('COM_EASYSOCIAL_GROUPS_NO_ACCESS'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}
	}

	/**
	 * Allows user to join a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function join()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group id
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the user's access as we want to limit the number of groups they can join
		if (!$this->my->canJoinGroups()) {
			return $this->view->call('exceededJoin');
		}

		// triggers
		$dispatcher = ES::dispatcher();

		$canJoin = true;
		$arguments = array(&$group, &$this->my, &$canJoin);
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onBeforeGroupJoin', $arguments);

		if (!$canJoin) {
			return $this->view->call('disallowedJoin');
		}

		// Create a member record for the group
		$group->createMember($this->my->id);

		// trigger on after group join
		$arguments = array(&$group, &$this->my);
		$dispatcher->trigger(SOCIAL_TYPE_GROUP, 'onAfterGroupJoin', $arguments);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Allows user to withdraw application to join a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function withdraw()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Remove the user from the group.
		$group->deleteMember($this->my->id);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Allows admin of a group to remove member from the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeMember()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		if (!$group->isOwner() && !$group->isAdmin() && !$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Get the target user that needs to be removed
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		// Remove the user from the group.
		$group->deleteMember($user->id, true);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_REMOVED_USER_SUCCESS', $user->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Allows user to leave a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function leaveGroup()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group id
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->id || !$id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Ensure that this is not the group owner.
		if ($group->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_GROUP_OWNER_NOT_ALLOWED_TO_LEAVE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Remove the user from the group.
		$group->leave($this->my->id);
		$group->notifyMembers('leave', array('userId' => $this->my->id));

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_LEAVE_GROUP_SUCCESS', $group->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Unpublishes a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unpublish()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$group = ES::group($id);

		if (!$id || !$group->id) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED');
		}

		// check user is allowed to unpublish an event
		if (!$group->canUnpublish($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_NOT_ALLOWED_TO_UNPUBLISH_GROUP');
		}

		// Try to unpublish the group now
		$state = $group->unpublish();

		if (!$state) {
			return $this->view->exception($group->getError());
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_UNPUBLISHED_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Unpublishes a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function publish()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$group = ES::group($id);

		if (!$id || !$group->id) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED');
		}

		// check user is allowed to unpublish an event
		if (!$group->canPublish($this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_NOT_ALLOWED_TO_PUBLISH_GROUP');
		}

		// Try to unpublish the group now
		$state = $group->publish();

		if (!$state) {
			return $this->view->exception($group->getError());
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_PUBLISHED_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * this method is called from the dialog to quickly add new filter based on the viewing hashtag.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function addFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$title = $this->input->get('title', '', 'default');
		$tag = $this->input->get('tag', '', 'default');
		$groupId = $this->input->get('id', '', 'int');

		$filter = ES::table('StreamFilter');

		$filter->title = $title;
		$filter->uid = $groupId;
		$filter->utype = SOCIAL_TYPE_GROUP;
		$filter->user_id = $this->my->id;
		$filter->store();

		// add hashtag into filter
		$filterItem = ES::table('StreamFilterItem');

		$filterItem->filter_id = $filter->id;
		$filterItem->type = 'hashtag';
		$filterItem->content = $tag;
		$filterItem->store();

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_SAVED', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $filter, $groupId);
	}

	/**
	 * Stores the groups's hashtag filter.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function saveFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$groupId = $this->input->get('uid', 0, 'int');
		$post = $this->input->post->getArray();

		$filter = ES::table('StreamFilter');

		if (!trim($post['title'])) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUP_STREAM_FILTER_WARNING_TITLE_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__, $filter);
		}

		if (!trim($post['hashtag'])) {
			$this->view->setError('COM_EASYSOCIAL_GROUP_STREAM_FILTER_WARNING_HASHTAG_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__, $filter);
		}

		if ($id) {
			$filter->load($id);
		}

		$filter->title = $post['title'];
		$filter->uid = $groupId;
		$filter->utype = SOCIAL_TYPE_GROUP;
		$filter->user_id = $this->my->id;
		$filter->store();

		// now we save the filter type and content.
		if ($post['hashtag']) {
			$hashtag = trim($post['hashtag']);
			$hashtag = str_replace( '#', '', $hashtag);
			$hashtag = str_replace( ' ', '', $hashtag);


			$filterItem = ES::table( 'StreamFilterItem' );
			$filterItem->load( array( 'filter_id' => $filter->id, 'type' => 'hashtag') );

			$filterItem->filter_id = $filter->id;
			$filterItem->type = 'hashtag';
			$filterItem->content = $hashtag;

			$filterItem->store();
		} else {
			$filter->deleteItem( 'hashtag' );
		}

		$this->view->setMessage('COM_EASYSOCIAL_GROUP_STREAM_FILTER_SAVED', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $filter, $groupId);
	}

	/**
	 * Stores the groups's hashtag filter.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function deleteFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$groupId = $this->input->get('uid', 0, 'int');

		if (!$id) {
			$this->view->setMessage('Invalid filter id', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$filter = ES::table('StreamFilter');

		// make sure the user is the filter owner before we delete.
		$filter->load(array('id' => $id, 'uid' => $groupId, 'utype' => SOCIAL_TYPE_GROUP));

		if (!$filter->id) {
			$this->view->setMessage('Filter not found', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$filter->deleteItem();
		$filter->delete();

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_DELETED', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $groupId);
	}

	/**
	 * Retrieves the group's stream items.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getStream()
	{
		ES::checkToken();

		// Load up the group
		$id = $this->input->get('groupId', 0, 'int');
		$group = ES::group($id);

		// Get the filter type
		$type = $this->input->get('filter', '', 'word');

		// Check if the group can be seen by this user
		if (!$group->canViewItem($this->my->id)) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$stream = ES::stream();

		// lets get the sticky posts 1st
		// Scheduled post shouldn't be allowed to be sticky. No need to search for this.
		if ($type != 'scheduled') {
			$stickies = $stream->getStickies(array('clusterId' => $group->id, 'clusterType' => SOCIAL_TYPE_GROUP, 'limit' => 0), array('perspective' => 'groups'));

			if ($stickies) {
				$stream->stickies = $stickies;
			}
		}

		// Determines if the user should see the story form
		if ($this->my->canPostClusterStory(SOCIAL_TYPE_GROUP, $group->id)) {

			$story = ES::story(SOCIAL_TYPE_GROUP);
			$story->setCluster($group->id, SOCIAL_TYPE_GROUP);
			$story->showPrivacy(false);

			$story->setFilter($type);

			$stream->story = $story;
		}

		// lets get stream items for this group
		$options = array('clusterId' => $group->id, 'clusterType' => SOCIAL_TYPE_GROUP, 'nosticky' => true);

		// Set the stream display options for this group
		$displayOptions = array('perspective' => 'groups');

		$filterId = $this->input->get('id', 0, 'int');

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

		// Retrieve items related to the particular stream filter only.
		if ($type == 'filters' && $filterId) {
			$streamFilter = ES::table('StreamFilter');
			$streamFilter->load($filterId);

			// Get a list of hashtags
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

		// Get the stream data now
		$stream->get($options, $displayOptions);

		$group->saveFilterPreferences($type, $filterId);

		return $this->view->call(__FUNCTION__ , $stream, $group, $streamFilter, $type);
	}

	/**
	 * Allows caller to filter groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter()
	{
		// Check for request forgeries
		ES::checkToken();

		$activeUserId = $this->input->get('userId', null, 'int');
		$categoryId = $this->input->get('categoryid', 0, 'int');

		// If there is a user id, means we are viewing profile's page listing
		// $browseView: is a listing view of all page.
		$browseView = $this->input->get('browseView', false, 'bool');

		$model = ES::model('Groups');
		$filter = $this->input->get('filter', '', 'string');

		// Sort
		$sort = $this->input->get('ordering', $this->config->get('groups.layout.listingsort'), 'cmd');
		$isSortingRequest = $this->input->get('sort', '', 'boolean');

		// Options
		$options = array('state' => SOCIAL_CLUSTER_PUBLISHED, 'types' => $this->my->isSiteAdmin() && $filter !== 'created' ? 'all' : 'user');

		// Default values
		$options['category'] = false;
		$groups = array();
		$featuredGroups	= array();
		$category = null;

		if (!$browseView && $activeUserId) {
			$options['uid'] = $activeUserId;
		}

		// Determine the pagination limit
		$limit = ES::getLimit('groups_limit');
		$options['limit'] = $limit;

		// Only exclude featured page when viewing all pages
		if ($browseView) {
			$options['featured'] = false;
		}

		if ($filter == 'mine') {
			$options['userid'] = $this->my->id;
			$options['types'] = 'participated';
			$options['featured'] = false;
		}

		// Filter by user own created groups
		if ($filter == 'created' && $options['types'] == 'user' && !isset($options['uid']) && $this->my->id) {
			$options['uid'] = $this->my->id;
			$options['featured'] = '';
		}

		if ($filter == 'invited') {
			$options['invited'] = $this->my->id;
			$options['types'] = 'all';
			$options['featured'] = '';
		}

		if ($filter == 'pending') {
			// groups that pending user's review.
			$options['uid'] = $this->my->id;
			$options['state'] = SOCIAL_CLUSTER_DRAFT;
			$options['types'] = 'user';
		}

		if ($filter == 'featured') {
			$options['featured'] = true;
		}

		if ($filter == 'participated' && $activeUserId) {
			$options['userid'] = $activeUserId;
			$options['types'] = 'participated';
		}

		$distance = $this->config->get('groups.nearby.radius');

		// Filter by nearby events
		if ($filter === 'nearby') {
			$distance = $this->input->get('distance', $distance, 'int');

			$options['location'] = true;
			$options['distance'] = $distance;
			$options['latitude'] = $this->input->getString('latitude');
			$options['longitude'] = $this->input->getString('longitude');
			$options['range'] = '<=';

			$session = JFactory::getSession();

			$userLocation = $session->get('groups.userlocation', array(), SOCIAL_SESSION_NAMESPACE);

			$hasLocation = !empty($userLocation) && !empty($userLocation['latitude']) && !empty($userLocation['longitude']);

			if (!$hasLocation) {
				$userLocation['latitude'] = $options['latitude'];
				$userLocation['longitude'] = $options['longitude'];

				$session->set('groups.userlocation', $userLocation, SOCIAL_SESSION_NAMESPACE);
			}
		}

		if ($categoryId) {
			$category = ES::table('GroupCategory');
			$category->load($categoryId);

			$options['category'] = $categoryId;

			// check if this category is a container or not
			if ($category->container) {
				// Get all child ids from this category
				$categoryModel = ES::model('ClusterCategory');
				$childs = $categoryModel->getChildCategories($category->id, array(), SOCIAL_TYPE_GROUP, array('state' => SOCIAL_STATE_PUBLISHED));

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

		if ($filter == 'unpublished') {

			// allow site admin to view all the unpublished post
			if (!$this->my->isSiteAdmin()) {
				$options['uid'] = $this->my->id;
			}

			$options['state'] = SOCIAL_CLUSTER_UNPUBLISHED;
		}

		$groups = $model->getGroups($options);
		$pagination	= $model->getPagination();

		// Define those query strings here
		$pagination->setVar('Itemid', ESR::getItemId('groups'));
		$pagination->setVar('view', 'groups');
		$pagination->setVar('filter', $filter);
		$pagination->setVar('ordering', $sort);

		if (!$browseView && $activeUserId) {
			$user = ES::User($activeUserId);
			$pagination->setVar('userid', $user->getAlias());
		}

		if ($filter === 'nearby') {
			$distance = $this->input->get('distance', $distance, 'int');

			$pagination->setVar('distance', $distance);
		}

		if ($categoryId) {
			$pagination->setVar('categoryid', $category->getAlias());
		}

		$showAllFeatured = false;

		// We also need to get the featured groups based on the current options
		if (($filter == 'all' || $filter == 'mine' || $categoryId) && !$isSortingRequest) {
			$options['featured'] = true;
			$featuredGroups = $model->getGroups($options);

			// Get total number of featured groups on the site
			$totalFeatured = $model->getTotalGroups(array('featured' => true));

			// Determine if we should show all link underneath featured group listings.
			if (!empty($featuredGroups) && $totalFeatured > count($featuredGroups)) {
				$showAllFeatured = true;
			}
		}

		return $this->view->call(__FUNCTION__, $filter, $sort, $groups, $pagination, $featuredGroups, $category, $isSortingRequest, $showAllFeatured, $activeUserId);
	}

	/**
	 * Allows caller to response to invitation
	 *
	 * @since	1.2
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
			ES::checkToken();
			ES::requireLogin();
		}

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$id || !$group) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if ($email && $userId && $group->key == $key) {
			$uid = $userId;
		} else {
			$uid = $this->my->id;
		}

		// Load the member
		$member	= ES::table('GroupMember');
		$member->load(array('cluster_id' => $group->id , 'uid' => $uid));

		if (!$member->id) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NOT_INVITED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the response action
		$action = $this->input->get('action', '', 'word');

		// If user rejected, just delete the invitation record.
		if ($action == 'reject') {
			$member->delete();
			$message = JText::sprintf('COM_EASYSOCIAL_GROUPS_REJECT_RESPONSE_SUCCESS', $group->getName());
		}

		if ($action == 'accept') {
			$member->state = SOCIAL_GROUPS_MEMBER_PUBLISHED;
			$member->store();

			// Create stream when user accepts the invitation
			$group->createStream($this->my->id, 'join');

			ES::apps()->load(SOCIAL_TYPE_GROUP);
			$dispatcher = ES::dispatcher();

			// Trigger: onJoinGroup
			$dispatcher->trigger('user', 'onJoinGroup', array($this->my->id, $group));

			// @points: groups.join
			// Add points when user joins a group
			$points = ES::points();
			$points->assign('groups.join', 'com_easysocial', $this->my->id);

			// Notify members when a new member is added
			$group->notifyMembers('join', array('userId' => $this->my->id));
			$message = JText::sprintf('COM_EASYSOCIAL_GROUPS_ACCEPT_RESPONSE_SUCCESS', $group->getName());

			// Transfer any existings events for invite only type
			$group->inviteToEvents($this->my->id, $inviterId);
		}

		$this->view->setMessage($message, SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group, $action);
	}

	/**
	 * Allows caller to send invites to friends
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function invite()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$id || !$group) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Determine if the user is a member of the group
		if (!$group->isMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NEED_TO_BE_MEMBER_TO_INVITE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Get the list of members that are invited
		$ids = $this->input->get('uid', '', 'default');

		if (!$ids) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_ENTER_FRIENDS_NAME_TO_INVITE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		// Flag if there is a user that has been invited before
		$invited = false;

		foreach ($ids as $id) {
			if (!$group->isInvited($id)) {
				$group->invite($id, $this->my->id);
			} else {
				$invited = true;
			}
		}

		$message = 'COM_EASYSOCIAL_GROUPS_FRIENDS_INVITED_SUCCESS';
		$class = SOCIAL_MSG_SUCCESS;

		if ($invited) {
			$message = 'COM_EASYSOCIAL_GROUPS_FRIENDS_HAS_BEEN_INVITED';
			$class = SOCIAL_MSG_INFO;
		}

		$this->view->setMessage($message, $class);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Deprecated
	 *
	 * @deprecated	3.0.0
	 */
	public function getAppContents()
	{
		// // Get the event
		// $id = $this->input->get('id', 0, 'int');
		// $group = ES::group($id);

		// if (!$group || !$group->id) {
		// 	return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED');
		// }

		// if (!$group->isPublished()) {
		// 	return $this->view->exception('COM_EASYSOCIAL_GROUPS_NO_ACCESS');
		// }

		// if (!$group->canViewItem()) {
		// 	return $this->view->exception('COM_EASYSOCIAL_GROUPS_NO_ACCESS');
		// }

		// // Get the application
		// $appId = $this->input->get('appId', 0, 'int');
		// $app = ES::table('App');
		// $state = $app->load($appId);

		// // If application id is not valid, throw an error.
		// if (!$appId || !$state) {
		// 	return $this->view->exception('COM_EASYSOCIAL_APPS_INVALID_APP_ID_PROVIDED');
		// }

		// return $this->view->call(__FUNCTION__, $group, $app);
	}

	/**
	 * Allows caller to set a group as a featured group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$id || !$group->id) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED');
		}

		if (!$group->canFeature()) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_NO_ACCESS');
		}

		// Set it as featured
		$group->removeFeatured();

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_GROUP_UNFEATURE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Allows caller to set a group as a featured group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function setFeatured()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$id || !$group->id) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED');
		}

		if (!$group->canFeature()) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_NO_ACCESS');
		}

		// Set it as featured
		$group->setFeatured();

		$this->view->setMessage('COM_EASYSOCIAL_GROUPS_GROUP_FEATURE_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Demote a group admin to normal member
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function demote()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->canPromoteMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user
		$userId	= $this->input->get('userId', 0, 'int');

		$user = ES::user($userId);

		$group->demoteUser($userId);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_DEMOTED_USER_SUCCESS', $user->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Promotes the provided user id as a group admin
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function promote()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->canPromoteMember()) {
			$this->view->setMessage('COM_EASYSOCIAL_GROUPS_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user
		$userId = $this->input->get('userId', 0, 'int');

		$user = ES::user($userId);

		// Make the target user as admin
		$group->promoteUser($userId);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_GROUPS_PROMOTED_USER_SUCCESS', $user->getName()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $group);
	}

	/**
	 * Service Hook for explorer
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function explorer()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->getint('uid');
		$group = ES::group($id);

		// Determine if the viewer can really view items
		if (!$group->canViewItem()) {
			return $this->view->call( __FUNCTION__ );
		}

		// Load up the explorer library
		$explorer = ES::explorer($group->id, SOCIAL_TYPE_GROUP);
		$hook = $this->input->get('hook', '', 'cmd');
		$result = $explorer->hook($hook);

		$exception = ES::response('Folder retrieval successful', SOCIAL_MSG_SUCCESS);

		return $this->view->call( __FUNCTION__ , $exception , $result );
	}

	/**
	 * Suggests a list of groups for a user.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function suggest()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the search query
		$search = $this->input->get('search', '', 'word');

		// Get exclusion list
		$exclusion = $this->input->get('exclusion', array(), 'array');

		// Determines if the user is an admin
		$options = array('unpublished' => false, 'exclusion' => $exclusion);

		if ($this->my->isSiteAdmin()) {
			$options['unpublished'] = true;
		}

		// Load up the groups model
		$model = ES::model('Groups');
		$groups = $model->search($search, $options);

		return $this->view->call(__FUNCTION__, $groups);
	}

	/**
	 * Retrieves the group's about information
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getInfo()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Ensure that the id provided is valid
		if (!$group || !$group->id) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_INVALID_GROUP_ID');
		}

		// Ensure that the user has access to view group's item
		if (!$group->canViewItem()) {
			return $this->view->exception('COM_EASYSOCIAL_GROUPS_NO_ACCESS');
		}

		// Get the current active step
		$activeStep = $this->input->get('step', 1, 'int');

		// Get the entire "about" for the group
		$model = ES::model('Groups');
		$steps = $model->getAbout($group, $activeStep);

		return $this->view->call(__FUNCTION__, $steps);
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

		$group = ES::group($id);

		// We should not allow anyone to send invites if it has been disabled.
		if (!$group->canInvite()) {
			die();
		}

		// Get the list of emails
		$emails = $this->input->get('emails', '', 'html');

		if (!$emails) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_PLEASE_ENTER_EMAILS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $group);
		}

		$emails = explode("\n", $emails);

		// Get the message
		$message = $this->input->get('message', '', 'default');

		$model = ES::model('Groups');

		foreach ($emails as $email) {

			// Ensure that the e-mail is valid
			$email = trim($email);
			$valid = JMailHelper::isEmailAddress($email);

			if (!$valid) {
				$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_INVALID_EMAIL', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__, $group);
			}

			$table = ES::table('FriendInvite');

			// Check if this email has been invited by this user before
			$table->load(array('email' => $email, 'user_id' => $this->my->id, 'utype' => SOCIAL_TYPE_GROUP, 'uid' => $group->id));

			// Skip this if the user has already been invited before.
			if ($table->id) {
				continue;
			}

			// Check if the e-mail is already registered on the site
			$exists = $model->isEmailExists($email, $group->id);

			if ($exists) {
				$this->view->setMessage(JText::sprintf('COM_ES_FRIENDS_INVITE_EMAIL_EXISTS_IN_GROUP', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__, $group);
			}

			$table->email = $email;
			$table->user_id = $this->my->id;
			$table->message = $message;
			$table->utype = SOCIAL_TYPE_GROUP;
			$table->uid = $group->id;

			$table->store();
		}

		$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_SENT_INVITATIONS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__, $group);
	}
}
