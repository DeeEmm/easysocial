<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

/**
 * Abstract layer for DB helpers
 */
abstract class SocialDBHelper
{
	public $db = null;

	public function __construct()
	{
		$this->db = JFactory::getDBO();
	}

	public function __call( $method , $args )
	{
		$refArray = array();

		if ($args) {
			foreach ($args as &$arg) {
				if ($arg instanceof SocialSql) {
					$string = $arg->toString();
					$refArray[] =& $string;
				} else {
					$refArray[] =& $arg;
				}
			}
		}

		// Fixed for Joomla 4 compatibility
		// if ($method == 'query' && !method_exists($this->db, 'query')) {
		// 	$method = 'execute';
		// }

		return call_user_func_array(array($this->db, $method), $refArray);
	}

	/**
	 * Override the quote to check if array is passed in, then quote all the items accordingly.
	 * This is actually already supported from J3.3 but for older versions, we need this compatibility layer
	 */
	public function quote($item, $escape = true)
	{
		if (!is_array($item)) {
			return $this->db->quote($item, $escape);
		}

		$result = array();

		foreach ($item as $i) {
			$result[] = $this->db->quote($i, $escape);
		}

		return $result;
	}

	/**
	 * Alias for quote.
	 *
	 * @since  1.3.7
	 * @access public
	 */
	public function q($item, $escape = true)
	{
		return $this->quote($item, $escape);
	}

	public function qn($name, $as = null)
	{
		return $this->quoteName($name, $as);
	}
}
