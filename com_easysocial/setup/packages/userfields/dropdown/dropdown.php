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

class SocialFieldsUserDropdown extends SocialFieldItem
{
	public function getValue()
	{
		$container = $this->getValueContainer();

		$value = $container->data;

		$option = ES::table('fieldoptions');
		$option->load(array('parent_id' => $this->field->id, 'key' => 'items', 'value' => $value));

		$container->value = $option->title;

		return $container;
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

		if (empty($options)) {
			return array();
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
	 */
	public function onRegister(&$post, &$registration)
	{
		// Current selected value.
		$selected = '';

		// Get list of child options
		$options = $this->params->get('items');

		if (empty($options)) {
			return;
		}

		foreach ($options as $id => $option) {
			if ($option->default) {
				$selected = $option->value;
			}
		}

		// If the value exists in the post data, it means that the user had previously set some values.
		if (isset($post[$this->inputName]) && !empty($post[$this->inputName])) {
			$selected = $post[$this->inputName];
		}

		// Detect if there's any errors.
		$errors = $registration->getErrors($this->inputName);

		$this->set('error', $errors);

		// Set the default value.
		$this->set('selected', $selected);

		// Set options
		$this->set('options', $options);

		// Display the output.
		return $this->display();
	}

	/**
	 * Trigger field validation during registration
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$user)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Displays the field input for user when they edit their profile.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the list items
		$options = $this->params->get('items');
		$selected = '';

		if ($options) {
			foreach ($options as $id => $option) {
				if ($option->default) {
					$selected = $option->value;
				}
			}
		}

		// If this field have value, then we use from value
		$selected = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		// Get any errors
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('selected', $selected);
		$this->set('options', $options);

		return $this->display();
	}

	/**
	 * Trigger validation during field editing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value = $this->value;

		if (!$value) {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$option = ES::table('fieldoptions');
		$option->load(array('parent_id' => $this->field->id, 'key' => 'items', 'value' => $value));

		$field = $this->field;

		$advGroups = array(SOCIAL_FIELDS_GROUP_GROUP, SOCIAL_FIELDS_GROUP_USER, SOCIAL_FIELDS_GROUP_EVENT, SOCIAL_FIELDS_GROUP_PAGE);

		// reset the search link. #4168
		$this->set('advancedsearchlink', '');

		if (in_array($field->type, $advGroups) && $field->searchable) {
			$params = array('layout' => 'advanced');

			if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
				$params['type'] = $field->type;
				$params['uid'] = $field->uid;
			}

			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'equal';
			$params['conditions[]'] = $this->value;

			$advsearchLink = FRoute::search($params);
			$this->set('advancedsearchlink', $advsearchLink);
		}

		// Push variables into theme.
		$this->set('option', $option);
		$this->set('value', $value);

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

		if (!$value) {
			return '';
		}

		// retrieve field data
		$field = $this->field;

		// retrieve formatted value
		$options = $this->getValue();
		$formattedValue = $options->value;

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $formattedValue;

		return $data;
	}

	/**
	 * return formated string from the fields value
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onIndexer($userFieldData)
	{
		if (!$this->field->searchable) {
			return false;
		}

		$content = trim($userFieldData);

		if ($content) {
			return $content;
		} else {
			return false;
		}
	}

	/**
	 * Method to validate the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function validate($value)
	{
		if ($this->isRequired() && (is_null($value) || $value == '')) {
			$this->setError(JText::_('PLG_FIELDS_DROPDOWN_VALIDATION_PLEASE_SELECT_A_VALUE'));
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

		return $this->validate($this->value);
	}
}
