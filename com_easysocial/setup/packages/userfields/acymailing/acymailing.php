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

class SocialFieldsUserAcymailing extends SocialFieldItem
{
	public function __construct()
	{
		parent::__construct();

		$this->helper = new SocialAcyMailingHelper();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		$lists = $this->params->get('list_ids');
		$isEnabled = $this->helper->isEnabled();

		if (!$lists || !$isEnabled) {
			return;
		}

		// Get the default value.
		$value = '';

		// If the value exists in the post data, it means that the user had previously set some values.
		if (isset($post[$this->inputName]) && !empty($post[$this->inputName])) {
			$value = $post[$this->inputName];
		}

		// Detect if there's any errors.
		$error = $registration->getErrors($this->inputName);
		$optionType = $this->params->get('option_type', 'checkbox');

		$this->set('optionType', $optionType);
		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Processes after a user registers on the site
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onRegisterAfterSave(&$data, $user)
	{
		$subscribe = isset($data[$this->inputName]) ? $data[$this->inputName] : false;

		if (!$subscribe) {
			return;
		}

		// Determine which lists should be available for the user to subscribe to
		$lists = $this->params->get('list_ids');
		$isEnabled = $this->helper->isEnabled();

		if (!$lists || !$isEnabled) {
			return;
		}

		// Get the list ids
		$listIds = explode(',', $lists);

		// Add the user to the list
		$this->helper->subscribe($listIds, $user);
	}

	/**
	 * Ensures that the field is validated before saving.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onRegisterValidate(&$post, SocialTableRegistration $registration)
	{
		$validate = $this->validate($post);

		return $validate;
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
			return $this->validate($post);
		}
	}

	private function validate(&$post)
	{
		$isEnabled = $this->helper->isEnabled();

		// Skip this if the site don't have acymailing extension enabled
		if (!$isEnabled) {
			return;
		}

		$selected = isset($post[$this->inputName]) && $post[$this->inputName] ? true : false;

		$optionType = $this->params->get('option_type', 'checkbox');

		// if that is toggle type
		if ($optionType == 'toggler') {
			$selected = isset($post[$this->inputName]) && ($post[$this->inputName] == 1 || $post[$this->inputName] == 0) ? true : false;
		}

		$required = $this->isRequired();

		if ($required && !$selected) {
			$this->setError($optionType == 'toggler' ? JText::_('PLG_FIELDS_USER_ACYMAILING_IS_REQUIRED_TOGGLER') : JText::_('PLG_FIELDS_USER_ACYMAILING_IS_REQUIRED'));
			return false;
		}

		return true;
	}

	/**
	 * Validates the field when the user edits their profile
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		return $this->validate($post);
	}

	/**
	 * Displays the field input for user when they edit their account
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName ] : $this->value;

		// Get the error.
		$error = $this->getError($errors);

		// Determine which lists should be available for the user to subscribe to
		$lists = $this->params->get('list_ids');
		$isEnabled = $this->helper->isEnabled();

		if (!$lists || !$isEnabled) {
			return;
		}

		// Get the list ids
		// Just in case if the user want to subscribe multiple list ids
		$listIds = explode(',', $lists);
		$subscribed = '';

		// Load Acymailing user subscriber library
		// Get the subscription id for this particular user
		// AcyMailing6 return an object for this user
		// AcyMailing below version 6 return user id
		$subscriber = $this->helper->getParticularSubscriber($user);

		// Skip it if this current logged in user doesn't have subscribe it
		if ($subscriber) {
			// We need to check with acymailing if the user is already subscribed previously.
			$subscribed = $this->helper->isSubscribed($listIds, $user);
		}

		if ($subscribed) {
			$value = 1;
		} else {
			$value = 0;
		}

		$value = (int) $this->escape($value);
		$optionType = $this->params->get('option_type', 'checkbox');

		$this->set('optionType', $optionType);
		$this->set('value', $value);
		$this->set('error', $error);

		return $this->display();
	}

	public function onAdminEdit(&$post, &$user, $errors)
	{
		// Get the value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName ] : $this->value;

		// Get the error.
		$error = $this->getError($errors);

		// Determine which lists should be available for the user to subscribe to
		$lists = $this->params->get('list_ids');
		$isEnabled = $this->helper->isEnabled();

		if (!$lists || !$isEnabled) {
			return;
		}

		// Get the list ids
		// Just in case if the user want to subscribe multiple list ids
		$listIds = explode(',', $lists);
		$subscribed = '';

		// Load Acymailing user subscriber library
		// Get the subscription id for this particular user
		// AcyMailing6 return an object for this user
		// AcyMailing below version 6 return user id
		$subscriber = $this->helper->getParticularSubscriber($user);

		// Skip it if this current logged in user doesn't have subscribe it
		if ($subscriber) {
			// We need to check with acymailing if the user is already subscribed previously.
			$subscribed = $this->helper->isSubscribed($listIds, $user);
		}

		if ($subscribed) {
			$value = 1;
		} else {
			$value = 0;
		}

		$value = (int) $this->escape($value);
		$optionType = $this->params->get('option_type', 'checkbox');

		$this->set('optionType', $optionType);
		$this->set('value', $value);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Perform field saving after profile is saved
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function onEditAfterSave(&$data, SocialUser &$user)
	{
		$subscribe = isset($data[$this->inputName]) ? $data[$this->inputName] : false;

		// Get the list ids
		$listIds = $this->params->get('list_ids');
		$listIds = explode(',', $listIds);

		if (!$subscribe) {

			// Unsubscribe the user
			$this->helper->unsubscribe($listIds, $user);
			return;
		}

		// Add the user to the list
		$this->helper->subscribe($listIds, $user);
	}
}
