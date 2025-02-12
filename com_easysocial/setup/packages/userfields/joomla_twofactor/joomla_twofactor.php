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
ES::import('fields:/user/joomla_twofactor/helper');

class SocialFieldsUserJoomla_twofactor extends SocialFieldItem
{
	public function __construct()
	{
		parent::__construct();

		// This requires the FOF framework
		// Load the Joomla! RAD layer
		if (!defined('FOF_INCLUDED') && !ES::isJoomla4()) {
			include_once JPATH_LIBRARIES . '/fof/include.php';
		}
	}

	/**
	 * Performs validation checks when edit user profile from backend
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function onAdminEditValidate(&$post, &$user)
	{
		return true;
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	private function validate(&$post, &$user)
	{
		// Skip this if the field is not set to required
		if (!$this->isRequired()) {
			return true;
		}

		// Determines if the user wants to enable two factor authentication
		$enabled = isset($post[$this->inputName]) ? $post[$this->inputName] : false;

		// Ensure that the user selects a two factor authentication method
		$method = isset($post['twofactor_method']) ? $post['twofactor_method'] : false;

		// Retrieve the current user id
		$userId = isset($post['userId']) ? $post['userId'] : '';

		if (!$enabled || !$method) {

			// reset the user otpkey and otep
			if ($userId) {
				$user = ES::user($userId);

				$user->otpKey = '';
				$user->otep = '';
				$user->save();
			}

			return $this->setError(JText::_('PLG_FIELDS_TWOFACTOR_VALIDATION_REQUIRED'));
		}

		// Determine if the user already has optKey and otep
		if ($enabled && $method) {

			// Get the user's otp configuration
			$userOtp = $user->getOtpConfig();

			// If user has already configured.
			if (($userOtp->method && ($userOtp->method == $method)) && (isset($userOtp->config) && $userOtp->config)) {
				return true;
			}
		}

		$twofactorObj = isset($post['jform']) ? $post['jform'] : false;

		if (!$twofactorObj) {
			return $this->setError(JText::_('PLG_FIELDS_TWOFACTOR_VALIDATION_REQUIRED'));
		}

		$twofactorObj = json_decode($twofactorObj);

		if ($method == 'totp' && (!isset($twofactorObj->twofactor->totp->securitycode) || (isset($twofactorObj->twofactor->totp->securitycode) && !$twofactorObj->twofactor->totp->securitycode))) {
			return $this->setError(JText::_('PLG_FIELDS_TWOFACTOR_VALIDATION_REQUIRED'));
		}

		if ($method == 'yubikey' && (!isset($twofactorObj->twofactor->yubikey->securitycode) || (isset($twofactorObj->twofactor->yubikey->securitycode) && !$twofactorObj->twofactor->yubikey->securitycode))) {
			return $this->setError(JText::_('PLG_FIELDS_TWOFACTOR_VALIDATION_REQUIRED'));
		}

		$otpConfig = new stdClass();

		// Trigger Joomla's twofactorauth plugin to process the configuration since we do not want to handle those encryption stuffs.
		SocialTwoFactorHelper::import2FaPlugin('twofactorauth');
		$otpConfigReplies = SocialTwoFactorHelper::trigger2FaPlugin('onUserTwofactorApplyConfiguration', array($method));

		// Look for a valid reply
		foreach ($otpConfigReplies as $reply) {

			if (!is_object($reply) || empty($reply->method) || ($reply->method != $method)) {
				continue;
			}

			$otpConfig->method = $reply->method;
			$otpConfig->config = $reply->config;

			break;
		}

		// Validate whether user enter the security code is match or not
		if (!isset($otpConfig->method) || !isset($otpConfig->config)) {
			return $this->setError(JText::_('PLG_FIELDS_TWOFACTOR_VALIDATION_FAILED'));
		}

		return true;
	}

	/**
	 * Validates the field input for user when they register their account.
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		return $this->validate($post, $user);
	}

	/**
	 * When a user saves their profile, we need to set the two factor data
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onEditBeforeSave(&$data, SocialUser &$user)
	{
		// This feature is only available if the totp plugins are enabled
		if (!SocialTwoFactorHelper::isEnabled()) {
			return;
		}

		// Determines if the user wants to enable two factor authentication
		$enabled = isset($data[$this->inputName]) ? $data[$this->inputName] : false;

		// Ensure that the user selects a two factor authentication method
		$method = isset($data['twofactor_method']) ? $data['twofactor_method'] : false;

		// If the method is not totp, we don't wan't to do anything
		if ($method != 'totp' || !$enabled) {

			// We also want to make sure the user's OTP and OTEP is cleared
			$user->otpKey = '';
			$user->otep   = '';

			return;
		}

		$twofactor = isset($data['jform']) ? $data['jform'] : false;

		if (!$twofactor) {
			return;
		}

		$twofactor = json_decode($twofactor);

		// Get the user's otp configuration
		$otpConfig = $user->getOtpConfig();

		// If user has already configured.
		if ($otpConfig->method && $otpConfig->method != 'none') {
			return;
		}

		// Trigger Joomla's twofactorauth plugin to process the configuration since we do not want to handle those encryption stuffs.
		SocialTwoFactorHelper::import2FaPlugin('twofactorauth');
		$otpConfigReplies = SocialTwoFactorHelper::trigger2FaPlugin('onUserTwofactorApplyConfiguration', array($method));

		// Look for a valid reply
		foreach ($otpConfigReplies as $reply) {

			if (!is_object($reply) || empty($reply->method) || ($reply->method != $method)) {
				continue;
			}

			$otpConfig->method = $reply->method;
			$otpConfig->config = $reply->config;

			break;
		}

		// If the method is still none, we need to disable this
		if ($otpConfig->method == 'none') {
			$data[$this->inputName] = false;
		}

		// If the method is still false, we need to ensure that twofactor is disabled
		// Generate one time emergency passwords if required (depleted or not set)
		if (empty($otpConfig->otep)) {
			$otpConfig->otep = SocialTwoFactorHelper::generateOteps($otpConfig);
		}


		// Save OTP configuration.
		$user->setOtpConfig($otpConfig);

		return true;
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// This feature is only available if the totp plugins are enabled
		if (!SocialTwoFactorHelper::isEnabled()) {
			return;
		}

		// Load com_users language file
		JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);

		// Determines if there's any errors on the form
		$error = $this->getError($errors);

		// Display the two factor form
		$methods = SocialTwoFactorHelper::getMethods($user->id);

		$this->set('methods', $methods);
		$this->set('error', $error);
		$this->set('user', $user);

		return $this->display();
	}
}
