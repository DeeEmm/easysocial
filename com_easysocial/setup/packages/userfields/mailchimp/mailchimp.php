<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserMailchimp extends SocialFieldItem
{
	/**
	 * Processes after a user registers on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$data, $user)
	{
		$subscribe = isset($data[$this->inputName]) ? $data[$this->inputName] : false;

		// Get the app params
		$params = $this->field->getApp()->getParams();
		$defaultList = $params->get('listid');
		$useDoubleOptIn = $params->get('double-opt-in', true);

		// Get the field params
		$fieldParams = $this->field->getParams();

		if ($subscribe && $defaultList && $params->get('apikey')) {

			// Load up mailchimp's library.
			$mailchimp = ES::mailchimp($params->get('apikey'));

			// Determine if there's a custom list id.
			$listId = $fieldParams->get('custom_list_id', $defaultList);

			// Try to get the first and last name of the user
			$firstName = isset($data['first_name']) ? $data['first_name'] : '';
			$lastName = isset($data['last_name']) ? $data['last_name'] : '';

			if (!$firstName) {
				$firstName = $user->getName();
			}

			$mailchimp->subscribe($listId, $user->email, $firstName, $lastName, $useDoubleOptIn);
		}
	}

	/**
	 * Responsible to output the form when the user is being edited by the admin
	 *
	 * @since	3.2.2
	 * @access	public
	 */
	public function onAdminEdit(&$post, &$user, $errors)
	{
		// Get the default value.
		$value = $this->params->get('default_value', false);

		// Determine for the editing existing user or create a new one
		$isNew = isset($user->id) && $user->id ? false : true;

		// Retrieve the value from the profile field data
		if (!$isNew) {
			$value = $this->value;
		}

		// If the value exists in the post data, it means that the user had previously set some values.
		if (isset($post[$this->inputName]) && !empty($post[$this->inputName])) {
			$value = $post[$this->inputName];
		}

		// Get the error.
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}


	/**
	 * Displays the field input for user when they edit their account
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$registration, $errors)
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
	 * Perform field saving after profile is saved
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onEditBeforeSave(&$data, SocialUser &$user)
	{
		$subscribe = isset($data[$this->inputName]) ? $data[$this->inputName] : false;

		// Get the app params
		$params = $this->field->getApp()->getParams();
		$defaultList = $params->get('listid');

		// Get the field params
		$fieldParams = $this->field->getParams();

		// Get the previous value
		$value = $this->getData();

		// If the previous value is selected and the current subscribe is false, we assume that the user is trying to unsubscribe
		if (!$subscribe && $value && $params->get('apikey')) {

			// Try to get the first and last name of the user
			$firstName = isset($data['first_name']) ? $data['first_name'] : '';
			$lastName = isset($data['last_name']) ? $data['last_name'] : '';

			if (!$firstName) {
				$firstName = $user->getName();
			}

			// Load up mailchimp's library.
			$mailchimp = ES::mailchimp($params->get('apikey'));

			// Determine if there's a custom list id.
			$listId = $fieldParams->get('custom_list_id', $defaultList);

			$mailchimp->unsubscribe($listId, $user->email);

			// Set the data to 0
			$data[$this->inputName]	= '';
		}
	}

	/**
	 * When saving a profile, checking for the subscribed status should be after saving the fields as we want to
	 * be able to retrieve the person's latest first and last name.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onEditAfterSaveFields(&$data, SocialUser $user)
	{
		$user->reloadFields();

		$subscribe = isset($data[$this->inputName]) ? $data[$this->inputName] : false;
		$name = $user->getFieldValue('JOOMLA_FULLNAME');

		// Get the app params
		$params = $this->field->getApp()->getParams();
		$defaultList = $params->get('listid');
		$useDoubleOptIn = $params->get('double-opt-in', true);

		// Get the field params
		$fieldParams = $this->field->getParams();

		if (is_object($name)) {
			$firstName = $name->first;
			$lastName = !empty($name->last) ? $name->last : $name->middle;
		} else {

			$name = explode(' ', $name);
			$firstName = $name[0];
			$lastName = isset($name[1]) ? $name[1] : '';
		}

		// Get the previous stored data
		if ($subscribe && $defaultList && $params->get('apikey')) {

			// Load up mailchimp's library.
			$mailchimp = ES::mailchimp($params->get('apikey'));

			// Determine if there's a custom list id.
			$listId = $fieldParams->get('custom_list_id', $defaultList);

			$mailchimp->subscribe($listId, $user->email, $firstName, $lastName, $useDoubleOptIn);
		}
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get the default value.
		$value = $this->params->get('default_value', false);

		// If the value exists in the post data, it means that the user had previously set some values.
		if (isset($post[$this->inputName]) && !empty($post[$this->inputName])) {
			$value = $post[$this->inputName];
		}

		// Detect if there's any errors.
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
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

		return !empty($this->value);
	}
}
