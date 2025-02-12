<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialFieldHandlers
{
	/**
	 * This is a list of general handlers that is used as a callback for field triggers
	 *
	 * General handlers have event passed in as the first parameter because general handlers is designed to work with various events
	 * Event specific handler, see below
	 */

	/**
	 * General handler to get triggered field's output
	 *
	 * @since  1.1
	 * @access public
	 * @param  string			$event	Event name to trigger
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function getOutput($event, &$class, &$data, &$result)
	{
		$class->field->output = '';

		if (!is_callable(array($class, $event))) {
			return;
		}

		ob_start();
		call_user_func_array(array($class, $event) , $data);
		$contents = ob_get_contents();
		ob_end_clean();

		if (!empty($contents)) {
			$class->field->output = $contents;
		}
	}

	/**
	 * General handler for validation triggers
	 *
	 * @since  1.1
	 * @access public
	 * @param  string			$event	Event name to trigger
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function validate($event, &$class, &$data, &$result)
	{
		if (!is_callable(array($class, $event))) {
			return true;
		}

		call_user_func_array(array($class, $event), $data);

		if ($class->hasError()) {
			$result[$class->inputName] = $class->getError();
		}
	}

	/**
	 * General handler for beforeSave triggers
	 *
	 * @since  1.1
	 * @access public
	 * @param  string			$event	Event name to trigger
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function beforeSave($event, &$class, &$data, &$result)
	{
		if (!is_callable(array($class, $event))) {
			return;
		}

		$return = call_user_func_array(array($class, $event), $data);

		if (!is_null($return) && $return !== true) {
			$result[$class->inputName] = $return;
		}
	}

	/**
	 * General handler to get result from triggers
	 *
	 * @since  1.1
	 * @access public
	 * @param  string			$event	Event name to trigger
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function getResult($event, &$class, &$data, &$result)
	{
		if (!is_callable(array($class, $event))) {
			return;
		}

		$return = call_user_func_array(array($class, $event), $data);

		if (!is_null($return)) {
			$result[$class->inputName] = $return;
		}
	}

	/**
	 * Magic method to route all unknown handlers to the fallback handler method
	 *
	 * @since  1.1
	 * @access public
	 * @param  string    $name
	 * @param  Array    $arguments
	 */
	public function __call($name, $arguments)
	{
		// If the execution comes here, means there is no custom/specific trigger handler define, and we route this to the fallback function 'getResult', and adding event as the first parameter
		array_unshift($arguments, $name);

		call_user_func_array(array($this, 'getResult'), $arguments);
	}

	/**
	 * Custom/Specific handlers should be placed below this line
	 *
	 * These handlers wouldn't have event as the first parameter because of redundancy
	 *
	 * To write a general handler that works across different event, then define them on top
	 */

	public function onRegisterMini(&$class, &$data, &$result)
	{
		$method = __FUNCTION__;

		if (!method_exists($class, $method)) {
			$method = 'onRegister';

			if (!method_exists($class, $method)) {
				return;
			}
		}

		$this->getOutput($method, $class, $data, $result);
	}

	public function onRegisterMiniValidate(&$class, &$data, &$result)
	{
		$method = __FUNCTION__;

		if (!method_exists($class, $method)) {
			$method = 'onRegisterValidate';

			if (!method_exists($class, $method)) {
				return;
			}
		}

		$this->validate($method, $class, $data, $result);
	}

	/**
	 * Handler for onAdminEdit trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onAdminEdit(&$class, &$data, &$result)
	{
		$method = $this->getAdminEditClass(__FUNCTION__, $class);

		if (!is_callable(array($class, $method))) {
			return;
		}

		// Manually set the event into the class
		// $class->event = $method;

		$this->getOutput($method, $class, $data, $result);
	}

	/**
	 * Handler for onAdminEditValidate trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onAdminEditValidate(&$class, &$data, &$result)
	{
		$method = $this->getAdminEditClass(__FUNCTION__, $class);

		if (!is_callable(array($class, $method))) {
			return;
		}

		// Manually set the event into the class
		$class->event = $method;

		$this->validate($method, $class, $data, $result);
	}

	/**
	 * Handler for onAdminEditBeforeSave trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onAdminEditBeforeSave(&$class, &$data, &$result)
	{
		$method = $this->getAdminEditClass(__FUNCTION__, $class);

		if (!is_callable(array($class, $method))) {
			return;
		}

		// Manually set the event into the class
		$class->event = $method;

		$this->beforeSave($method, $class, $data, $result);
	}

	/**
	 * Handler for onAdminEditAfterSave trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onAdminEditAfterSave(&$class, &$data, &$result)
	{
		$method = $this->getAdminEditClass(__FUNCTION__, $class);

		if (!is_callable(array($class, $method))) {
			return;
		}

		// Manually set the event into the class
		$class->event = $method;

		$this->getResult($method, $class, $data, $result);
	}

	/**
	 * Handler for onAdminEditAfterSaveFields trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onAdminEditAfterSaveFields(&$class, &$data, &$result)
	{
		$method = $this->getAdminEditClass(__FUNCTION__, $class);

		if (!is_callable(array($class, $method))) {
			return;
		}

		// Manually set the event into the class
		$class->event = $method;

		$this->getResult($method, $class, $data, $result);
	}

	/**
	 * Helper function for AdminEdit classes to fallback to Edit class
	 *
	 * @since  1.1
	 * @access private
	 * @param  string			$name	Class name
	 * @param  SocialFieldItem	$class	Field class item
	 */
	private function getAdminEditClass($name, $class)
	{
		if (!method_exists($class, $name)) {
			$name = str_replace('Admin', '', $name);
		}

		return $name;
	}

	/**
	 * Handler for onDisplay trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onDisplay(&$class, &$data, &$result)
	{
		// First data is user object, manually inject the user data in
		$class->set('user' , $data[0]);

		$this->getOutput(__FUNCTION__ , $class , $data , $result);

		if (isset($class->field->output) && empty($class->field->output)) {
			unset($class->field->output);
		}
	}
	
	/**
	 * Handler for onIndexer trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onIndexer(&$class, &$data, &$result)
	{
		if (!is_callable(array($class, __FUNCTION__))) {
			return;
		}

		$return = call_user_func_array(array($class, __FUNCTION__), $data);

		if ($return !== false && $return) {
			$return = trim($return);

			if ($return) {
				$result[] = $return;
			}
		}
	}

	/**
	 * Handler for onIndexerSearch trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onIndexerSearch(&$class, &$data, &$result)
	{
		if (!is_callable(array($class, __FUNCTION__))) {
			return;
		}

		$return = call_user_func_array(array($class, __FUNCTION__), $data);

		if ($return !== false && $return) {
			$return = trim($return);

			if ($return) {
				$result[] = $return;
			}
		}
	}

	/**
	 * Handler for onFriendSuggestSearch trigger
	 *
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onFriendSuggestSearch(&$class, &$data, &$result)
	{
		if (!is_callable(array($class, __FUNCTION__))) {
			return;
		}

		$return = call_user_func_array(array($class, __FUNCTION__), $data);

		if (is_array($return) && count($return) > 0) {
			$result = array_merge($result, $return);
		}
	}


	/**
	 * Handler for onExport trigger
	 *
	 * @author Sammy Boy <sam@stackideas.com>
	 * @since  1.1
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onExport(&$class, &$data, &$result)
	{
		if (!is_callable(array($class, __FUNCTION__))) {
			return;
		}

		$return = call_user_func_array(array($class, __FUNCTION__), $data);
		$result[$class->field->id] = $return;
	}

	/**
	 * Handler for onFieldCheck trigger.
	 *
	 * @since  1.2
	 * @access public
	 * @param  SocialFieldItem	$class	Field class item
	 * @param  Array			$data	Arguments to pass when triggering event
	 * @param  Array			$result Result of the returned value by triggered event
	 */
	public function onFieldCheck(&$class, &$data, &$result)
	{
		if (is_callable(array($class, __FUNCTION__))) {
			call_user_func_array(array($class, __FUNCTION__), $data);

			if ($class->hasError()) {
				return $result[$class->inputName] = $class->getError();
			}
		}

		return $result[$class->inputName] = $class->isRequired();
	}

	public function onGetValue(&$class, &$data, &$result)
	{
		if (!is_callable(array($class, __FUNCTION__))) {
			return;
		}

		$value = call_user_func_array(array($class, __FUNCTION__), $data);

		if (!is_null($value)) {
			$class->field->value = $value;
		}
	}

	public function onProfileCompleteCheck(&$class, &$data, &$result)
	{
		$return = true;

		if (is_callable(array($class, __FUNCTION__))) {

			// Only check if conditional is fulfilled
			if ($class->isConditionalFulfill()) {
				$return = call_user_func_array(array($class, __FUNCTION__), $data);
			}
		}

		// If return is explicitly NOT FALSE, then we consider the field as filled.
		if ($return !== false) {
			$result[$class->field->id] = true;
		}
	}
}
