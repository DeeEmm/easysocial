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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserRadio extends SocialFieldItem
{
	public function getValue()
	{
		$container = $this->getValueContainer();

		$container->data = ES::makeObject($container->raw);

		$container->value = array();

		if ($container->data) {
			foreach ($container->data as $v) {
				$option = ES::table('fieldoptions');
				$option->load(array('parent_id' => $this->field->id, 'key' => 'items', 'value' => $v));

				$container->value[$option->value] = $option->title;
			}
		}

		return $container;
	}

	/**
	 * Returns a formatted data for display from the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getDisplayValue()
	{
		$options = $this->getValue();
		$values = array();

		foreach ($options as $option) {
			$values[$option->value] = $option->title;
		}

		return $values;
	}

	/**
	 * Method to retrieve the options for this field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getOptions()
	{
		$options = $this->field->getOptions('items');

		if (!$options) {
			$options = array();
		}

		$result = array();

		foreach ($options as $o) {
			$result[$o->value] = $o->title;
		}

		return $result;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function onRegister(&$post, &$registration)
	{
		// Selected value
		$selected = array();

		// Test if the user had tried to submit any values.
		if (!empty($post[$this->inputName])) {
			$selected = json_decode($post[$this->inputName]);
		}

		// Get a list of options for this field.
		$options = $this->field->getOptions('items');

		// If there's no options, we shouldn't even be showing this field.
		if (empty($options)) {
			return;
		}

		// Detect if there's any errors.
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('selected', $selected);
		$this->set('options', $options);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Validate mini registration
	 *
	 * @since   2.0.11
	 * @access  public
	 */
	public function onRegisterMiniValidate(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// Only validate the field if it set the field to be visible in mini registration
		if ($this->params->get('visible_mini_registration')) {
			return $this->validate($value);
		}
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$options = $this->field->getOptions('items');

		// Get the value
		$selected = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		if (empty($selected) || $selected == '[""]') {
			$selected = [];
		}

		if (!is_array($selected)) {
			$selected = ES::makeArray($selected);
		}

		$error = $this->getError($errors);

		// Set the value
		$this->set('options', $options);
		$this->set('error', $error);
		$this->set('selected', $selected);

		return $this->display();
	}

	/**
	 * Trigger onEditBeforeSave when saving the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		$this->beforeSave($post, $user);
	}

	/**
	 * Trigger onRegisterBeforeSave when saving the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post, &$user)
	{
		$this->beforeSave($post, $user);
	}

	public function beforeSave(&$post, &$user)
	{
		// We need to store as empty data so that 
		// the default option is not selected when there is no data #2091
		if (empty($post[$this->inputName])) {
			$post[$this->inputName] = '[""]';
		}
	}

	/**
	 * Determines whether there's any errors in the submission in the edit form.
	 *
	 * @since	2.1
	 * @access	public
	 *
	 */
	public function onEditValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Display the field output
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value = $this->value;

		// If the value is [""], means it is empty
		if (!$value || $value == '[""]') {
			return;
		}

		$value = ES::makeObject($value);

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$options = array();

		$field = $this->field;

		$advGroups = array(SOCIAL_FIELDS_GROUP_GROUP, SOCIAL_FIELDS_GROUP_USER, SOCIAL_FIELDS_GROUP_EVENT, SOCIAL_FIELDS_GROUP_PAGE);
		$addAdvLink = in_array($field->type, $advGroups) && $field->searchable;

		foreach ($value as $v) {
			$option = ES::table('fieldoptions');
			$option->load(array('parent_id' => $this->field->id, 'key' => 'items', 'value' => $v));

			if ($addAdvLink) {
				$params = array('layout' => 'advanced');

				if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
					$params['type'] = $field->type;
					$params['uid'] = $field->uid;
				}

				$params['criterias[]'] = $field->unique_key . '|' . $field->element;
				$params['operators[]'] = 'equal';
				$params['conditions[]'] = $v;

				$advsearchLink = FRoute::search($params);
				$option->advancedsearchlink = $advsearchLink;
			}

			$options[] = $option;
		}

		$this->set('options', $options);

		return $this->display();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$value = $this->value;

		// If the value is [""], means it is empty
		if (!$value || $value == '[""]') {
			return '';
		}

		// retrieve field data
		$field = $this->field;

		// retrieve formatted value
		$options = $this->getValue();
		$formattedValue = implode(", ", $options->value);

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $formattedValue;

		return $data;
	}

	/**
	 * Method to validate the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function validate($value)
	{
		if (!empty($value)) {
			$value = ES::json()->decode($value);
		}

		// If this is required, check for the value.
		if($this->isRequired() && empty($value))
		{
			$this->setError(JText::_('PLG_FIELDS_RADIO_CHECK_AT_LEAST_ONE_ITEM'));
			return false;
		}

		return true;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		return $this->validate($this->value);
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function onGetValue($user)
	{
		return $this->getValue();
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!ES::config()->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		if (empty($this->value)) {
			return false;
		}

		$value = ES::makeObject($this->value);

		if (empty($value)) {
			return false;
		}

		return true;
	}
}

class SocialFieldsUserRadioValue extends SocialFieldValue
{
	public function toString()
	{
		$values = array_values($this->value);

		return implode(', ', $values);
	}
}