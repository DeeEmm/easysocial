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

class SocialParserJoomla
{
	private $parser = null;

	/**
	 * Parse the content.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($contents)
	{
		$class = 'SimpleXMLElement';

		libxml_use_internal_errors();
		$this->parser = @simplexml_load_string($contents, $class, LIBXML_NOWARNING);

		if (!$this->parser) {
			return false;
		}

		return true;
	}

	/**
	 * Proxy function to call the xml helper object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __get( $key )
	{
		return ( isset( $this->parser->$key ) ) ? $this->parser->$key : false;
	}

	/**
	 * Proxy function to call the xml helper object methods.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __call($method , $args)
	{
		return call_user_func_array(array($this->parser, $method), $args);
	}

}
