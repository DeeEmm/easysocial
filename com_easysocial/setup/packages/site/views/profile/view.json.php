<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewProfile extends EasySocialSiteView
{
	/**
	 * Displays the edit profile form for rest api
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function edit()
	{
		// Validate user and get the user object
		$userId = $this->validateAuth();

		// Get user object
		$user = ES::user($userId);

		// We need to know which profile this user belongs to
		$profile = $user->getProfile();

		// Get a list of steps
		$stepsModel = ES::model('Steps');
		$steps = $stepsModel->getSteps($profile->getWorkflow()->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_EDIT);

		// Get al ist of fields
		$fieldsModel = ES::model('Fields');

		// Get custom fields library.
		$fields = ES::fields();

		// Set the callback for the triggered custom fields
		$callback = array($fields->getHandler(), 'getOutput');

		$errors = null;

		$customFields = $fieldsModel->getCustomFields(array('workflow_id' => $user->getWorkflow()->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT));

		// Trigger onEdit for custom fields.
		if (!empty($customFields)) {
			$post = $this->input->post->getArray();
			$args = array(&$post, &$user, $errors);
			$fields->trigger('onEdit', SOCIAL_FIELDS_GROUP_USER, $customFields, $args, $callback);
		}

		// Format the field to only display important information to the caller
		$output = $this->formatOutput($user, $customFields);

		$this->set('user', $output);
		$this->set('code', 200);

		parent::display();
	}

	/**
	 * Format the output
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function formatOutput($userData, $fields)
	{
		ES::language()->loadAdmin();

		// We will give formatted user informations
		$data = array();

		$user = new stdClass();
		$user->id = $userData->id;
		$user->email = $userData->email;
		$user->username = $userData->username;

		// Group all the fields
		$user->fields = array();

		// Exclude these fields from the output.
		$specialField = array('header', 'joomla_fullname', 'joomla_password', 'joomla_email', 'avatar', 'cover');

		foreach ($fields as $key => $value) {

			if (in_array($value->element, $specialField)) {

				// Process fullname
				if ($value->element == 'joomla_fullname') {
					$user->first_name = $value->data['first'];
					$user->middle_name = $value->data['middle'];
					$user->last_name = $value->data['last'];
				}

				continue;
			}

			$field = new stdClass();

			$field->identifier = SOCIAL_CUSTOM_FIELD_PREFIX . '-' . $value->id;            
			$field->label = JText::_($value->title);
			$field->info = JText::_($value->description);
			$field->core = $value->core;
			$field->required = $value->required;

			// Need to find a way to get field input type
			// $field->type = $value->type;

			$field->value = $value->data;
			$field->raw = is_array($value->data) ? json_encode($value->data) : $value->data;            
			$field->html = $value->output;

			// Special identifier for username
			if ($value->element == 'joomla_username') {

				// Check if system allow to change username
				$params = json_decode($value->params);

				if (!$params->allow_edit_change) {
					continue;
				}

				$field->identifier = 'username';
				$field->value = $user->username;
				$field->raw = $user->username;
			}

			$user->fields[] = $field;
		}

		return $user;
	}

	/**
	 * Format saving data to follow the correct format
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function formatSaveData(&$data)
	{
		// Initialize default registry
		$registry = ES::registry();

		// Get disallowed keys so we wont get wrong values.
		$disallowed = array(ES::token(), 'option' , 'task' , 'controller');

		$saveData = json_decode($data['data']);

		if (!$saveData) {
			$this->set('message', 'Invalid data provided');
			$this->set('code', 403);

			return parent::display();
		}

		// Process $_POST vars
		foreach ($saveData as $key => $value) {

			if (!in_array($key, $disallowed)) {

				if (is_array($value)) {
					$value = json_encode($value);
				}

				$registry->set($key, $value);
			}
		}

		// Convert the data into array
		$data = $registry->toArray();
	}

	/**
	 * Invoke saving method
	 * /profile/json/save/TYPE
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function save()
	{
		$type = $this->input->get('type', 'profile', 'cmd');

		$allowed = array('profile', 'fields');

		if (in_array($type, $allowed)) {
			$method = 'save' . ucfirst($type);
			return $this->$method();
		}

		$this->set('code', 403);
		$this->set('message', 'Please specify the saving type');

		parent::display();
	}

	/**
	 * Save custom fields based on the provided unique_key
	 * /profile/json/save/fields
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function saveFields()
	{
		$userId = $this->validateAuth();
		$user = ES::user($userId);

		// @TODO process saving method
		$postData = $this->input->post->getArray();

		// The form name must be 'userFields'
		$fields = isset($postData['userFields']) ? $postData['userFields'] : null;

		if (!$fields) {

			// If array of fields is not exists, means the caller are trying to save single field value.
			$state = $this->saveField();

			if (!$state) {
				$this->set('code', 403);
				$this->set('message', 'Invalid fields data.');

				return parent::display();
			}
		}

		// Check whether the data is json encoded
		$json = ES::json();

		if ($json->isJsonString($fields)) {
			$fields = $json->decode($fields);
		}

		// Save the data
		foreach ($fields as $key => $value) {
			
			$state = $user->setFieldValue($key, $value);

			// Invalid unique key
			if (!$state) {
				$this->set('code', 403);
				$this->set('error', $key . ' is not a valid unique key.');
				
				return parent::display();
			}            

			// Check for errors
			if (is_array($state) && count($state) > 0) {
				$this->set('message', 'Error');

				foreach ($state as $identifier => $value) {
					$id = $this->extractFieldId($identifier);

					$fieldTable = ES::table('Field');

					$fieldTable->load($id);

					$this->set($fieldTable->unique_key, JText::_($value));
				}

				$this->set('code', 403);

				return parent::display();
			}
		}

		$this->set('code', 200);
		$this->set('message', 'Fields saved successfully.');

		return parent::display();
	}

	/**
	 * Method to save single field data
	 * From @saveFields
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function saveField()
	{
		$userId = $this->validateAuth();
		$user = ES::user($userId);

		$uniqueKey = $this->input->get('uniqueKey', '', 'raw');
		$value = $this->input->get('value', '', 'raw');

		if (!$uniqueKey) {
			return false;
		}

		// Save the data
		$state = $user->setFieldValue($uniqueKey, $value);

		// Invalid unique key
		if (!$state) {
			$this->set('code', 403);
			$this->set('error', $uniqueKey . ' is not a valid unique key.');

			return parent::display();
		}

		// Check for errors
		if (is_array($state) && count($state) > 0) {
			$this->set('message', 'Error');

			foreach ($state as $identifier => $value) {
				$id = $this->extractFieldId($identifier);

				$fieldTable = ES::table('Field');

				$fieldTable->load($id);

				$this->set($fieldTable->unique_key, JText::_($value));
			}                

			$this->set('code', 403);

			return parent::display();
		}     

		$this->set('code', 200);
		$this->set('message', 'Field saved successfully');

		return parent::display();
	}

	public function extractFieldId($identifier)
	{
		$prefix = SOCIAL_CUSTOM_FIELD_PREFIX . '-';
		$id = str_replace($prefix, '', $identifier);

		return $id;
	}

	/**
	 * Save full profile information based on the provided post data
	 * /profile/json/save/profiles
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function saveProfile()
	{
		$userId = $this->validateAuth();
		$user = ES::user($userId);

		// Get user data
		$data = $this->input->getArray('POST');

		if (empty($data)) {
			$this->set('code', 403);
			$this->set('message', 'Invalid data provided');

			return parent::display();            
		}

		// Only fetch relevant fields for this user.
		$options = array('profile_id' => $user->getProfile()->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT, 'group' => SOCIAL_FIELDS_GROUP_USER);

		// Get all published fields apps that are available in the current form to perform validations
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		// Format the save data
		$this->formatSaveData($data);

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		$fieldsLib = ES::fields();

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		// Build arguments to be passed to the field apps.
		$args = array(&$data, &$user);

		// Ensure that there is no errors.
		// @trigger onEditValidate
		$errors = $fieldsLib->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'validate'));

		// If there are errors, we should be exiting here.
		if (is_array($errors) && count($errors) > 0) {
			$this->set('code', 403);
			$this->set('message', JText::_('COM_EASYSOCIAL_PROFILE_SAVE_ERRORS'));

			foreach ($errors as $error => $value) {
				$this->set('error-' . $error, JText::_($value));
			}

			return parent::display();
		}

		// @trigger onEditBeforeSave
		$errors = $fieldsLib->trigger('onEditBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'beforeSave'));

		if (is_array($errors) && count($errors) > 0) {
			$this->set('code', 403);
			$this->set('message', JText::_('COM_EASYSOCIAL_PROFILE_ERRORS_IN_FORM'));

			foreach ($errors as $error => $value) {
				$this->set('error-' . $error, JText::_($value));
			}            

			return parent::display();
		}

		$user->bind($data);

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

		// Add stream item to notify the world that this user updated their profile.
		$user->addStream('updateProfile');

		// Update indexer
		$user->syncIndex();

		// @points: profile.update
		// Assign points to the user when their profile is updated
		ES::points()->assign('profile.update', 'com_easysocial', $user->id);

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);

		$dispatcher = ES::dispatcher();
		$args = array(&$user, &$fields, &$data);

		// @trigger: onUserProfileUpdate
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserProfileUpdate', $args);

		// @trigger onProfileCompleteCheck
		// Get back all the fields data after the saving is done and check for profile completeness
		$options = array('profile_id' => $user->getProfile()->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT, 'group' => SOCIAL_FIELDS_GROUP_USER);

		$fields = $fieldsModel->getCustomFields($options);

		$args = array(&$user);
		$completedFields = $fieldsLib->trigger('onProfileCompleteCheck', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		$table = ES::table('Users');
		$table->load(array('user_id' => $user->id));
		$table->completed_fields = count($completedFields);
		$table->store();

		// Return success message
		$this->set('code', 200);
		$this->set('message', JText::_('COM_EASYSOCIAL_PROFILE_ACCOUNT_UPDATED_SUCCESSFULLY'));

		return parent::display(); 

	}

	/**
	 * Get lists of custom fields
	 * /profile/json/getfields/AUTHKEY/USERID
	 * view=profile&format=json&task=getFields&auth=AUTHKEY&userid=USERID
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getFields()
	{
		$userId = $this->validateAuth();
		$user = ES::user($userId);

		// Check whether the caller want to get the value from single field
		// profile/json/getfields/AUTHKEY/USERID?uniqueKey=HEADER
		$uniqueKey = $this->input->get('uniqueKey', null, 'default');

		$fields = $user->getCustomFields($uniqueKey);

		if (!$fields) {
			return false;
		}

		// Load the language
		ES::language()->loadAdmin();

		$userFields = array();

		// Exclude these fields from the output.
		$specialField = array('header', 'joomla_fullname', 'joomla_username', 'joomla_password', 'joomla_email', 'avatar', 'cover');        

		foreach ($fields as $field) {

			if (in_array($field->element, $specialField)) {
				continue;
			}

			$obj = new stdClass();

			$obj->id = $field->id;
			$obj->unique_key = $field->unique_key;
			$obj->field_title = JText::_($field->title);
			$obj->field_description = JText::_($field->description);
			$obj->required = $field->required;
			$obj->data = $field->data;

			$userFields[] = $obj;
		}

		$this->set('userFields', $userFields);
		$this->set('code', 200);

		parent::display();

		exit;        
	}
}
