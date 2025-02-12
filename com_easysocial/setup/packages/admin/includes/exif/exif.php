<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Load adapter.
require_once(dirname(__FILE__) . '/adapters/exif.php');
require_once(dirname(__FILE__) . '/adapters/reader.php');

/**
 * Class to proxy SocialExifLibrary
 *
 * @since	1.0
 */
class SocialExif
{
	public $lib = null;
	public $reader = null;

	public $file = null;
	/**
	 * This class uses the factory pattern.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string			The image driver to use.
	 * @return	SocialImage		Returns itself for chaining.
	 */
	public static function factory()
	{
		$exif = new self();

		return $exif;
	}

	/**
	 * Class constructor
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 */
	public function __construct()
	{
		$this->reader = new SocialExifReader();
	}

	/**
	 * Determines if exif is available on the system.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function isAvailable($checkConfig = true)
	{
		$config	= ES::config();

		if ($checkConfig && !$config->get('photos.import.exif')) {
			return false;
		}

		$state = function_exists('exif_read_data');

		return $state;
	}

	/**
	 * Reads the exif information from a given path
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function load($file)
	{
		$this->exif = $this->reader->getExifFromFile($file);

		return true;
	}

	/**
	 * Maps back the call method functions to the exif library.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	Method's name.
	 * @param	mixed	Arguments
	 * @return
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {
			foreach ($args as &$arg) {
				$refArray[] =& $arg;
			}
		}

		return call_user_func_array(array($this->exif, $method), $refArray);
	}
}
