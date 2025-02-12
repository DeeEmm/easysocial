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
ES::import('fields:/user/permalink/helper');

class SocialFieldsUserPermalink extends SocialFieldItem
{
	/**
	 * Saves the permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save($post , $user)
	{
		$value = isset($post[$this->inputName]) && !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$table = ES::table('Users');
		$table->load(array('user_id' => $user->id));

		// Ensure that the permalink value goes through the string filters
		$value = JFilterOutput::stringURLSafe($value);

		$table->permalink = $value;
		$table->store();
	}

	/**
	 * Once the registration is stored, we need to update the user's `permalink` column
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$post , $user)
	{
		return $this->save($post , $user);
	}

	/**
	 * Saves the permalink after their profile is edited.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditAfterSave(&$post , $user)
	{
		return $this->save($post , $user);
	}

	/**
	 * Performs validation for the gender field.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function validate($value , $user = null)
	{
		if (!$this->isRequired() && empty($value)) {
			return true;
		}

		// Catch for errors if this is a required field.
		if ($this->isRequired() && empty($value)) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_REQUIRED'));
			return false;
		}

		if ($this->params->get('max') > 0 && ESJString::strlen($value) > $this->params->get('max')) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_EXCEEDED_MAX_LENGTH'));
			return false;
		}

		if (!SocialFieldsUserPermalinkHelper::allowed($value)) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_CONFLICTS_WITH_SYSTEM'));
			return false;
		}

		// Determine the current user that is being edited
		$current = '';
		$currentUserId = '';

		if (!empty($user)) {
			$current = $user->permalink;
			$currentUserId = $user->id;
		}

		if (SocialFieldsUserPermalinkHelper::exists($value, $current, $currentUserId)) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_NOT_AVAILABLE'));
			return false;
		}

		if (!SocialFieldsUserPermalinkHelper::valid($value, $this->params)) {
			$this->setError(JText::_('PLG_FIELDS_PERMALINK_INVALID_PERMALINK'));
			return false;
		}

		return true;
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$state 	= $this->validate($value);

		return $state;
	}

	/**
	 * Performs validation when a user updates their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post , $user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$state 	= $this->validate($value, $user);

		return $state;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// Detect if there's any errors.
		$error 	= $registration->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $this->escape($value));

		$this->set('userid', null);

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
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $user->permalink;

		$error = $this->getError($errors);

		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		$this->set('userid', $user->id);

		return $this->display();
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		return $this->validate($this->value, $user);
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

		if (empty($user->alias) && empty($user->permalink)) {
			return false;
		}

		return true;
	}
}
