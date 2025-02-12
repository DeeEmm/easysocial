<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserMultilist extends SocialFieldItem
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
	public function onRegister(&$post , &$registration)
	{
		// Get list of child options
		$options = $this->params->get('items');

		// Current selected value.
		$selected = array();

		// If the value exists in the post data, it means that the user had previously set some values.
		if (empty($post[$this->inputName])) {
			if (!empty($options)) {
				foreach ($options as $id => $option) {
					if (!empty($option->default)) {
						$selected[] = $option->value;
					}
				}
			}
		} else {
			$selected = ES::makeObject($post[$this->inputName]);
		}

		// Detect if there's any errors.
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);

		// Set the default value.
		$this->set('selected', $selected);

		// Set options
		$this->set('options', $options);

		// Display the output.
		return $this->display();
	}

	/**
	 * Validate the field during registration
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterValidate($post, $registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

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
		$options = $this->params->get('items');

		$selected = array();

		if (empty($this->value)) {
			if (!empty($options)) {
				foreach ($options as $id => $option) {
					if (!empty($option->default)) {
						$selected[] = $option->value;
					}
				}
			}
		} else {
			$selected = ES::makeObject($this->value);
		}

		// If the value exists in the post data, it means that the user had previously set some values.
		if (isset($post[$this->inputName]) && !empty($post[$this->inputName])) {

			$selected = $post[$this->inputName];

			if (!is_array($post[$this->inputName])) {
				$selected = ES::makeObject($post[$this->inputName]);
			}
		}

		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('selected', $selected);
		$this->set('options', $options);

		return $this->display();
	}

	/**
	 * Validate the field during editing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEditValidate($post, $user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validate($value);
	}

	/**
	 * Display the field on the information section
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value = $this->value;

		if (!$value || $value == '[""]') {
			return;
		}

		$value = ES::makeObject($value);

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$field = $this->field;
		$options = array();

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
				$params['operators[]'] = 'contain';
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

		if (empty($value)) {
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
	 * General method to validate the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function validate($value)
	{
		$selected = ES::makeObject($value);

		if ($this->isRequired() && empty($selected)) {
			$this->setError(JText::_('PLG_FIELDS_MULTILIST_VALIDATION_PLEASE_SELECT_A_VALUE'));

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
	 * @since  1.2
	 * @access public
	 */
	public function onGetValue($user)
	{
		return $this->getValue();
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
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

		$obj = ES::makeObject($this->value);

		if (empty($obj)) {
			return false;
		}

		return true;
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
}

class SocialFieldsUserMultilistValue extends SocialFieldValue
{
	public function toString()
	{
		$values = array_values($this->value);

		return implode(', ', $values);
	}
}
