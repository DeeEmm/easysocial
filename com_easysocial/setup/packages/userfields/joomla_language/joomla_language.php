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

require_once(__DIR__ . '/helper.php');

class SocialFieldsUserJoomla_language extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$showSubname = $this->params->get('show_subname');
		$required = $this->isRequired();

		// Get available languages
		$languages = SocialLanguageHelper::getLanguages('', $showSubname);

		// Check for errors.
		$error = $registration->getErrors($this->inputName);

		$this->set('value', $value);
		$this->set('languages', $languages);
		$this->set('error', $error);
		$this->set('required', $required);

		// Output the registration template.
		return $this->display();
	}

	/**
	 * Save trigger which is called before really saving the object.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$user)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		$user->setParam('language', $value);

		// Remove this from the index
		unset($post[$this->inputName]);

		return true;
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the params
		$value = $user->getParam('language', '');

		// dump('joomla language', $value);
		$required = $this->isRequired();

		if (!empty($post[$this->inputName])) {
			$value = $post[$this->inputName];
		}

		// Check for errors.
		$error = $this->getError($errors);

		$showSubname = $this->params->get('show_subname');

		// Get available languages
		$languages = SocialLanguageHelper::getLanguages('', $showSubname);

		$this->set('value', $value);
		$this->set('languages', $languages);
		$this->set('error', $error);
		$this->set('required', $required);

		// Output the edit template.
		return $this->display();
	}

	/**
	 * Save trigger which is called before really saving the object.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		$value = isset($post[$this->inputName]) ? $post[$this->inputName] : '';

		$user->setParam('language', $value);

		// Remove this from the index
		unset($post[$this->inputName]);

		return true;
	}

	/**
	 * Validate the field during edit
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
	 * Validate the field during registration
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
	 * Validate the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function validate($value)
	{
		// Catch for errors if this is a required field.
		if ($this->isRequired() && empty($value)) {
			$this->setError(JText::_('PLG_FIELDS_USER_JOOMLA_LANGUAGE_REQUIRE_MESSAGE'));

			return false;
		}

		return true;
	}
}
