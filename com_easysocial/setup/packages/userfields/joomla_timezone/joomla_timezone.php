<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserJoomla_timezone extends SocialFieldItem
{
	/**
	 * The list of available timezone groups to use.
	 * @var Array
	 */
	private $timezones = array(
		'Africa' 	=> null,
		'America' 	=> null,
		'Antartica' => null,
		'Arctic' 	=> null,
		'Asia' 		=> null,
		'Atlantic'	=> null,
		'Australia' => null,
		'Europe' 	=> null,
		'Indian' 	=> null,
		'Pacific' 	=> null
	);

	/**
	 * Stores the state of the current group when filtering arrays.
	 * @var string
	 */
	static $tmpState = null;

	public function __construct()
	{
		// Initialize our timezones.
		$this->initTimeZones();

		parent::__construct();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->params->get('default');

		// Set value.
		$this->set('value', $value);

		// Check for errors.
		$error = $registration->getErrors($this->inputName);

		// Set errors.
		$this->set('error', $error);

		// Set the timezones for the template.
		$this->set('timezones', $this->timezones);

		// Output the registration template.
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
		// Selected value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : null;

		// If this is required, check for the value.
		if ($this->isRequired() && empty($value)) {
			return $this->setError(JText::_('PLG_FIELDS_JOOMLA_TIMEZONE_VALIDATION_SELECT_TIMEZONE'));
		}

		return true;
	}

	/**
	 * Save trigger which is called before really saving the object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : null;

		// UTC timezone is only meant for server. 
		if ($value === 'UTC') {
			// reset the value
			$value = '';
		}

		$user->setParam('timezone', $value);

		unset($post[$this->inputName]);

		return true;
	}

	/**
	 * Displays the field input for user on edit page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Get error.
		$error = $this->getError($errors);

		// Set error.
		$this->set('error', $error);

		// Get value.
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $user->getParam('timezone', $this->params->get('default'));

		// Set value.
		$this->set('value', $value);

		$this->set('timezones', $this->timezones);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the edit form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		// Selected value
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// If this is required, check for the value.
		if ($this->isRequired() && empty($value)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_TIMEZONE_VALIDATION_SELECT_TIMEZONE'));
			return false;
		}

		return true;
	}

	/**
	 * Save trigger which is called before really saving the object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// UTC timezone is only meant for server. 
		if ($value === 'UTC') {
			// reset the value
			$value = '';
		}

		$user->setParam('timezone', $value);

		unset($post[$this->inputName]);

		return true;
	}

	/**
	 * Initializes timezones.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initTimeZones()
	{
		// Get available time zones.
		$zones = DateTimeZone::listIdentifiers();

		foreach ($this->timezones as $group => &$val) {
			// Set the temporary state
			self::$tmpState = $group;

			// Perform filtering of the current group
			$match = array_filter($zones, array($this, 'filterByGroup'));

			$val = $match;
		}
	}

	/**
	 * Performs array filtering of the timezone.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function filterByGroup($var)
	{
		if (stristr($var, self::$tmpState) === false) {
			return false;
		}

		return true;
	}

	public function onDisplay($user)
	{
		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$timezone = $user->getParam('timezone');

		if (empty($timezone)) {
			return;
		}

		$this->set('value', $timezone);

		return $this->display();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$timezone = $user->getParam('timezone');

		$field = $this->field;

		if (empty($timezone)) {
			return '';
		}

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $timezone;

		return $data;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		$timezone = $user->getParam('timezone');

		if ($this->isRequired() && empty($timezone)) {
			$this->setError(JText::_('PLG_FIELDS_JOOMLA_TIMEZONE_VALIDATION_SELECT_TIMEZONE'));
			return false;
		}

		return true;
	}
}
