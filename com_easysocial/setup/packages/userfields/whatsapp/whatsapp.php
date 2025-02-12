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

class SocialFieldsUserWhatsapp extends SocialFieldItem
{
	public function __construct($options)
	{
		parent::__construct($options);
	}

	/**
	 * Displays the field input for user when they edit their account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onEdit(&$post, &$registration, $errors)
	{
		$value = ES::normalize($post, $this->inputName, $this->value);
		$value = $this->escape($value);
		$error = $this->getError($errors);

		$this->set('value', $value);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Renders the field on the users profile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function onDisplay($user)
	{
		$value 	= $this->value;

		if (!$value) {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$value = $this->escape($value);

		$this->set('params', $this->params);
		$this->set('user', $user);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		$value = ES::normalize($post, $this->inputName, '');
		$error = $registration->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Profile completeness checking
	 *
	 * @since  3.3.0
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
