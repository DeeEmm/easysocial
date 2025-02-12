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

class SocialFieldsUserRecaptcha extends SocialFieldItem
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Determines if recaptcha has been configured
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function isCaptchaConfigured()
	{
		$params = $this->field->getApp()->getParams();
		$private = $params->get('private');
		$public = $params->get('public');

		if (!empty($private) && !empty($public)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if we should render invisible captcha
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function isInvisible()
	{
		static $invisible = null;

		if (is_null($invisible)) {
			$params = $this->field->getApp()->getParams();
			$invisible = (bool) $params->get('invisible');
		}

		return $invisible;
	}

	/**
	 * Retrieves the recaptcha library
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function getRecaptcha()
	{
		$app = $this->field->getApp();
		$params = $app->getParams();

		$options = array(
							'public' => $params->get('public'),
							'secret' => $params->get('private'),
							'theme' => $params->get('theme'),
							'language' => $params->get('language'),
							'invisible_position' => $params->get('invisible_position', 'inline')
						);

		$captcha = ES::captcha('recaptcha', $options);

		return $captcha;
	}

	/**
	 * Determines if the user has already validated with recaptcha
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasValidated(&$post)
	{
		$validated = isset($post[$this->inputName]) ? $post[$this->inputName] : false;

		return $validated;
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Check if recaptcha has been configured
		if (!$this->isCaptchaConfigured()) {
			return;
		}

		// The key for this element.
		$key = SOCIAL_FIELDS_PREFIX . $this->field->id;

		// If user has already validated, skip this
		if ($this->hasValidated($post)) {
			return;
		}

		// Check for errors
		$error = $this->getError($errors);
		$captcha = $this->getRecaptcha();
		$invisible = $this->isInvisible();

		$this->set('invisible', $invisible);
		$this->set('captcha', $captcha);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		if (!$this->isCaptchaConfigured() || $this->hasValidated($post)) {
			return;
		}

		$error = $registration->getErrors($this->inputName);
		$captcha = $this->getRecaptcha();
		$invisible = $this->isInvisible();

		$this->set('invisible', $invisible);
		$this->set('error', $error);
		$this->set('captcha', $captcha);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		return $this->validateCaptcha($post);
	}

	/**
	 * Validate mini registration
	 *
	 * @since   2.0.11
	 * @access  public
	 */
	public function onRegisterMiniValidate(&$post, &$registration)
	{
		// Only validate the field if it set the field to be visible in mini registration
		if ($this->params->get('visible_mini_registration')) {
			return $this->validateCaptcha($post);
		}
	}

	/**
	 * Performs validation when a user updates their profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		return $this->validateCaptcha($post);
	}

	/**
	 * Performs validation of captcha text
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function validateCaptcha(&$post)
	{
		// If user has already validated or this field isn't required, skip this altogether.
		if (!$this->field->isRequired() || $this->hasValidated($post)) {
			return true;
		}

		// Check if recaptcha has been configured
		if (!$this->isCaptchaConfigured()) {
			return true;
		}

		$response = $this->input->get('g-recaptcha-response');

		if (!$response) {
			return $this->setError(JText::_('PLG_FIELDS_RECAPTCHA_VALIDATION_PLEASE_ENTER_CAPTCHA_RESPONSE'));
		}

		$captcha = $this->getRecaptcha();
		$state = $captcha->checkAnswer($_SERVER['REMOTE_ADDR'], $response);

		if (!$state) {
			return $this->setError(JText::_('PLG_FIELDS_RECAPTCHA_VALIDATION_INVALID_RESPONSE'));
		}

		// Set a valid response to the registration object.
		$post[$this->inputName] = true;

		return true;
	}

	/**
	 * Checks if this field is already entered for profile completeness.
	 *
	 * @since	1.4.8
	 * @access	public
	 */
	public function onProfileCompleteCheck($user)
	{
		return true;
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	2.0.11
	 * @access	public
	 */
	public function onAdminEditValidate(&$post, &$user)
	{
		// Regardless of readonly parameter, we allow admin to edit this field
		return true;
	}
}
