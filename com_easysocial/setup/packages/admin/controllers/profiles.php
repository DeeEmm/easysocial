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

class EasySocialControllerProfiles extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		// Map the alias methods here.
		$this->registerTask('unpublish', 'togglePublish');
		$this->registerTask('publish', 'togglePublish');

		$this->registerTask('form', 'form');

		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('savecopy', 'store');
	}

	/**
	 * Method to update the ordering of profile type
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		ES::checkToken();

		$cid = $this->input->get('cid', array(), 'array');
		$ordering = $this->input->get('order', array(), 'array');

		if (!$cid) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_ORDERING_NO_ITEMS');
		}

		$model = ES::model('Profiles');

		for($i = 0; $i < count($cid); $i++) {

			$id = $cid[$i];
			$order = $ordering[$i];

			$model->updateOrdering($id, $order);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_ORDERING_UPDATED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Responsible to delete a profile from the system.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function delete()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_DELETE_NO_ID');
		}

		// Let's go through each of the profile and delete it.
		foreach ($ids as $id) {
			$profile = ES::table('Profile');
			$profile->load($id);

			if ($profile->default) {
				$this->view->setMessage(JText::sprintf('COM_ES_ERROR_DELETE_DEFAULT_PROFILE', $profile->title), ES_ERROR);
				return $this->view->call('redirectToProfiles');
			}

			// If profile has members in it, do not try to delete this.
			if ($profile->hasMembers()) {
				$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_ERROR_DELETE_PROFILE_CONTAINS_USERS', $profile->title), ES_ERROR);
				return $this->view->call('redirectToProfiles');
			}

			$name = $profile->getTitle();

			$profile->delete();

			$actionlog = ES::actionlog();
			$actionlog->log('COM_ES_ACTION_LOG_PROFILE_DELETED', 'users', [
				'name' => $name
			]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_DELETED_SUCCESSFULLY');
		return $this->view->call('redirectToProfiles');
	}

	/**
	 * Saves a new or existing profile.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function store()
	{
		ES::checkToken();

		$pid = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');
		$post = $this->input->getArray('post');

		// Determines if this is a new profile type
		$isNew = !$pid ? true : false;

		// Get the current task
		$task = $this->getTask();
		$isCopy = $task == 'savecopy' ? true : false;

		// Load the profile type.
		$profile = ES::table('Profile');

		$isPrevDefaultProfile = false;

		if ($cid && $isCopy) {
			$profile->load($cid);

			//reset the pid
			$post['id'] = $cid;
		} else {
			$profile->load($pid);

			$isPrevDefaultProfile = $profile->default ? true : false;
		}

		$post['description'] = $this->input->get('description', '', 'raw');

		// Bind the posted data.
		$profile->bind($post, array(), true);

		// we need to check if we allow to un-default this profile type or not.
		if ($isPrevDefaultProfile && !$profile->default) {
			// we dont allow that to happen.
			// #3406
			$profile->default = true;
		}

		// if the profile is a default, we must enable this profiel type.
		if ($profile->default) {
			$profile->state = SOCIAL_STATE_PUBLISHED;
		}

		// Bind the user group's that are associated with the profile.
		$gid = $this->input->get('gid', '', 'default');

		// This is a minimum requirement to create a profile.
		if (!$gid) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_FORM_ERROR_SELECT_GROUP');
		}

		$workflowId = $this->input->get('workflow_id');

		// There workflow must be selected in order to proceed
		if (!$workflowId) {
			$this->view->setMessage('COM_ES_WORKFLOW_NOT_SELECTED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$profile->bindUserGroups($gid);
		$valid = $profile->validate();

		// If there's errors, just show the error.
		if ($valid !== true) {
			$this->view->setMessage($profile->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $profile);
		}

		$defaultPrivacyCustomField = isset($post['defaultPrivacyField']) ? $post['defaultPrivacyField'] : '';

		// update profile default privacy's custom fields.
		if ($defaultPrivacyCustomField) {
			$defaultCustomFields = explode(',', $defaultPrivacyCustomField);
			$profile->privacy_fields = ES::json()->encode($defaultCustomFields);
		}

		// Try to store the profile.
		if (!$profile->store(false, $isCopy)) {
			$this->view->setMessage($profile->getError(), ES_ERROR);
			return $this->view->store($profile);
		}

		// Bind the access
		$profile->bindAccess($post['access']);

		// If this profile is default, we need to ensure that the rest of the profiles are not default any longer.
		if ($profile->default) {
			$profile->makeDefault($isCopy);
		}

		// Store the avatar for this profile.
		$file = $this->input->files->get('avatar', '');

		// Try to upload the profile's avatar if required
		if (!empty($file['tmp_name'])) {
			$profile->uploadAvatar($file);
		}

		// Store the badge image for this profile.
		$file = $this->input->files->get('profileBadgeImage', '');

		if (!empty($file['tmp_name'])) {
			$profile->storeBadgeImage($file);
		}

		// Assign workflow only after the profile is saved
		$profile->assignWorkflow($workflowId);

		// Set the privacy for this profile type
		if (isset($post['privacy'])) {

			$privacyLib = ES::privacy();
			$resetMap = $privacyLib->getResetMap('all');

			$privacy = $post['privacy'];
			$ids = $post['privacyID'];
			$fields = $post['privacyField'];

			$requireReset = isset($post['privacyReset']) ? true : false;

			$data = array();

			if (count($privacy)) {
				foreach ($privacy as $group => $items) {
					foreach ($items as $rule => $val) {
						$id = $ids[$group][$rule];
						$id = explode('_', $id);

						$field = $fields[$group][$rule];

						$obj = new stdClass();
						$obj->id = $id[0];
						$obj->mapid = $id[1];
						$obj->value = $val;
						$obj->reset = false;
						$obj->params = '';

						if ($field) {
							$arrField = explode(',', $field);
							$obj->params = ES::json()->encode($arrField);
						}

						//check if require to reset or not.
						$gr = strtolower($group . '.' . $rule);

						if ($gr != 'field.joomla_username' && $gr != 'field.joomla_email' && $gr != 'field.joomla_timezone' && $gr != 'field.joomla_fullname') {
							$gr = str_replace('_', '.', $gr);
						}

						if ($requireReset && in_array($gr,  $resetMap)) {
							$obj->reset = true;
						}

						$data[] = $obj;
					}
				}
			}

			$privacyModel = ES::model('Privacy');
			$privacyModel->updatePrivacy($profile->id, $data, SOCIAL_PRIVACY_TYPE_PROFILES);

			// update search indexing for existing user
			if ($requireReset) {
				$userModel = ES::model('Users');

				// get robots from param.
				$robots = (isset($post['params']) && isset($post['params']['robots'])) ? $post['params']['robots'] : SOCIAL_PROFILE_ROBOTS_INHERIT;
				$userModel->updateUserRobots($profile->id, $robots);
			}
		}

		// default apps assignment.
		if (!$isNew && isset($post['apps']) && $post['apps'] && is_array($post['apps'])) {
			$profile->assignUsersApps($post['apps']);
		}

		// If this is a save as copy
		if ($isCopy && $pid) {
			$profile->copyAvatar($pid);
		}

		$message = 'COM_EASYSOCIAL_PROFILES_PROFILE_CREATED_SUCCESSFULLY';
		$actionString = 'COM_ES_ACTION_LOG_PROFILE_CREATED';

		if (!$isNew) {
			$message = 'COM_EASYSOCIAL_PROFILES_PROFILE_UPDATED_SUCCESSFULLY';
			$actionString = 'COM_ES_ACTION_LOG_PROFILE_UPDATED';
		}

		if ($isCopy) {
			$message = 'COM_EASYSOCIAL_PROFILES_PROFILE_COPIED_SUCCESSFULLY';
			$actionString = 'COM_ES_ACTION_LOG_PROFILE_COPIED';
		}

		$actionlog = ES::actionlog();
		$actionlog->log($actionString, 'profiles', [
				'profileName' => $profile->getTitle(),
				'profileLink' => 'index.php?option=com_easysocial&view=profiles&layout=form&id=' . $profile->id
			]);

		// Set message.
		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $profile, $task);
	}

	/**
	 * Method to process files that is being sent to store default avatars.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function uploadDefaultAvatars()
	{
		ES::checkToken();

		$defaultAvatar = ES::table('DefaultAvatar');
		$defaultAvatar->uid = $this->input->get('uid', 0, 'int');
		$defaultAvatar->type = $this->input->get('type', SOCIAL_TYPE_PROFILES, 'cmd');
		$defaultAvatar->state = SOCIAL_STATE_PUBLISHED;

		$file = $this->input->files->getArray();
		$state = $defaultAvatar->upload($file);

		// There's an error when saving the images.
		if (!$state) {
			$this->view->setMessage($defaultAvatar->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $defaultAvatar);
		}

		// Let's try to save the defaultAvatar now.
		$state = $defaultAvatar->store();

		// If we hit any errors, we should notify the user.
		if (!$state) {
			$this->view->setMessage($defaultAvatar->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $defaultAvatar);
		}

		return $this->view->call(__FUNCTION__, $defaultAvatar);
	}

	/**
	 * Toggles a profile as default.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function toggleDefault()
	{
		ES::checkToken();

		$cid = $this->input->get('cid', array(), 'array');

		if (!$cid) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_PROFILE_DOES_NOT_EXIST');
		}

		$cid = $cid[0];

		$profile = ES::table('Profile');
		$profile->load($cid);

		if (!$profile->id) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_PROFILE_DOES_NOT_EXIST');
		}

		$state = $profile->makeDefault();

		if (!$state) {
			$this->view->setMessage($profile->getError(), ES_ERROR);
			return $this->view->call('redirectToProfiles');
		}

		$actionlog = ES::actionlog();
		$actionlog->log('COM_ES_ACTION_LOG_PROFILE_SET_DEFAULT', 'users', [
				'name' => $profile->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=profiles&layout=form&id=' . $profile->id
			]);

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_PROFILE_PROFILE_IS_NOW_DEFAULT_PROFILE');
		return $this->view->call('redirectToProfiles');
	}

	/**
	 * Publishes a profile.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$task = $this->getTask();
		$cid = $this->input->get('cid', array(), 'array');
		$actionString = $task == 'publish' ? 'COM_ES_ACTION_LOG_PROFILE_PUBLISHED' : 'COM_ES_ACTION_LOG_PROFILE_UNPUBLISHED';

		foreach ($cid as $id) {
			$profile = ES::table('Profile');
			$profile->load((int) $id);

			if (!$profile->id) {
				return $this->view->exception('COM_EASYSOCIAL_PROFILES_PROFILE_DOES_NOT_EXIST');
			}

			// Do not allow admin to unpublish a default profile
			if ($profile->default) {
				$this->view->setMessage('COM_EASYSOCIAL_PROFILES_UNABLE_TO_UNPUBLISH_DEFAULT_PROFILE', ES_ERROR);
				return $this->view->call('redirectToProfiles');
			}

			if (!$profile->$task()) {
				$this->view->setMessage($profile->getError(), ES_ERROR);
				return $this->view->call('redirectToProfiles');
			}

			$actionlog = ES::actionlog();
			$actionlog->log($actionString, 'users', [
					'name' => $profile->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=profiles&layout=form&id=' . $profile->id
				]);
		}

		$message = $task == 'publish' ? 'COM_EASYSOCIAL_PROFILES_PROFILE_PUBLISHED_SUCCESSFULLY' : 'COM_EASYSOCIAL_PROFILES_PROFILE_UNPUBLISHED_SUCCESSFULLY';

		$this->view->setMessage($message);
		return $this->view->call('redirectToProfiles');
	}

	/**
	 * Allows a profile to be ordered down
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function moveDown()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');
		$ids = ES::makeArray($ids);

		if (!$ids) {
			return $this->view->exception('Invalid profile id provided');
		}

		foreach ($ids as $id) {
			$profile = ES::table('Profile');
			$profile->load((int) $id);
			$profile->move(1);
		}

		$this->view->setMessage('Profile re-ordered successfully.');
		return $this->view->call('redirectToProfiles');
	}

	/**
	 * Allows a profile to be ordered up
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function moveUp()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('Invalid profile id provided');
		}

		foreach ($ids as $id) {
			$profile = ES::table('Profile');
			$profile->load((int) $id);
			$profile->move(-1);
		}

		$this->view->setMessage('Profile re-ordered successfully.');
		return $this->view->call('redirectToProfiles');
	}

	/**
	 * Updates the ordering of the profiles
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateOrdering()
	{
		// Check for request forgeries.
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$order = $this->input->get('order', array(), 'int');

		$model = ES::model('Profiles');
		$model->saveOrder($ids, $order);

		return $this->view->call('redirectToProfiles');
	}

	public function getFieldValues()
	{
		// Check for request forgeries.
		ES::checkToken();

		$fieldid = $this->input->get('fieldid', 0, 'int');
		$values = '';

		if ($fieldid !== 0) {
			$fields = ES::table('field');
			$fields->load($fieldid);

			$values = json_decode($fields->params);

			if (!is_object($values)) {
				$values = new stdClass();
			}

			$values->core_title = $fields->title;
			$values->core_display_title = (boolean) $fields->display_title;
			$values->core_description = $fields->description;
			$values->core_required = (boolean) $fields->required;
			$values->core_default = $fields->default;
		}

		return $this->view->call(__FUNCTION__, $values);
	}

	/**
	 * Save the custom fields.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createBlankProfile()
	{
		// Check for request forgeries.
		ES::checkToken();

		// Create the new profile
		$newProfile = ES::table('Profile');
		$newProfile->title = 'temp';
		$newProfile->createBlank();
		$id = $newProfile->id;

		return $this->view->call(__FUNCTION__, $id);
	}

	/**
	 * Deletes the profile avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteProfileAvatar()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('profile');
		$state = $table->load($id);

		if ($state) {
			$state = $table->removeAvatar();

			if (!$state) {
				$this->view->setMessage('PROFILES: Unable to delete the avatar', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Restore the profile badge image
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function restoreProfileBadgeImage()
	{
		$id = $this->input->get('id', 0, 'int');

		$profile = ES::table('profile');
		$profile->load($id);

		if ($profile->id) {
			$profile->restoreBadgeImage();
		}

		return $this->view->call(__FUNCTION__);
	}
}
