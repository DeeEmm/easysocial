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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

ES::import( 'admin:/tables/table' );

/**
 * Object mapping for registrations table.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableRegistration extends SocialTable
{
	/**
	 * The unique session id.
	 * @var string
	 */
	public $session_id = null;

	/**
	 * The profile type's unique id.
	 * @var int
	 */
	public $profile_id = null;

	/**
	 * The created date time.
	 * @var datetime
	 */
	public $created = null;

	/**
	 * A pre-stored values the user has entered during registration.
	 * @var string
	 */
	public $values = '';

	/**
	 * The current step the user is on.
	 * @var int
	 */
	public $step = null;

	/**
	 * Comma separated values to determine which step the user has access to.
	 * @var string
	 */
	public $step_access = null;

	/**
	 * Stores any errors.
	 * @var string
	 */
	public $errors = '';

	public function __construct( $db )
	{
		parent::__construct('#__social_registrations', 'session_id', $db);
	}

	/**
	 * Override parent's load implementation
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique row id.
	 * @param   bool	True to reset the default values before loading the new row.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function load($key = null, $reset = true)
	{
		if (!$key) {
			$session = JFactory::getSession();
			$key = $session->getId();
		}

		$state = parent::load($key, $reset);

		// If state is false, then we create the session here
		if (!$state) {
			$session = JFactory::getSession();
			$newKey = $session->getId();

			// Ensure that the key really exists
			if ($key != $newKey) {
				$state = parent::load($newKey, $reset);
			}

			if (!$state) {
				$this->session_id = $newKey;
				$this->created = ES::date()->toSql();
				$this->profile_id = 0;
				$this->values = '';
				$this->step = 0;
				$this->step_access = '';
				$this->errors = '';

				$state = $this->store();
			}
		}

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
	 * @since	1.0
	 * @access	public
	 * @param	bool	True to update fields even if they are null.
	 * @param   bool	True to reset the default values before loading the new row.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function store($updateNulls = false)
	{
		$db = ES::db();
		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_registrations') . ' '
				. 'WHERE ' . $db->nameQuote('session_id') . '=' . $db->Quote($this->session_id);

		$db->setQuery($query);

		$exist = (bool) $db->loadResult();

		// @rule: Make step_access a string instead of an array
		if (is_array($this->step_access)) {
		    $this->step_access = implode(',', $this->step_access);
		}

		// If created is not set, need to set here
		if (!$this->created) {
			$this->created = ES::date()->toSql();
		}

		// fix when key exists, it doesn't get insert to db
		if (!$exist) {
			$stored	= $db->insertObject($this->_tbl, $this, $this->_tbl_key);
		} else {
			$stored	= $db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
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

	/*
	 * Tests whether the current accessed step is in its list of accessed.
	 *
	 * @param   int     $step   The current step that is being requested.
	 * @return  boolean         True if allowed, false otherwise.
	 */
	public function hasStepAccess( $step )
	{
	    return in_array( $step , $this->step_access );
	}

	public function removeAccess( $step )
	{
	    if( is_array( $this->step_access ) )
	    {
	        for( $i = 0; $i < count( $this->step_access ); $i++ )
	        {
	            $stepAccess = $this->step_access[ $i ];

				if( $stepAccess > $step )
				{
	            	unset( $this->step_access[ $i ] );
				}
			}
		}
	}

	public function addStepAccess( $step )
	{
	    if( empty( $this->step_access ) )
	    {
	        $this->step_access = array();
		}

		if( !in_array( $step , $this->step_access ) )
		{
			$this->step_access[] = $step;
		}
		return true;
	}

	/**
	 * Method for caller to set errors during registration.
	 *
	 * Example:
	 * <code>
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 * @param	mixed	Array or boolean or string.
	 * @return	bool	True if success.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function setErrors( $errors )
	{
		// If there's no errors, then we should reset the form.
		if( !$errors )
		{
			$this->set( 'errors' , '' );

			return true;
		}

		// Set the error messages.
		$this->errors 	= ES::makeJSON( $errors );

		return true;
	}

	/**
	 * Method for caller to retrieve errors during registration.
	 *
	 * Example:
	 * <code>
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	Array|bool	An array of standard objects or false when there are no errors.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getErrors( $key = null )
	{
		// If there's no errors,
		if( !$this->errors || is_null( $this->errors ) )
		{
			return false;
		}

		// Error code is always a JSON string. Decode the error string.
		$obj 	= ES::makeObject( $this->errors );

		// Get the vars from the object since they are stored in key/value form.
		$errors	= get_object_vars( $obj );

		if( !is_null( $key ) )
		{
			if( !isset( $errors[ $key ] ) )
			{
				return false;
			}

			return $errors[ $key ];
		}

		return $errors;
	}

	public function getValues()
	{
		if (!$this->values || is_null($this->values)) {
			return false;
		}

		$obj = json_decode($this->values);

		if (!$obj) {
			return false;
		}

		$values	= get_object_vars($obj);

		return $values;
	}
}
