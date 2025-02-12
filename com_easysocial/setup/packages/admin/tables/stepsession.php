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

ES::import('admin:/tables/table');

class SocialTableStepSession extends SocialTable
{
	public $session_id = null;
	public $uid = null;
	public $type = null;
	public $created = null;
	public $values = null;
	public $step = null;
	public $step_access = null;
	public $errors = null;

	public function __construct($db)
	{
		parent::__construct('#__social_step_sessions', 'session_id', $db);
	}

	/**
	 * Override parent's load implementation
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function load($key = null, $reset = true)
	{
		$state = parent::load($key, $reset);

		// @rule: We want to see which steps the user has already walked through.
		if (empty($this->step_access)) {
			$this->step_access = array();
		}

		if (!empty($this->step_access) && is_string($this->step_access)) {
			$this->step_access = explode(',', $this->step_access);
		}

		return $state;
	}

	/**
	 * Override parent's store implementation
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function store($updateNulls = false)
	{
		$db = ES::db();
		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_step_sessions');
		$query .= ' WHERE ' . $db->nameQuote('session_id') . '=' . $db->Quote($this->session_id);

		$db->setQuery($query);

		$exist = (bool) $db->loadResult();

		// @rule: Make step_access a string instead of an array
		if (is_array($this->step_access)) {
			$this->step_access  = implode(',', $this->step_access);
		}

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->type) {
			$this->type = '';
		}

		if (!$this->session_id) {
			$this->session_id = '';
		}

		if (!$this->values) {
			$this->values = '';
		}

		if (!$this->step_access) {
			$this->step_access = '';
		}

		if (!$this->errors) {
			$this->errors = '';
		}

		if (!$this->uid) {
			$this->uid = 0;
		}

		if (!$this->step) {
			$this->step = 0;
		}

		if (!$this->created) {
			$this->created = ES::date()->toSql();
		}

		// fix when key exists, it doesn't get insert to db
		if (!$exist) {
			$stored = $db->insertObject($this->_tbl, $this, $this->_tbl_key);
		} else {
			$stored = $db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}

		// error handling
		if (!$stored) {
			$this->setError($db->getError());
			return false;
		}

		// @rule: Once saving is done, convert step_access back to an array
		$this->step_access = explode(',', $this->step_access);

		return true;
	}

	/**
	 * Tests whether the current accessed step is in its list of accessed.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function hasStepAccess($step)
	{
		return in_array($step, $this->step_access);
	}

	public function removeAccess($step)
	{
		if (is_array($this->step_access)) {
			for ($i = 0; $i <= count($this->step_access); $i++) {
				$stepAccess = $this->step_access[$i];

				if ($stepAccess > $step) {
					unset($this->step_access[$i]);
				}
			}
		}
	}

	public function addStepAccess($step)
	{
		if (empty($this->step_access)) {
			$this->step_access = array();
		}

		if (!in_array($step, $this->step_access)) {
			$this->step_access[] = $step;
		}
		return true;
	}

	/**
	 * Method for caller to set errors during registration.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function setErrors($errors)
	{
		// If there's no errors, then we should reset the form.
		if (!$errors) {
			$this->set('errors', '');
			return true;
		}

		// Set the error messages.
		$this->errors = ES::makeJSON($errors);

		return true;
	}

	/**
	 * Method for caller to retrieve errors during registration.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getErrors($key = null)
	{
		// If there's no errors,
		if (!$this->errors || is_null($this->errors)) {
			return false;
		}

		// Error code is always a JSON string. Decode the error string.
		$obj = ES::makeObject($this->errors);

		// Get the vars from the object since they are stored in key/value form.
		$errors = get_object_vars($obj);

		if (!is_null($key)) {
			if (!isset($errors[$key])) {
				return false;
			}

			return $errors[$key];
		}

		return $errors;
	}

	public function getValues()
	{
		if (!$this->values || is_null($this->values)) {
			return [];
		}

		$obj = ES::json()->decode($this->values);
		$values = get_object_vars($obj);

		return $values;
	}

	public function setValue($key, $value)
	{
		$reg = ES::registry();

		if (!empty($this->values)) {
			$reg->load($this->values);
		}

		$reg->set($key, $value);

		$this->values = $reg->toString();
	}
}
