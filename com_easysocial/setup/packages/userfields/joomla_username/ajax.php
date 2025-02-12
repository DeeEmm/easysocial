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
ES::import('fields:/user/joomla_username/helper');

class SocialFieldsUserJoomla_Username extends SocialFieldItem
{
	/**
	 * Validates the username.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function isValid()
	{
		$userid = $this->input->get('userid', 0, 'int');
		$event = $this->input->get('event', '', 'string');
		$current = '';

		if (!empty($userid)) {
			$user = ES::user($userid);
			$current = $user->username;
		}

		$username = $this->input->get('username', '', 'default');

		// Ensure the username doesn't contain start/end of the string with a space, since Joomla side not allow this but only allow a space between the username
		if (trim($username) !== $username) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_JOOMLA_USERNAME_NO_SPACE_AT_BEGINNING_OR_END'));
		}

		// Username is required, check if username is empty
		if (ESJString::strlen($username) < $this->params->get('min')) {
			return $this->ajax->reject(JText::sprintf('PLG_FIELDS_JOOMLA_USERNAME_MIN_CHARACTERS', $this->params->get('min')));
		}

		// Test if username is allowed (by pass for adminedit).
		if ($event !== 'onAdminEdit' && !SocialFieldsUserJoomlaUsernameHelper::allowed($username, $this->params, $current)) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_JOOMLA_USERNAME_NOT_ALLOWED'));
		}

		// Test if username exists.
		if (SocialFieldsUserJoomlaUsernameHelper::exists($username, $current)) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_JOOMLA_USERNAME_NOT_AVAILABLE'));
		}

		// Test if the username is valid
		if (!SocialFieldsUserJoomlaUsernameHelper::isValid($username, $this->params)) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_JOOMLA_USERNAME_IS_INVALID'));
		}

		$text = JText::_('PLG_FIELDS_JOOMLA_USERNAME_AVAILABLE');

		return $this->ajax->resolve($text);
	}

}
