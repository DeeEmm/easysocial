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

class SocialFieldsUserNumeric extends SocialFieldItem
{
	public function __construct($options)
	{
		parent::__construct($options);
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value = $this->value;

		if ($value === '') {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		// Push variables into theme.
		$this->set('value', $this->escape($value));

		// linkage to advanced search page.
		$field = $this->field;

		$searchable = false;

		$advGroups = array(
				SOCIAL_FIELDS_GROUP_GROUP,
				SOCIAL_FIELDS_GROUP_USER,
				SOCIAL_FIELDS_GROUP_EVENT,
				SOCIAL_FIELDS_GROUP_PAGE
			);

		// reset the search link. #4168
		$this->set('advancedsearchlink', '');

		if (in_array($field->type, $advGroups) && $field->searchable) {

			$params = array('layout' => 'advanced');

			if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
				$params['type'] = $field->type;
				$params['uid'] = $field->uid;
			}

			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'contain';
			$params['conditions[]'] = $this->escape($value);

			$advsearchLink = ESR::search($params);

			$this->set('advancedsearchlink', $advsearchLink);

			$searchable = true;
		}

		// Set searchable
		$this->set('searchable', $searchable);

		return $this->display();
	}

	/**
	 * Responsible to output the form when the user is being edited by the admin
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onAdminEdit(&$post, &$user, $errors)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		// Manually override the readonly parameter for admin
		$this->params->set('readonly', false);
		$this->set('params', $this->params);

		return $this->display();
	}

	/**
	 * Executes before a user's edit is save in admin more
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAdminEditBeforeSave(&$post, &$user)
	{
		// Regardless of readonly parameter, we allow admin to edit this field
		return true;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Executes before a user's edit is saved.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		// use isset instead of !empty because we do not even wan empty string or false value here
		if ($this->params->get('readonly') && isset($post[$this->inputName])) {
			unset($post[$this->inputName]);
		}

		return true;
	}

	/**
	 * Validates the field input for user when they edit their account.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * General validation function
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	private function validateInput($value)
	{
		if ($this->isRequired() && $value === '') {
			return $this->setError(JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_REQUIRED'));
		}

		$value = (int) $value;
		$min = (int) $this->params->get('min');

		if (!empty($value) && $min > 0 && $value < $min) {
			return $this->setError(JText::sprintf('PLG_FIELDS_NUMERIC_TOO_SMALL', $min));
		}

		$max = (int) $this->params->get('max');

		if ($this->params->get('max') > 0 && $value > $max) {
			return $this->setError(JText::sprintf('PLG_FIELDS_NUMERIC_TOO_BIG', $max));
		}

		return true;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  2.1.11
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		return $this->validateInput($this->value);
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  2.1.11
	 * @access public
	 */
	public function onGetValue($user)
	{
		return $this->getValue();
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  2.1.11
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!$this->config->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		return !empty($this->value);
	}


	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get the value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : JText::_($this->params->get('default'), true);

		// Set value
		$this->set('value', $this->escape($value));

		// Set errors
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Executes before a user's registration is saved.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post, &$user)
	{
		// use isset instead of !empty because we do not even wan empty string or false value here
		if ($this->params->get('readonly') && isset($post[$this->inputName])) {
			unset($post[$this->inputName]);
		}

		return true;
	}

	/**
	 * Validates the field input for user when they register their account.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($value);
	}

	/**
	 * Validate mini registration
	 *
	 * @since   2.1.11
	 * @access  public
	 */
	public function onRegisterMiniValidate(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// Only validate the field if it set the field to be visible in mini registration
		if ($this->params->get('visible_mini_registration')) {
			return $this->validateInput($value);
		}
	}
}
