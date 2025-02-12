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

// This is required if anyone needs access to the engine.
require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/constants.php');
require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/tables/table.php');
require_once(SOCIAL_LIB . '/exception/exception.php');
require_once(SOCIAL_LIB . '/router.php');

jimport('joomla.filesystem.file');

if (!function_exists('dump')) {
	function dump()
	{
		$args = func_get_args();

		echo '<pre>';

		foreach ($args as $arg) {
			var_dump($arg);
		}
		echo '</pre>';
		exit;
	}
}

if (!function_exists('vd')) {

	function vd()
	{
		$args = func_get_args();

		echo '<pre>';

		foreach ($args as $arg) {
			var_dump($arg);
		}
		echo '</pre>';
		exit;
	}
}

if (!function_exists('pdump')) {
	function pdump()
	{
		$args = func_get_args();

		echo '<pre>';

		foreach ($args as $arg) {
			print_r($arg);
		}
		echo '</pre>';
		exit;
	}
}


// check if current request is a REST request or not.
$rest = false;
if (!defined('SOCIAL_COMPONENT_CLI')) {
	$rest = JFactory::getApplication()->input->get('rest', false, 'bool');
}

if ($rest === true) {
	class EasySocialWrapper
	{
		protected $config = null;
		protected $jConfig = null;
		protected $app = null;
		protected $input = null;
		protected $my = null;
		protected $doc = null;

		public function __construct()
		{

		}
	}
} else {
	class EasySocialWrapper
	{
		public $config = null;
		public $jConfig = null;
		public $app = null;
		public $input = null;
		public $my = null;
		public $doc = null;

		public function __construct()
		{

		}
	}
}


class EasySocial extends EasySocialWrapper
{
	public $access = null;
	protected $error = null;

	public function __construct()
	{
		if (!defined('SOCIAL_COMPONENT_CLI')) {
			$this->doc = JFactory::getDocument();
			$this->app = JFactory::getApplication();
			$this->input = ES::request();
			$this->jConfig = ES::jConfig();

			$this->my = ES::user();
			$this->access = ES::access();
			$this->string = ES::string();
		}

		$this->config = ES::config();
	}

	public function setError($message)
	{
		$this->error = $message;

		return false;
	}

	public function getError()
	{
		if (!$this->error) {
			return false;
		}

		return JText::_($this->error);
	}

	/**
	 * Determines if the current request is from mobile
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMobile()
	{
		$mobile = ES::responsive()->isMobile();

		return $mobile;
	}
	/**
	 * Allows caller to normalize an array key / object key
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function normalize($object, $key, $default = false)
	{
		if (is_array($object)) {
			return isset($object[$key]) ? $object[$key] : $default;
		}

		if (is_object($object)) {
			return isset($object->$key) ? $object->$key : $default;
		}
	}
}

/**
 * Reusable classes
 */
class SocialObject
{
	/**
	 * Given an array of items, map it against the object properties.
	 *
	 * @access	public
	 * @param 	Array	A list of items in an associative array.
	 * @return 	null
	 */
	public function map( $items )
	{
		// @task: Process arrays
		if( is_array( $items ) )
		{
			foreach( $items as $itemKey => $itemValue )
			{
				if( isset( $this->$itemKey ) )
				{
					$this->$itemKey	= $itemValue;
				}
			}
		}

		// @task: If this is a stdclass object.
		if( is_object( $items ) )
		{
			$properties 	= get_object_vars( $items );

			foreach( $properties as $property )
			{
				if( isset( $this->$property ) )
				{
					$this->$property 	= $items->$property;
				}
			}
		}
	}

	/**
	 * Returns a property value from the object.
	 *
	 * @access	public
	 * @param	string 	$key		The key property.
	 * @param	string 	$default 	The default value if the property is empty.
	 */
	public function get( $key , $default = '' )
	{
		if( !isset( $this->$key ) || empty( $this->$key ) || is_null( $this->$key ) )
		{
			return $default;
		}

		return $this->$key;
	}
}
