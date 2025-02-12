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

class EasySocialControllerProfile extends EasySocialController
{
	/**
	 * Determines if the view should be visible on lockdown mode
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isLockDown($task)
	{
		return true;
	}

	/**
	 * Alias to @save
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveClose()
	{
		return $this->save();
	}

	/**
	 * Save user's information.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		// Clear previous session
		$session = JFactory::getSession();
		$session->clear('easysocial.profile.errors', SOCIAL_SESSION_NAMESPACE);

		// Get post data.
		$post = $this->input->getArray('POST');

		// There is instances where we can allow user to edit another person's profile through an app
		$userId = $this->input->get('userId', $this->my->id, 'int');
		$canEdit = $userId == $this->my->id;
		$user = ES::user($userId);

		// Get list of steps for this user's profile type.
		$profile = $user->getProfile();

		// Get workflow that are associated with this profile type
		$workflow = $profile->getWorkflow();

		$saveLogic = $this->config->get('users.profile.editLogic', 'default');
		$isLastStep = false;
		$stepId = false;
		$nextStepId = false;

		if ($saveLogic == 'steps') {

			// current working step Id.
			$stepId = $this->input->get('stepId', 0, 'int');

			// the next current step id, if supplied
			$nextStepId = $this->input->get('nextStepId', 0, 'int');

			// Get the steps model
			$stepsModel = ES::model('Steps');
			$allSteps = $stepsModel->getSteps($workflow->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_EDIT);

			$activeStep = ES::table('FieldStep');
			$activeStep->load($stepId);

			if (!$nextStepId && count($allSteps) == $activeStep->sequence) {
				$isLastStep = true;
			}

			// lets get the next step
			if (!$nextStepId && !$isLastStep) {
				// we only want the first step
				foreach ($allSteps as $step) {

					if ($step->sequence > $activeStep->sequence) {
						$nextStepId = $step->id;
						break;
					}
				}
			}
		}

		// Honeypot checks
		if ($this->config->get('honeypot.profile')) {
			$honeypot = ES::honeypot();
			$trapped = $honeypot->isTrapped('profile');

			if ($trapped) {
				$this->view->setMessage(JText::_('Profile updated'));

				return $this->view->call(__FUNCTION__, $user);
			}
		}

		$arguments = array(&$user, &$canEdit);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onBeforeProfileSave', $arguments);

		if (!$canEdit) {
			throw ES::exception('Not allowed', 500);
		}

		// Only fetch relevant fields for this user.
		$options = array('workflow_id' => $user->getProfile()->getWorkflow()->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT, 'group' => SOCIAL_FIELDS_GROUP_USER);

		if ($saveLogic == 'steps' && $stepId) {
			$options['step_id'] = $stepId;
		}

		// Get all published fields apps that are available in the current form to perform validations
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		// Initialize default registry
		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array(ES::token(), 'option' , 'task' , 'controller', 'Itemid', 'profileId', 'workflowId');

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
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$user);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$user);

		// Ensure that there is no errors.
		// @trigger onEditValidate
		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'validate'));

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PROFILE_SAVE_ERRORS'), ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'profile', 'POST');
			$this->input->set('layout', 'edit', 'POST');
			if ($saveLogic == 'steps') {
				$this->input->set('activeStep', $stepId, 'POST');
			}


			if ($this->my->id && $user->id && $user->id != $this->my->id) {
				// There is instances where we can allow user to edit another person's profile through an app
				$this->input->set('id', $user->id, 'POST');
			}

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('edit', $errors, $data);
		}

		// @trigger onEditBeforeSave
		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'beforeSave'));

		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PROFILE_ERRORS_IN_FORM'), ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'profile', 'POST');
			$this->input->set('layout', 'edit', 'POST');
			if ($saveLogic == 'steps') {
				$this->input->set('activeStep', $stepId, 'POST');
			}

			if ($this->my->id && $user->id && $user->id != $this->my->id) {
				// There is instances where we can allow user to edit another person's profile through an app
				$this->input->set('id', $user->id, 'POST');
			}

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('edit', $errors);
		}

		// Bind the my object with appropriate data.
		$user->bind($data);

		// Save the user object.
		$user->save();

		// Reconstruct args
		$args = array(&$data, &$user);

		// @trigger onEditAfterSave
		$fieldsLib->trigger('onEditAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Bind custom fields for the user.
		$user->bindCustomFields($data);

		// Reconstruct args
		$args = array(&$data, &$user);

		// @trigger onEditAfterSaveFields
		$fieldsLib->trigger('onEditAfterSaveFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		$addPointsAndStream = true;
		$task = $this->input->getString('task');

		if ($saveLogic == 'steps' && !($isLastStep || $task == 'saveclose')) {
			$addPointsAndStream = false;
		}

		if ($addPointsAndStream) {
			// Add stream item to notify the world that this user updated their profile.
			$user->addStream('updateProfile');
			$points = ES::points();

			// We only assign points to the user once for the profile update
			if (!$points->hasAchievedBefore('profile.update', 'com_easysocial', $user->id)) {
				$points->assign('profile.update', 'com_easysocial', $user->id);
			}
		}

		// Update indexer
		$user->syncIndex();

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);

		$dispatcher = ES::dispatcher();
		$args = array(&$user, &$fields, &$data);

		// @trigger: onUserProfileUpdate
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserProfileUpdate', $args);

		// @trigger onProfileCompleteCheck
		// This should return an array of booleans to state which field is filled in.
		// We count the returned result since it will be an array of trues that marks the field that have data for profile completeness checking.
		// We do this after all the data has been saved, and we reget the fields from the model again.
		// We also need to reset the cached field data
		SocialTableField::$_fielddata = array();
		$options = array('workflow_id' => $user->getProfile()->getWorkflow()->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT, 'group' => SOCIAL_FIELDS_GROUP_USER);
		$fields = $fieldsModel->getCustomFields($options);

		$args = array(&$user);
		$completedFields = $fieldsLib->trigger('onProfileCompleteCheck', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		$table = ES::table('Users');
		$table->load(array('user_id' => $user->id));
		$table->completed_fields = count($completedFields);
		$table->store();

		// Update social goals progress
		$user->updateGoals('completeprofile');

		$msg = JText::_('COM_EASYSOCIAL_PROFILE_ACCOUNT_UPDATED_SUCCESSFULLY');

		if ($saveLogic == 'steps' && !($isLastStep || $task == 'saveclose')) {
			$msg = JText::_('COM_ES_PROFILE_STEP_UPDATED_SUCCESSFULLY');
		}

		$this->view->setMessage($msg, SOCIAL_MSG_SUCCESS);

		if ($saveLogic == 'steps') {
			$this->input->set('nextStepId', $nextStepId, 'POST');
			$this->input->set('isLastStep', $isLastStep, 'POST');
		}

		// remove user from cache so that we can reload the user data.
		$userId = $user->id;
		$user->removeFromCache();

		$user = ES::user($userId);

		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * Complete the process of profile switching.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function completeSwitch()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get post data.
		$post = $this->input->getArray('POST');

		if (!$this->my->canSwitchProfile()) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_NOT_ALLOWED'), ES_ERROR);

			$redirect = ESR::profile(array('layout' => 'edit'), false);
			return ES::redirect($redirect);
		}

		$newProfileId = $this->input->get('newProfileId', 0, 'int');

		$profile = ES::table('Profile');
		$profile->load($newProfileId);

		if (!$profile->id) {
			$this->info->set(false, JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_INVALID_PROFILE_TYPE'), ES_ERROR);

			$redirect = ESR::profile(array('layout' => 'edit'), false);
			return ES::redirect($redirect);
		}

		// previous user workflow id
		$oldWorkflowId = $this->my->getProfile()->getWorkflow()->id;

		// New workflow id
		$newWorkflowId = $profile->getWorkflow()->id;

		// Only fetch relevant fields for this user.
		$options = array('workflow_id' => $newWorkflowId, 'data' => true, 'dataId' => $this->my->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT, 'group' => SOCIAL_FIELDS_GROUP_USER);

		// Get all published fields apps that are available in the current form to perform validations
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		foreach ($fields as $field) {
			if ($field->element == 'relationship') {

				$getManually = true;

				// test if form post has this field or not.
				if (isset($post[SOCIAL_FIELDS_PREFIX.$field->id]) && $post[SOCIAL_FIELDS_PREFIX.$field->id]) {
					if (isset($post[SOCIAL_FIELDS_PREFIX.$field->id]['type'])) {
						$getManually = false;
					}
				}

				if ($getManually) {
					// this mean user did not change the relationship during the profile switch. we need to manually get the value from exiting profile.
					$value = $this->my->getFieldData($field->unique_key);
					$post[SOCIAL_FIELDS_PREFIX.$field->id] = $value;
				}
			}
		}

		// Initialize default registry
		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array(ES::token(), 'option' , 'task' , 'controller', 'Itemid', 'profileId', 'workflowId');

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
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$this->my);

		// Format conditional data
		$fieldsLib->trigger('onConditionalFormat', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler));

		// Rebuild the arguments since the data is already changed previously.
		$args = array(&$data, 'conditionalRequired' => $data['conditionalRequired'], &$this->my);

		// Ensure that there is no errors.
		// @trigger onEditValidate
		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'validate'));

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_SAVE_ERRORS'), ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'profile');
			$this->input->set('layout', 'switchProfileEdit');
			$this->input->set('profile_id', $profile->id);

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('switchProfileEdit', $errors, $data);
		}

		// @trigger onEditBeforeSave
		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'beforeSave'));

		if (is_array($errors) && count($errors) > 0) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_ERRORS_IN_FORM'), ES_ERROR);

			// We need to set the proper vars here so that the es-wrapper contains appropriate class
			$this->input->set('view', 'profile');
			$this->input->set('layout', 'switchProfileEdit');
			$this->input->set('profile_id', $profile->id);

			// We need to set the data into the post again because onEditValidate might have changed the data structure
			$this->input->setVars($data);

			return $this->view->call('switchProfileEdit', $errors);
		}

		// reset the joomla groups
		// If selected user is super user, we should skip it. #4600
		if ($this->config->get('users.profile.switchgroup') && !$this->my->isSiteAdmin()) {
			$gid = $profile->getJoomlaGroups();

			if ($gid) {
				$data['gid'] = $gid;
			}
		}

		// Bind the my object with appropriate data.
		$this->my->bind($data);

		// Save the user object.
		$this->my->save();

		// Reconstruct args
		$args = array(&$data, &$this->my);

		// @trigger onEditAfterSave
		$fieldsLib->trigger('onEditAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Bind custom fields for the user.
		$this->my->bindCustomFields($data, $profile->id);

		// Reconstruct args
		$args = array(&$data, &$this->my);

		// @trigger onEditAfterSaveFields
		$fieldsLib->trigger('onEditAfterSaveFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// TODO: update the profile type mapping for this user.
		$oldProfileId = $this->my->profile_id;
		$newProfileId = $profile->id;

		$profileModel = ES::model('Profiles');
		$state = $profileModel->switchProfile($this->my->id, $oldProfileId, $newProfileId);

		// TODO: now we need to delete the old data for this user.
		if ($state) {

			// delete those old data from this user
			$this->my->deleteFieldData($oldWorkflowId);

			// update this user object the new profile id.
			$this->my->profile_id = $newProfileId;
		}

		// Update indexer
		$this->my->syncIndex();

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PROFILE_SWITCH_ACCOUNT_UPDATED_SUCCESSFULLY', $profile->getTitle()), SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Save user's privacy.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function savePrivacy()
	{
		ES::requireLogin();
		ES::checkToken();

		$privacyLib = ES::privacy();
		$resetMap = $privacyLib->getResetMap();

		$post = $this->input->getArray('post');
		$privacy = $post['privacy'];
		$ids = $post['privacyID'];
		$curValues = $post['privacyOld'];
		$customIds = $post['privacyCustom'];
		$requireReset = isset($post['privacyReset']) ? true : false;

		$data = array();

		if (count($privacy)) {
			foreach ($privacy as $group => $items) {
				foreach ($items as $rule => $val) {

					$id = $ids[$group][$rule];
					$custom = $customIds[$group][$rule];
					$curVal = $curValues[$group][$rule];

					$customUsers = array();

					if (!empty($custom)) {
						$tmp = explode(',', $custom);
						foreach ($tmp as $tid) {
							if (!empty($tid)) {
								$customUsers[] = $tid;
							}
						}
					}

					$id = explode('_', $id);

					$obj = new stdClass();

					$obj->id = $id[0];
					$obj->mapid = $id[1];
					$obj->value = $val;
					$obj->custom = $customUsers;
					$obj->reset = false;

					//check if require to reset or not.
					$gr = strtolower($group . '.' . $rule);

					if ($requireReset && in_array($gr,  $resetMap)) {
						$obj->reset = true;
					}

					$data[] = $obj;
				}
			}
		}

		// Set the privacy for this user
		if (count($data) > 0) {
			$privacyModel = ES::model('Privacy');
			$state = $privacyModel->updatePrivacy($this->my->id , $data, SOCIAL_PRIVACY_TYPE_USER);

			if ($state !== true) {
				$this->view->setMessage($state, ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		// Assign points when user updates their privacy
		$points = ES::points();
		$points->assign('privacy.update', 'com_easysocial', $this->my->id);

		// Index user access in finder
		$this->my->syncIndex();

		// update the user profile robots indexing
		$searchIndexing = isset($post['searchIndexing']) && $post['searchIndexing'] ? $post['searchIndexing'] : SOCIAL_PROFILE_ROBOTS_INHERIT;
		$this->my->addUserRobots($searchIndexing);

		$this->view->setMessage('COM_EASYSOCIAL_PRIVACY_UPDATED_SUCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows to remove profile avatar
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function removeAvatar()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the profile user Id
		$id = $this->input->get('id', 0, 'int');
		$user = ES::user($id);

		if (!$user->id == $this->my->id && !$user->id == $this->my->isSiteAdmin()) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_USER');
		}

		// Remove the avatar of the current view profile
		$user->removeAvatar();

		$this->view->setMessage('COM_EASYSOCIAL_PROFILE_AVATAR_REMOVED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * Save user's notification.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveNotification()
	{
		ES::requireLogin();
		ES::checkToken();

		$post = $this->input->post->getArray();

		$systemNotifications = isset($post['system']) && $post['system'] ? $post['system'] : array();
		$emailNotifications = isset($post['email']) && $post['email'] ? $post['email'] : array();

		if ($systemNotifications || $emailNotifications) {
			$model = ES::model('Notifications');
			$state = $model->saveNotifications($systemNotifications , $emailNotifications , $this->my);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILE_NOTIFICATION_UPDATED_SUCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Renders a user timeline
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStream()
	{
		ES::checkToken();

		$this->input->set('view', 'profile');

		// Get the type of the stream to load.
		$type = $this->input->get('type', '', 'word');

		// Get the current user that is being viewed.
		$id = $this->input->get('id', 0, 'int');
		$user = ES::user($id);

		// Retrieve user's stream
		$stream = ES::stream();
		$stickies = $stream->getStickies(array('userId' => $user->id, 'limit' => 0));

		if ($stickies) {
			$stream->stickies = $stickies;
		}

		$appType = '';

		$options = array('userId' => $user->id, 'nosticky' => true);
		$stream->get($options);

		// Retrieve user's status
		$story = ES::story(SOCIAL_TYPE_USER);
		$story->target = $user->id;
		$stream->story = $story;

		return $this->view->call(__FUNCTION__, $stream, $story);
	}

	/**
	 * Retrieves the dashboard contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAppContents()
	{
		// Check for request forgeries.
		ES::checkToken();

		$appId = $this->input->get('appId', 0, 'int');
		$userId = $this->input->get('id', 0, 'int');

		$app = ES::table('App');
		$state = $app->load($appId);

		if (!$appId || !$state) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_APP_ID_INVALID', ES_ERROR);
			return $this->view->call(__FUNCTION__ , $app , $userId);
		}

		return $this->view->call(__FUNCTION__ , $app , $userId);
	}

	/**
	 * Allows user to delete their own account
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		// Check for request forgeries
		ES::checkToken();

		// Determine if the user is really allowed
		if (!$this->my->deleteable()) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_DELETE');
		}

		// Determine if we should immediately delete the user
		if ($this->config->get('users.deleteLogic') == 'delete') {
			$mailTemplate = 'deleted.removed';

			// Delete the user.
			$this->my->delete();
		}

		if ($this->config->get('users.deleteLogic') == 'unpublish') {
			$mailTemplate = 'deleted.blocked';

			// Block the user
			$this->my->block();
		}

		// Send notification to admin
		// Push arguments to template variables so users can use these arguments
		$date = ES::date()->format(JText::_('COM_EASYSOCIAL_DATE_DMY'));

		$params = array(
						'name' => $this->my->getName(),
						'avatar' => $this->my->getAvatar(SOCIAL_AVATAR_MEDIUM),
						'profileLink' => JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=form&id=' . $this->my->id,
						'date' => $date,
						'totalFriends' => $this->my->getTotalFriends(),
						'totalFollowers' => $this->my->getTotalFollowers()
				);


		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_USER_DELETED_ACCOUNT_TITLE', $this->my->getName());

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		if ($admins) {
			foreach ($admins as $admin) {

				$params['adminName'] = $admin->name;

				$mailer = ES::mailer();
				$template = $mailer->getTemplate();

				$template->setRecipient($admin->name, $admin->email);
				$template->setTitle($title);
				$template->setTemplate('site/profile/' . $mailTemplate, $params);
				$template->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

				// Try to send out email to the admin now.
				$state = $mailer->create($template);
			}
		}

		// Log the user out from the system
		$this->my->logout();

		return $this->view->call(__FUNCTION__);
	}


	/**
	 * Allows admin to delete user account
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function deleteUser()
	{
		// Check for request forgeries
		ES::checkToken();

		// Determine current logged in user is an admin or not.
		if (!$this->my->isSiteAdmin()) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_DELETE_USER');
		}

		$userId = $this->input->get('id', 0, 'int');
		$user = ES::user($userId);

		if (!$this->my->canDeleteUser($user)) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_DELETE_USER');
		}

		// Log the user out from the system
		$app = JFactory::getApplication();
		$app->logout($user->id, array('clientid' => 0));

		// Delete the user.
		$user->delete();

		// Send notification to admin
		// Push arguments to template variables so users can use these arguments
		$params = array(
						'name'				=> $user->getName(),
						'avatar'			=> $user->getAvatar(SOCIAL_AVATAR_MEDIUM),
						'profileLink'		=> JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id,
						'date'				=> ES::date()->format(JText::_('COM_EASYSOCIAL_DATE_DMY')),
						'totalFriends'		=> $user->getTotalFriends(),
						'totalFollowers'	=> $user->getTotalFollowers()
				);


		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_ADMIN_USER_DELETED_TITLE', $user->getName());

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		if ($admins) {
			foreach ($admins as $admin) {

				$params['adminName'] = $admin->name;

				$mailer = ES::mailer();
				$template = $mailer->getTemplate();

				$template->setRecipient($admin->name, $admin->email);
				$template->setTitle($title);
				$template->setTemplate('site/profile/deleted.removed', $params);
				$template->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

				// Try to send out email to the admin now.
				$state = $mailer->create($template);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILE_ADMINTOOL_DELETE_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows site admin to unban a user
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function unbanUser()
	{
		ES::checkToken();

		// Determine current logged in user is an admin or not.
		if (!$this->my->isSiteAdmin()) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_BAN_USER');
		}

		$userId = $this->input->get('id', 0, 'int');
		$user = ES::user($userId);

		if (!$this->my->canBanUser($user)) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_BAN_USER');
		}

		// Unblock the user
		$user->unblock();

		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * Allows admin to ban / block user account
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function banUser()
	{
		ES::checkToken();

		if (!$this->my->isSiteAdmin()) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_BAN_USER');
		}

		$userId = $this->input->get('id', 0, 'int');
		$period = $this->input->get('period', 0, 'int');
		$reason = $this->input->get('reason', '', 'string');

		$user = ES::user($userId);

		if (!$this->my->canBanUser($user)) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_NOT_ALLOWED_TO_BAN_USER');
		}

		// Ban the user
		$user->ban($period, $reason);

		$message = JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_BAN_SUCCESSFULLY');

		if ($period) {
			$message = JText::sprintf('COM_EASYSOCIAL_PROFILE_ADMINTOOL_BAN_SUCCESSFULLY_X_TIME', $period);
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $user);
	}

	/**
	 * Retrieve additional information for a specific user
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getInfo()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the user object
		$id = $this->input->get('id', 0, 'int');
		$user = ES::user($id);

		if (empty($user) || empty($user->id) || $user->isBlock()) {
			return $this->view->exception('COM_EASYSOCIAL_USERS_NO_SUCH_USER');
		}

		// Ensure that the viewer can see the person's profile
		if (!$this->my->canView($user)) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILE_PRIVACY_NOT_ALLOWED');
		}

		// Load admin's languge file
		ES::language()->loadAdmin();

		// Get the step index
		$index = $this->input->get('index', 0, 'int');

		// Get the user's profile
		$profile = $user->getProfile();
		$sequence = $profile->getSequenceFromIndex($index, SOCIAL_PROFILES_VIEW_DISPLAY);

		$step = ES::table('FieldStep');
		$state = $step->load(array('workflow_id' => $profile->getWorkflow()->id, 'type' => SOCIAL_TYPE_PROFILES, 'sequence' => $sequence, 'visible_display' => 1));

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_PROFILE_USER_NOT_EXIST', $user->getName()), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$fields = ES::model('Fields')->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_DISPLAY));
		$fieldsLib = ES::fields();

		if (!empty($fields)) {
			$args = array($user);

			$fieldsLib->trigger('onDisplay', SOCIAL_FIELDS_GROUP_USER, $fields, $args);
		}

		return $this->view->call(__FUNCTION__, $fields);
	}

	/**
	 * Submit request to download information
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function download()
	{
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the user is logged in
		if (!$this->my->id) {
			throw ES::exception(JText::_('You need to be logged in first'), 500);
		}

		$table = ES::table('download');
		$exists = $table->load(array('userid' => $this->my->id));

		if ($exists) {
			throw ES::exception(JText::_('You cannot request more than once'), 500);
		}

		$params = array();

		$table->userid = $this->my->id;
		$table->state = ES_DOWNLOAD_REQ_NEW;
		$table->params = json_encode($params);
		$table->created = ES::date()->toSql();
		$table->store();

		$redirect = ESR::profile(array('layout' => 'download'), false);

		$this->view->setMessage('COM_ES_GDPR_REQUEST_DATA_SUCCESS');
		return $this->view->setRedirection($redirect);
	}
}
