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
ES::import('fields:/user/joomla_username/helper');

class SocialFieldsUserJoomla_username extends SocialFieldItem
{
	/**
	 * Determines if the user is allowed to edit this field
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function canEdit(SocialUser $user)
	{
		$canEdit = $this->params->get('allow_edit_change', false);

		// Check if the user can really edit this
		$editLimitDays = (int) $this->params->get('max_change_days', 0);

		if ($editLimitDays) {
			$userParams = $user->getEsParams();

			// Only check if we truly have the date of their last change
			$lastChanged = $userParams->get('username_changed');

			if ($lastChanged) {
				$editLimitDaysString = $editLimitDays . ' days';
				$compute = strtotime($editLimitDaysString, strtotime($lastChanged));

				// If the computed time is higher than the current time
				// we should not allow the user to edit their username
				if ($compute > strtotime('now')) {
					$canEdit = false;
				}
			}
		}

		return $canEdit;
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onRegister(&$post, &$registration)
	{
		$config = ES::config();

		// If settings is set to use email as username, then we hide username field
		if ($config->get('registrations.emailasusername')) {
			return false;
		}

		// Try to check to see if user has already set the username.
		$username = isset($post['username']) ? $post['username'] : '';

		// Check for errors
		$error = $registration->getErrors($this->inputName);

		// Set errors.
		$this->set('error', $error);

		// Set the username property for the theme.
		$this->set('username', $this->escape($username));

		$this->set('userid', null);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onRegisterValidate(&$post)
	{
		$username = !empty($post['username']) ? $post['username'] : '';

		return $this->validateUsername($username);
	}

	public function onRegisterBeforeSave(&$post)
	{
		if (!empty($post['username']) && empty($post['first_name']) && empty($post['name'])) {
			// Assign directly to name because Joomla is reading name instead
			// We also check for first_name because first_name is unique to EasySocial and this is to ensure that the field is either empty or not loaded

			$post['name'] = $post['username'];
		}
	}

	/**
	 * Processes after a user registers on the site
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onRegisterAfterSave(&$data, $user)
	{
		$config = ES::config();

		if ($config->get('users.aliasName') != 'username') {
			return;
		}

		// only if the alias is empty as the alias might be created already from user plugin.
		// #909
		if (!$user->alias) {
			$this->saveAlias($data, $user);
		}
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$config = ES::config();

		// If settings is set to use email as username, then we hide username field
		if ($config->get('registrations.emailasusername')) {
			return false;
		}

		$error = $this->getError($errors);

		$canEdit = $this->canEdit($user);

		$this->set('username', $this->escape($user->username));
		$this->set('error', $error);
		$this->set('userid', $user->id);
		$this->set('canEdit', $canEdit);

		return $this->display();
	}

	/**
	 * Validate the username field
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$canEdit = $this->canEdit($user);

		if (!$this->params->get('allow_edit_change', false) || !$canEdit) {
			return true;
		}

		$username = !empty($post['username']) ? $post['username'] : '';

		return $this->validateUsername($username, $user->username);
	}

	public function onEditBeforeSave(&$post, $user)
	{
		$canEdit = $this->canEdit($user);

		if (!$canEdit) {
			return true;
		}

		$post['usernameChanged'] = $this->usernameChanged($post, $user);
	}

	/**
	 * Triggered after the username is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onEditAfterSave(&$data, $user)
	{
		$canEdit = $this->canEdit($user);

		if (!$this->params->get('allow_edit_change', false) || !$canEdit) {
			return true;
		}

		$config = ES::config();

		// Only proceed when the name has been changed.
		if (isset($data['usernameChanged']) && !$data['usernameChanged']) {
			return;
		}

		// Add timestamp so that we know when the user last changed their username
		$this->saveChangeTimestamp($user);

		// If the user is not using username as an alias, we do not need to update the alias
		if ($config->get('users.aliasName') != 'username') {
			return;
		}

		$this->saveAlias($data, $user);
	}

	public function onAdminEdit(&$post, &$user, $errors)
	{
		$config = ES::config();

		// If settings is set to use email as username, then we hide username field
		if ($config->get('registrations.emailasusername')) {
			return false;
		}

		$error = $this->getError($errors);
		$username = !empty($user->username) ? $user->username : '';

		$this->set('username', $username);
		$this->set('error', $error);
		$this->set('userid', $user->id);

		return $this->display();
	}

	/**
	 * Triggers before a user is saved
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$post, $user)
	{
		// Detect if the name is changed.
		$post['usernameChanged'] = $this->usernameChanged($post, $user);
	}

	/**
	 * Triggers after a user is saved by the admin
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAdminEditAfterSave(&$data, $user)
	{
		$config = ES::config();

		if ($config->get('users.aliasName') != 'username') {
			return;
		}

		// Only proceed when the name has been changed.
		if (isset($data['usernameChanged']) && !$data['usernameChanged']) {
			return;
		}

		$this->saveAlias($data, $user);
	}

	public function onDisplay($user)
	{
		if (ES::config()->get('registrations.emailasusername')) {
			return;
		}

		$this->set('username', $this->escape($user->username));

		return $this->display();
	}

	/**
	 * Determines if the name has changed
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function usernameChanged($post, $user)
	{
		// Detect if the name has changed
		$username = isset($post['username']) ? $post['username'] : '';

		if ($username != $user->username) {
			return true;
		}

		return false;
	}

	/**
	 * Responsible to save the alias of the user.
	 *
	 * @since   2.0.11
	 * @access  public
	 */
	public function saveAlias(&$data, &$user)
	{
		// Get the username
		$username = isset($data['username']) ? $data['username'] : '';

		// Filter the username so that it becomes a valid alias
		$alias = JFilterOutput::stringURLSafe($username);

		if ($this->config->get('registrations.emailasusername') || JMailHelper::isEmailAddress($username)) {
			// if admin configured to use email as username, or user enter their email as username, due to security concern, we will use fullname as alias.
			$alias = JFilterOutput::stringURLSafe($user->name);
		}

		$model = ES::model('Users');
		$user->alias = $model->generateAlias($alias, $user->id);
		$user->setUserParams('username_changed', JFactory::getDate()->toSql());
		$user->save();
	}

	/**
	 * Save the last date the username was changed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function saveChangeTimestamp(&$user)
	{
		$user->setUserParams('username_changed', JFactory::getDate()->toSql());
		$user->save();
	}

	public function validateUsername($username, $current = '')
	{
		$config = ES::config();

		// If settings is set to use email as username, then we bypass this check
		if ($config->get('registrations.emailasusername')) {
			return true;
		}

		// Test the username length
		if (ESJString::strlen($username) < $this->params->get('min')) {
			return $this->setError(JText::sprintf('PLG_FIELDS_JOOMLA_USERNAME_MIN_CHARACTERS', $this->params->get('min')));
		}

		// Test if the username is allowed
		if (!SocialFieldsUserJoomlaUsernameHelper::allowed($username, $this->params, $current)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_USERNAME_NOT_ALLOWED'));

			return false;
		}

		// Test if the username provided is valid.
		if (!SocialFieldsUserJoomlaUsernameHelper::isValid($username, $this->params)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_USERNAME_PLEASE_ENTER_VALID_USERNAME'));

			return false;
		}

		// Test if the username is available.
		if (SocialFieldsUserJoomlaUsernameHelper::exists($username, $current)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_USERNAME_NOT_AVAILABLE'));

			return false;
		}

		return true;
	}

	public function onOAuthGetMetaFields(&$fields)
	{
		$fields[] = ES::config()->get('oauth.facebook.username', 'email');
	}

	public function onOAuthGetUserMeta(&$details, &$client)
	{
		if ($client->getType() != 'facebook') {
			return;
		}

		$key = ES::config()->get('oauth.facebook.username', 'email');

		if (isset($details[$key])) {
			$details['username'] = $details[$key];
		}
	}

	public function onRegisterOAuthBeforeSave(&$post, &$client)
	{
		$type = $client->getType();

		if (empty($post['username'])) {

			$username = $post['email'];

			if ($type == 'facebook') {
				$username = $post[ES::config()->get('oauth.facebook.username', 'email')];
			}

			if ($type == 'twitter' && isset($post['screen_name'])) {
				$username = $post['screen_name'];
			}

			$post['username'] = $username;
		}
	}

	public function onRegisterOAuthAfterSave(&$data, &$client, &$user)
	{
		if ($this->config->get('users.aliasName') != 'username') {
			return;
		}

		$this->saveAlias($data, $user);
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  3.2
	 * @access public
	 */
	public function onGetValue($user)
	{
		$container = $this->getValueContainer();

		$username = $user->username;

		$container->raw = $username;
		$container->data = $username;
		$container->value = $username;

		return $container;
	}
}
