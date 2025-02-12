<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/joomla_fullname/helper');

class SocialFieldsUserJoomla_fullname extends SocialFieldItem
{
	/**
	 * format the value used in data export
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onExport($data, $user)
	{
		$field = $this->field;

		$formatted = [
						'name' => '',
						'first' => '',
						'middle' => '',
						'last' => ''
					];

		if (isset($data[$field->id])) {
			$formatted['name'] = isset($data[$field->id]['name']) ? $data[$field->id]['name'] : '';
			$formatted['first'] = isset($data[$field->id]['first']) ? $data[$field->id]['first'] : '';
			$formatted['middle'] = isset($data[$field->id]['middle']) ? $data[$field->id]['middle'] : '';
			$formatted['last'] = isset($data[$field->id]['last']) ? $data[$field->id]['last'] : '';
		}

		// lets further check if user has atleast the 'name' segment or not. if not, then let return user 'fullname' to caller.
		if (!$formatted['name'] && !$formatted['first'] && !$formatted['middle'] && !$formatted['last']) {
			// if all empty, let load up user data and return fullname atlease.
			$jUser = JFactory::getUser($user);
			$formatted['name'] = $jUser->name;
		} else{
			$formatted['name'] = [];

			// Let format it to such as "first,middle,last"
			foreach ($formatted as $key => $item) {
				if ($key == 'name') {
					continue;
				}

				if (empty($formatted['name'])) {
					$formatted['name'] = $item ? $item : ' ';
				} else {
					$formatted['name'] .= $item ? $item : ' ';
				}

				if ($key !== 'last') {
					$formatted['name'] .= ',';
				}
			}
		}

		return $formatted;
	}

	/**
	 * We need to ensure that the fields data are stored so that we don't need to mess up with the name column in Joomla.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post)
	{
		// Ensure that the first name is always populated
		$post['first_name'] = $this->getFirstNameFallback($post);

		return $this->save($post);
	}
	/**
	 * We need to ensure that the fields data are stored so that we don't need to mess up with the name column in Joomla.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		// Ensure that the first name is always populated
		$post['first_name'] = $this->getFirstNameFallback($post);

		// Detect if the name is changed.
		$post['nameChanged'] = $this->nameChanged($post, $user);

		return $this->save($post);
	}

	/**
	 * Triggers before a user is saved
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAdminEditBeforeSave(&$post, &$user)
	{
		// Ensure that the first name is always populated
		$post['first_name'] = $this->getFirstNameFallback($post);

		// Detect if the name is changed.
		$post['nameChanged'] = $this->nameChanged($post, $user);

		return $this->save($post);
	}

	/**
	 * Processes after a user registers on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$data, &$user)
	{
		if ($this->config->get('users.aliasName') != 'realname') {
			return;
		}

		// Only proceed when the name has been changed.
		if (isset($data['nameChanged']) && !$data['nameChanged']) {
			return;
		}

		// only if the alias is empty as the alias might be created already from user plugin.
		// #909
		if (!$user->alias) {
			$this->saveAlias($data, $user);
		}
	}

	/**
	 * Triggers after a user is saved.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditAfterSave(&$data, &$user)
	{
		if ($this->config->get('users.aliasName') != 'realname') {
			return;
		}

		// Only proceed when the name has been changed.
		if (isset($data['nameChanged']) && !$data['nameChanged']) {
			return;
		}

		$this->saveAlias($data, $user);
	}

	/**
	 * Triggers after a user is saved by the admin
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAdminEditAfterSave(&$data, &$user)
	{
		if ($this->config->get('users.aliasName') != 'realname') {
			return;
		}

		// Only proceed when the name has been changed.
		if (isset($data['nameChanged']) && !$data['nameChanged']) {
			return;
		}

		$this->saveAlias($data, $user);
	}

	/**
	 * Determines if the name has changed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function nameChanged($post, $user)
	{
		$this->normalizeNameData($post);

		// Detect if the name has changed
		$firstName = isset($post['first_name']) ? $post['first_name'] : '';
		$middleName = isset($post['middle_name']) ? $post['middle_name'] : '';
		$lastName = isset($post['last_name']) ? $post['last_name'] : '';

		// Build the real name.
		$name = $this->buildFullname($firstName, $middleName, $lastName);

		if ($name != $user->name) {
			return true;
		}

		return false;
	}

	/**
	 * Responsible to save the alias of the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveAlias(&$data, &$user)
	{
		$this->normalizeNameData($data);

		// Get the first name.
		$firstName = isset($data['first_name']) ? $data['first_name'] : '';
		$middleName = isset($data['middle_name']) ? $data['middle_name'] : '';
		$lastName = isset($data['last_name']) ? $data['last_name'] : '';

		// Build the real name.
		$name = $this->buildFullname($firstName, $middleName, $lastName);

		// If name is empty, it's probably because we are unable to obtain the full name
		if (!$name) {
			$name = $user->name;
		}

		$alias = JFilterOutput::stringURLSafe($name);

		// Check if the alias exists.
		$model = ES::model('Users');
		$user->alias = $model->generateAlias($alias, $user->id);

		$user->save();
	}

	private function save(&$data)
	{
		$this->normalizeNameData($data);

		// Get the first name.
		$firstName = isset($data['first_name']) ? $data['first_name'] : '';
		$middleName = isset($data['middle_name']) ? $data['middle_name'] : '';
		$lastName = isset($data['last_name']) ? $data['last_name'] : '';

		if ($firstName || $middleName || $lastName) {
			$name = $this->buildFullname($firstName, $middleName, $lastName);

			// Assign a "name" index so that `#__users`.`name` can have proper values.
			$data['name'] = $name;
		} else {
			$name = $data['name'];
		}

		// Assign the data to be stored in our own table.
		$nameObj = new stdClass;
		$nameObj->first = $firstName;
		$nameObj->middle = $middleName;
		$nameObj->last = $lastName;
		$nameObj->name = $name;

		$data[$this->inputName] = $nameObj;


		return true;
	}

	/**
	 * Normalize the name data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function normalizeNameData(&$data)
	{
		$data['name'] = isset($data['name']) ? $this->normalizeName($data['name']) : '';
		$data['first_name'] = isset($data['first_name']) ? $this->normalizeName($data['first_name']) : '';
		$data['middle_name'] = isset($data['middle_name']) ? $this->normalizeName($data['middle_name']) : '';
		$data['last_name'] = isset($data['last_name']) ? $this->normalizeName($data['last_name']) : '';
	}

	/**
	 * Normalize the name format
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function normalizeName($name)
	{
		// Ensure the name is not empty to avoid unecessary php warning
		if ($name) {

			// Remove unwanted spacing in the name
			$name = ES::string()->trimSpacing($name);
		}

		return $name;
	}

	/**
	 * Build fullname based on the store format specified.
	 *
	 * @since  1.3.7
	 * @access public
	 */
	public function buildFullname($first, $middle, $last)
	{
		// 1: first, middle, last
		// 2: last, middle, first

		$format = $this->params->get('store_format');

		$name = '';

		if ($format == 1 && !empty($first)) {
			$name .= $first;
		}

		if ($format == 2 && !empty($last)) {
			$name .= $last;
		}

		if (!empty($middle)) {
			$name .= ' ' . $middle;
		}

		if ($format == 1 && !empty($last)) {
			$name .= ' ' . $last;
		}

		if ($format == 2 && !empty($first)) {
			$name .= ' ' . $first;
		}

		return $name;
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$this->normalizeNameData($post);

		// Get the first name from the query.
		$firstName = isset($post['first_name']) ? trim($post['first_name']) : '';

		// Get the middle name
		$middleName = isset($post['middle_name']) ? trim($post['middle_name']) : '';

		// Get the last name
		$lastName = isset($post['last_name']) ? trim($post['last_name']) : '';

		return $this->validateName($firstName, $middleName, $lastName);
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$this->normalizeNameData($post);

		// Get the first name from the query.
		$firstName = isset($post['first_name']) ? trim($post['first_name']) : '';

		// Get the middle name
		$middleName = isset($post['middle_name']) ? trim($post['middle_name']) : '';

		// Get the last name
		$lastName = isset($post['last_name']) ? trim($post['last_name']) : '';

		return $this->validateName($firstName, $middleName, $lastName, $user);
	}

	/**
	 * Validates the field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function validateName($firstName, $middleName, $lastName, $user = null)
	{
		// Test if this field is required and firstName is the basic minumum
		if ($this->isRequired() && empty($firstName)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_FULLNAME_VALIDATION_EMPTY_NAME'));

			return false;
		}

		$name = array('FirstName' => $firstName, 'MiddleName' => $middleName, 'LastName' => $lastName);

		// Ensure that the name is allowed
		foreach ($name as $key => $value) {
			$current = '';

			if ($user) {
				$action = 'get' . $key;
				$current = $user->$action($this->params->get('unique_key'));
			}

			if (!SocialFieldsUserJoomlaFullnameHelper::allowed($value, $this->params, $current)) {
				$this->setError(JText::_('PLG_FIELDS_JOOMLA_FULLNAME_NOT_ALLOWED'));

				return false;
			}
		}
		

		if ($this->params->get('max') > 0) {
			$totalLength = ESJString::strlen($firstName) + ESJString::strlen($middleName) + ESJString::strlen($lastName);

			if ($totalLength > $this->params->get('max')) {
				$this->setError(JText::_('PLG_FIELDS_JOOMLA_FULLNAME_VALIDATION_NAME_TOO_LONG'));
				return false;
			}
		}

		if ($this->params->get('regex_validate')) {
			$name = $firstName;

			if (!empty($middleName)) {
				$name = ' ' . $middleName;
			}

			if (!empty($lastName)) {
				$name = ' ' . $lastName;
			}

			$format = $this->params->get('regex_format');

			$modifier = $this->params->get('regex_modifier');

			$pattern = '/' . $format . '/' . $modifier;

			$result = preg_match($pattern, $name);

			if (empty($result)) {
				$this->setError(JText::_('PLG_FIELDS_JOOMLA_FULLNAME_VALIDATION_INVALID_NAME'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post,  &$registration)
	{
		// Detect if there's any errors.
		$error 	= $registration->getErrors($this->inputName);

		$this->displayForm(empty($error) ? $post : array());

		$this->set('error', $error);
		$this->set('userId', null);

		return $this->display();
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when they edit their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$default = $this->getName($user, $this->value);

		$this->displayForm($post, $default);

		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('userId', $user->id);

		return $this->display();
	}

	public function displayForm($post, $default = null)
	{
		$firstName = isset($post['first_name']) ? trim($post['first_name']) : (isset($default->first) ? $default->first : '');
		$middleName = isset($post['middle_name']) ? trim($post['middle_name']) : (isset($default->middle) ? $default->middle : '');
		$lastName = isset($post['last_name']) ? trim($post['last_name']) : (isset($default->last) ? $default->last : '');

		$this->set('firstName', $this->escape($firstName));
		$this->set('middleName', $this->escape($middleName));
		$this->set('lastName', $this->escape($lastName));

		// Build the real name.
		$name = $this->buildFullname($firstName, $middleName, $lastName);

		$this->set('name', $this->escape($name));

		return true;
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$name = new stdClass();

		if (empty($this->value)) {
			$name->first = $this->escape($user->name);
			$name->middle = '';
			$name->last = '';
			$name->name = $this->escape($user->name);
		} else {
			$obj = ES::makeObject($this->value);

			$name->first = !empty($obj->first) ? $this->escape($obj->first) : '';
			$name->middle = !empty($obj->middle) ? $this->escape($obj->middle) : '';
			$name->last	= !empty($obj->last) ? $this->escape($obj->last) : '';
			$name->name	= !empty($obj->name) ? $this->escape($obj->name) : '';
		}

		$this->set('name', $name);

		return $this->display();
	}

	public function getValue()
	{
		$user = ES::user($this->field->uid);

		return $this->getName($user, $this->field->data);
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$name = new stdClass();

		if (empty($this->value)) {
			$name->first = $this->escape($user->name);
			$name->middle = '';
			$name->last = '';
			$name->name = $this->escape($user->name);
		} else {
			$obj = ES::makeObject($this->value);

			$name->first = !empty($obj->first) ? $this->escape($obj->first) : '';
			$name->middle = !empty($obj->middle) ? $this->escape($obj->middle) : '';
			$name->last	= !empty($obj->last) ? $this->escape($obj->last) : '';
			$name->name	= !empty($obj->name) ? $this->escape($obj->name) : '';
		}

		$nameFormat = $this->params->get('format', 1);

		if ($nameFormat == 1) {
			$name = $name->first . ' ' . $name->middle . ' ' . $name->last;
		}

		if ($nameFormat == 2) {
			$name = $name->last . ' ' . $name->middle . ' ' . $name->first;
		}

		if ($nameFormat == 3) {
			$name = $name->name;
		}

		if ($nameFormat == 4) {
			$name = $name->first . ' ' . $name->last;
		}

		if ($nameFormat == 5) {
			$name = $name->last . ' ' . $name->first;
		}

		// retrieve field data
		$field = $this->field;

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $name;

		return $data;
	}

	/**
	 * Retrieves the user's name.
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function getName(SocialUser $user, $value = '')
	{
		$obj = new SocialFieldUserJoomla_FullnameObject($user, $value);

		$obj->setFormat($this->params->get('store_format'));

		return $obj;
	}

	/**
	 * There are instances where the name is empty
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getFirstNameFallback($post)
	{
		// Always fallback if the first and last name is empty
		if (empty($post['first_name']) && !empty($post['username']) && $this->params->get('username_fallback')) {
			return $post['username'];
		}

		// If username is empty, then we need to get their e-mail
		if (empty($post['first_name']) && empty($post['username']) && $this->params->get('username_fallback')) {
			return $post['email'];
		}

		return $post['first_name'];
	}

	public function onOAuthGetMetaFields(&$fields)
	{
		$fields = array_merge($fields, array('name', 'first_name', 'middle_name', 'last_name'));
	}

	public function onRegisterOAuthBeforeSave(&$post, &$client)
	{
		$this->save($post);
	}

	public function onRegisterOAuthAfterSave(&$post, &$client, &$user)
	{
		if ($this->config->get('users.aliasName') != 'realname') {
			return;
		}

		// Only proceed when the name has been changed.
		if (isset($post['nameChanged']) && !$post['nameChanged']) {
			return;
		}

		$this->saveAlias($post, $user);
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		$firstName = '';

		if (!empty($this->value)) {
			$obj = ES::makeObject($this->value);

			$firstName = !empty($obj->first) ? $obj->first : '';
		}

		if ($this->isRequired() && empty($firstName) && empty($user->name)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_FULLNAME_VALIDATION_EMPTY_NAME'));

			return false;
		}

		return true;
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!$this->config->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		if (empty($this->value)) {
			return false;
		}

		$obj = ES::makeObject($this->value);

		if (empty($obj->first) && empty($user->name)) {
			return false;
		}

		return true;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onGetValue($user)
	{
		$container = $this->getValueContainer();

		if (ES::json()->isJsonString($container->raw)) {
			$container->data = ES::json()->decode($container->raw);
		}

		$container->value = $this->getName($user, $container->raw);

		return $container;
	}
}

class SocialFieldUserJoomla_FullnameObject
{
	public $first = '';
	public $middle = '';
	public $last = '';
	public $name = '';

	public $format = 1;

	public function __construct($user, $data = null)
	{
		$this->load($user, $data);
	}

	public function load($user, $data = null)
	{
		$this->first = $user->name;
		$this->name = $user->name;

		$json = ES::json();

		if ($json->isJsonString($data)) {
			$data = $json->decode($data);
		} else {
			$data = (object) $data;
		}

		if (!empty($data->name)) {
			$this->name = $data->name;
		}

		if (!empty($data->first)) {
			$this->first = $data->first;
		}

		if (!empty($data->middle)) {
			$this->middle = $data->middle;
		}

		if (!empty($data->last)) {
			$this->last = $data->last;
		}
	}

	public function toJson()
	{
		return ES::json()->encode(array(
			'first' => $this->first,
			'middle' => $this->middle,
			'last' => $this->last,
			'name' => $this->name
		));
	}

	public function toString()
	{
		if (!empty($this->name) && empty($this->first) && empty($this->middle) && empty($this->last)) {
			return $this->name;
		}

		$name = '';

		if ($this->format == 1 && !empty($this->first)) {
			$name .= $this->first;
		}

		if ($this->format == 2 && !empty($this->last)) {
			$name .= $this->last;
		}

		if (!empty($this->middle)) {
			$name .= $this->middle;
		}

		if ($this->format == 1 && !empty($this->last)) {
			$name .= $this->last;
		}

		if ($this->format == 2 && !empty($this->first)) {
			$name .= $this->first;
		}

		return $name;
	}

	public function __toString()
	{
		return $this->toString();
	}

	public function setFormat($format = 1)
	{
		$this->format = $format;
	}
}
