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

jimport('joomla.filesystem.file');

class SocialParser
{
	private $helper = null;

	public function __construct()
	{
		require_once(__DIR__ . '/helpers/joomla.php');

		$this->helper = new SocialParserJoomla();
	}

	/**
	 * This class can only be created every time.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory()
	{
		return new self();
	}

	/**
	 * Loads the content from a file or a string.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($item)
	{
		$contents = $item;

		if (is_file($item)) {
			$contents = file_get_contents($item);
		}

		// Call the helper to load the items.
		$state	= $this->helper->load($contents);

		if (!$state) {
			return false;
		}

		return $this;
	}

	/**
	 * Proxy function to call the xml helper object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __get($key)
	{
		return (isset($this->helper->$key)) ? $this->helper->$key : false;
	}

	/**
	 * Proxy function to call the xml helper object methods.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __call($method, $args = array())
	{
		return call_user_func_array(array($this->helper, $method), $args);
	}
}
