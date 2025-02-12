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

ES::import('admin:/includes/fields/fields');

class SocialFieldsUserMultidropdown extends SocialFieldItem
{
	public function getValue()
	{
		$container = $this->getValueContainer();

		$container->data = ES::makeArray($container->raw);

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
	 * Get and formatting the field options
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
	 * Renders the multiple dropdown field during creation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);

		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->onOutput($value);
	}

	/**
	 * Validate the field during registration
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->onValidate($value);
	}

	/**
	 * Trigger onRegisterBeforeSave during registration
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post)
	{
		$post[$this->inputName] = $this->onBeforeSave($post);
	}

	/**
	 * Display the field during editing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$error = $this->getError($errors);

		$this->set('error', $error);

		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		return $this->onOutput($value);
	}

	/**
	 * Validate the field during editing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->onValidate($value);
	}

	/**
	 * Trigger onEditBeforeSave during editing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onEditBeforeSave(&$post)
	{
		$post[$this->inputName] = $this->onBeforeSave($post);
	}

	/**
	 * Renders the output of the multiple dropdown in information section
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onDisplay($user)
	{
		if (empty($this->value)) {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$json = ES::json();

		$result = $json->decode($this->value);

		if (!is_array($result) || empty($result)) {
			return;
		}

		$field = $this->field;

		$advGroups = array(SOCIAL_FIELDS_GROUP_GROUP, SOCIAL_FIELDS_GROUP_USER, SOCIAL_FIELDS_GROUP_EVENT, SOCIAL_FIELDS_GROUP_PAGE);

		$addAdvLink = in_array($field->type, $advGroups) && $field->searchable;

		$values = array();

		foreach ($result as $r) {
			$r = trim($r);

			if (empty($r)) {
				continue;
			}

			$option = ES::table('fieldoptions');
			$option->load(array('parent_id' => $this->field->id, 'key' => 'items', 'value' => $r));

			if ($addAdvLink) {
				$params = array('layout' => 'advanced');

				if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
					$params['type'] = $field->type;
					$params['uid'] = $field->uid;
				}

				$params['criterias[]'] = $field->unique_key . '|' . $field->element;
				$params['operators[]'] = 'contain';
				$params['conditions[]'] = $r;

				$advsearchLink = FRoute::search($params);
				$option->advancedsearchlink = $advsearchLink;
			}

			$values[] = $option;
		}

		if (empty($values)) {
			return;
		}

		$this->set('values', $values);

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
	 * Process the field during output
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function onOutput($value)
	{
		// if value is 1, means, that user doesnt selected anything on the previous edit.
		// it is safe to say that we don't need to display default value
		$showDefault = true;

		if ($value == '1') {
			$showDefault = false;
		}

		$selected = [];

		if (!is_array($value) && !empty($value)) {
			$selected = json_decode($value);

			// if the selected value still doesn't show array mean this original value is not json format
			// Mean the user doesn't select anything so we need to return back to the empty array
			if (!is_array($selected)) {
				$selected = [];
			}
		}

		// Get a list of choices
		$choices = $this->params->get('items');

		if (!is_array($choices)) {
			$choices = array();
		}

		// Add a default choice
		array_unshift($choices, (object) array('default' => !$selected, 'value' => '', 'title' => JText::_($this->params->get('placeholder'))));

		$limit = $this->params->get('max');
		$count = count(array_filter($selected));

		$this->set('choices', $choices);
		$this->set('limit', $limit);
		$this->set('count', $count);
		$this->set('selected', $selected);
		$this->set('showDefault', $showDefault);

		return $this->display();
	}

	/**
	 * Method to validate the field
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function onValidate($data)
	{
		if (!$this->isRequired()) {
			return true;
		}

		if (empty($data)) {
			$this->setError(JText::_('PLG_FIELDS_MULTIDROPDOWN_VALIDATION_REQUIRED_FIELD'));
			return false;
		}

		$json = ES::json();

		$value = $json->decode($data);

		if (!is_array($value) || empty($value)) {
			$this->setError(JText::_('PLG_FIELDS_MULTIDROPDOWN_VALIDATION_REQUIRED_FIELD'));
			return false;
		}

		foreach ($value as $v) {
			if (!empty($v)) {
				return true;
			}
		}

		$this->setError(JText::_('PLG_FIELDS_MULTIDROPDOWN_VALIDATION_REQUIRED_FIELD'));
		return false;
	}

	/**
	 * Method to process the field data during saving
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function onBeforeSave($post)
	{
		if (empty($post[$this->inputName])) {
			unset($post[$this->inputName]);
			return true;
		}

		$json = ES::json();

		$value = $json->decode($post[$this->inputName]);

		if (!is_array($value) || empty($value)) {
			unset($post[$this->inputName]);
			return true;
		}

		$result = array();

		foreach ($value as $v) {
			$v = trim($v);

			if (!empty($v)) {
				$result[] = $v;
			}
		}

		if (!empty($result)) {
			$post[$this->inputName] = $result;
		} else {
			unset($post[$this->inputName]);
			return true;
		}

		$post[$this->inputName] = json_encode($post[$this->inputName]);

		return $post[$this->inputName];
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		return $this->onValidate($this->value);
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

class SocialFieldsUserMultidropdownValue extends SocialFieldValue
{
	public function toString()
	{
		$values = array_values($this->value);

		return implode(', ', $values);
	}
}
