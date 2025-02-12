<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserJoomla_password extends SocialFieldItem
{
	protected $password	= null;

	/**
	 * Determines if the original password is required when changing passwords
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function requireOriginalPassword()
	{
		$required = (bool) $this->params->get('require_original_password');

		if ($this->my->isAssociated()) {
			$required = false;
		}

		return $required;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post , &$registration)
	{
		$input = !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';

		$this->set('input', $input);

		// Check for errors
		$error = $registration->getErrors($this->inputName);

		// Set errors.
		$this->set('error', $error);

		$this->set('showOriginalPassword', false);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post)
	{
		$input	 	= !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';
		$reconfirm	= !empty($post[$this->inputName . '-reconfirm']) ? $post[$this->inputName . '-reconfirm'] : '';

		// Check if reconfirm passwords is disabled.
		if (!$this->params->get('reconfirm_password'))
		{
			$reconfirm = $input;
		}

		return $this->validatePassword($input, $reconfirm);
	}

	/**
	 * Validate mini registrations
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegisterMiniValidate(&$post)
	{
		$input = !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';
		$reconfirm	= !empty($post[$this->inputName . '-reconfirm']) ? $post[$this->inputName . '-reconfirm'] : '';

		// Check if reconfirm passwords is disabled.
		if (!$this->params->get('mini_reconfirm_password')) {
			$reconfirm = $input;
		}

		return $this->validatePassword($input, $reconfirm);
	}

	/**
	 * Executes before a user's registration is saved.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post, &$user)
	{
		// We do not need to validate against reconfirm here
		$input	 	= !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';

		// The user->bind function expects the post to have a password key
		$post['password'] = $input;

		// Remove the data from $post to prevent passwords saving in fields table
		unset($post[$this->inputName . '-input']);
		unset($post[$this->inputName . '-reconfirm']);

		return true;
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$error = $this->getError($errors);
		$showOriginalPassword = $this->requireOriginalPassword();

		$this->set('error', $error);
		$this->set('showOriginalPassword', $showOriginalPassword);

		return $this->display();
	}

	/**
	 * Displays the field input for user when admin edit their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAdminEdit(&$post, &$user, $errors)
	{
		// Get errors.
		$error = $this->getError($errors);

		// Set errors.
		$this->set('error', $error);

		$this->set('showOriginalPassword', false);

		return $this->display();
	}

	/**
	 * Executes before a user's registration is saved.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAdminEditValidate(&$post, &$user)
	{
		// Get the input
		$input	 	= !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';
		$reconfirm	= !empty($post[$this->inputName . '-reconfirm']) ? $post[$this->inputName . '-reconfirm'] : '';

		// Check if reconfirm passwords is disabled.
		if (!$this->params->get('reconfirm_password'))
		{
			$reconfirm = $input;
		}

		// Check if user is registered user or new user
		$newUser = empty($user->id);

		if ($newUser || !(empty($input) && empty($reconfirm)))
		{
			return $this->validatePassword($input, $reconfirm);
		}

		return true;
	}

	/**
	 * Validates the password when the user edits their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$original	= !empty($post[$this->inputName . '-orig']) ? $post[$this->inputName . '-orig'] : '';
		$input	 	= !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';
		$reconfirm	= !empty($post[$this->inputName . '-reconfirm']) ? $post[$this->inputName . '-reconfirm'] : '';

		$requireOriginalPassword = $this->requireOriginalPassword();

		if (!empty($input) && $requireOriginalPassword) {
			
			if (empty($original)) {
				$this->setError(JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_ORIGINAL_PASSWORD'));
				return false;
			}

			if (!$this->checkOriginalPassword($original, $user)) {
				return false;
			}
		}

		// Check if reconfirm passwords is disabled.
		if (!$this->params->get('reconfirm_password')) {
			$reconfirm = $input;
		}

		if ((!empty($reconfirm) || !empty($input)) && !$this->validatePassword($input, $reconfirm)) {
			return false;
		}

		return true;
	}

	/**
	 * Executes before a user's edit is saved.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		// We do not need to validate against reconfirm here
		$input	 	= !empty($post[$this->inputName . '-input']) ? $post[$this->inputName . '-input'] : '';

		// If input is empty then don't change the password
		if (!empty($input))
		{
			// The user->bind function expects the post to have a password key
			// For changing password, Joomla expects both key to be the same in order for password to change properly
			$post['password'] = $input;
			$post['password2'] = $input;
		} else {
			// If not input, then we set the password as empty to prevent Joomla from changing the password
			$post['password'] = '';
			$post['password2'] = '';
		}

		// Remove the data from $post to prevent passwords saving in fields table
		unset($post[$this->inputName . '-input']);
		unset($post[$this->inputName . '-reconfirm']);

		return true;
	}

	/**
	 * Performs password validation
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function validatePassword($input, $reconfirm)
	{
		// Verify that the passwords are valid and not empty
		if (empty($input) || empty($reconfirm)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_PASSWORD'));

			return false;
		}

		if ($this->params->get('min') > 0 && strlen($input) < $this->params->get('min')) {
			$this->setError(JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_CHAR', $this->params->get('min')));

			return false;
		}

		if ($this->params->get('max') > 0 && strlen($input) > $this->params->get('max')) {
			$this->setError(JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MAXIMUM_CHAR', $this->params->get('max')));

			return false;
		}

		// Verify minimum symbols
		$minSymbols = (int) $this->params->get('min_symbols');

		if ($minSymbols > 0) {

			// Get the total number of symbols used in the password
			$totalSymbols = preg_match_all('[\W]', $input, $matches);

			if ($totalSymbols < $minSymbols) {
				return $this->setError(JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_SYMBOLS', $minSymbols));
			}
		}

		// Verify minimum integers
		$minInteger = $this->params->get('min_integer');
		if ($minInteger > 0) {
			$totalIntegers = preg_match_all('/[0-9]/', $input, $matches);

			if ($totalIntegers < $minInteger) {
				return $this->setError(JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_INTEGER', $minInteger));
			}
		}

		// Verify minimum uppercase
		$minUppercase = $this->params->get('min_uppercase');

		if ($minUppercase > 0) {
			$totalUppercase = preg_match_all('/[A-Z]/', $input, $matches);

			if ($totalUppercase < $minUppercase) {
				return $this->setError(JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_UPPERCASE', $minUppercase));
			}
		}

		if ($input !== $reconfirm) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_PASSWORD_NOT_MATCHING'));

			return false;
		}

		return true;
	}

	private function checkOriginalPassword($password, $user)
	{
		$state = $user->verifyUserPassword($password);

		if (!$state)
		{
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_PASSWORD_WRONG_PASSWORD'));
		}

		return $state;
	}
}