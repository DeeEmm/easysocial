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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserAutocomplete extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register on the site.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onRegister(&$post, SocialTableRegistration &$registration)
	{
		// Get any errors on the existing requests
		$error = $registration->getError();
		$value = $this->getValueFromPost($post);

		// Defaults
		$selected = array();
		$exclusion = array();

		if ($value) {
			$exclusion = $value;

			$valueObj = json_decode($exclusion);

			foreach ($valueObj as $optionId) {

				$option = ES::table('FieldOptions');
				$option->load(array('id' => $optionId, 'parent_id' => $this->field->id));

				// Append the value in array.
				$selected[] = $option;
			}
		}

		$this->set('exclusion', $exclusion);
		$this->set('selected', $selected);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	1.4
	 * @access	public
	 *
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$error = $this->getError($errors);

		$selected = array();

		if ($this->value) {
			$selected = json_decode($this->value);
			$selected = $this->getSelectedOptions($selected);
		}

		$this->set('exclusion', $this->value);
		$this->set('selected', $selected);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Given a list of option id's, retrieve the results
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getSelectedOptions($options)
	{
		$result = array();

		foreach ($options as $value) {
			$table = ES::table('FieldOptions');
			$table->load(array('value' => $value, 'parent_id' => $this->field->id));

			$result[] = $table;
		}

		return $result;
	}

	/**
	 * Displays the selected field values on the user's profile
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onDisplay(SocialUser $user)
	{
		// Get the selected values
		$value = $this->value;

		if (!$value) {
			return;
		}

		// Privacy checks
		if (!$this->allowedPrivacy($user)) {
			return;
		}

		// Get the selected values
		$value = json_decode($value);
		$options = $this->getSelectedOptions($value);

		// We need to format the result for advanced search's linkage
		foreach ($options as $option) {

			$option->searchLink = false;

			if ($this->field->searchable) {

				$params = array('layout' => 'advanced');
				$params['criterias[]'] = $this->field->unique_key . '|' . $this->field->element;
				$params['operators[]'] = 'contain';
				$params['conditions[]'] = $option->value;

				$option->searchLink = FRoute::search($params);
			}
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

		if (!$value) {
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
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function onGetValue($user)
	{
		return $this->getValue()->value;
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAdminEditValidate(&$post, &$user)
	{
		return true;
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$value = $this->getValueFromPost($post);

		// Validate the field value
		$valid = $this->validate($value);

		return $valid;
	}

	/**
	 * Validates the field input for user when they register their account.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Validate mini registration
	 *
	 * @since   4.0
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
	 * Performs validation checks before storing the value
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function validate($value)
	{
		$value = json_decode($value);

		if (!$value && $this->isRequired()) {
			$this->setError(JText::_('PLG_FIELDS_USER_AUTOCOMPLETE_PLEASE_SELECT_SOME_VALUES'));
		}

		return true;
	}

	/**
	 * To get the correct field value based on the input value.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		$post[$this->inputName] = $this->autoCompleteSave($post);
		return true;
	}

	public function onRegisterBeforeSave(&$post, &$user)
	{
		$post[$this->inputName] = $this->autoCompleteSave($post);
		return true;
	}

	public function onAdminBeforeSave(&$post, &$user)
	{
		$post[$this->inputName] = $this->autoCompleteSave($post);
		return true;
	}

	public function autoCompleteSave($post)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		if (!$value) {
			return false;
		}

		// Since all the value is being set as the field id, we need to get the proper value
		$valueObj = json_decode($value);

		// If the value is not a proper json string, we should just skip this
		if (!$valueObj) {
			return false;
		}

		$result = array();

		if (is_array($valueObj)) {

			foreach ($valueObj as $optionValue) {
				if ($optionValue) {

					// Check if the id is valid integer
					$optionId = (int) $optionValue;

					// If null, that's mean the value is string and we should respect that.
					if (!$optionId) {
						$result[] = $optionValue;
						continue;
					}

					// If the id is integer, try to get the correct value based on option id.
					$option = ES::table('FieldOptions');
					$option->load(array('id' => $optionId, 'parent_id' => $this->field->id));

					// Append the value in array.
					$result[] = $option->value;
				}
			}
		}

		// Encode the data into json string.
		$result = json_encode($result);
		return $result;
	}
}
