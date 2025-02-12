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

class SocialVersion
{
	private	$adapter = null;
	private	$plattform = null;
	public $version = null;
	static $instance = null;

	public function __construct()
	{
		$this->version	= explode( '.' , JVERSION );

		return $this;
	}

	/**
	 * Creates an instance of the config object.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	$key	The configuration key.
	 */
	public static function getInstance()
	{
		if( is_null( self::$instance ) )
		{
			self::$instance	= new self();
		}

		return self::$instance;
	}

	public function getVersion()
	{
		return $this->version[0] . '.' . $this->version[ 1 ];
	}

	public function getLongVersion()
	{
		return implode( '.' , $this->version );
	}

	public function getCodeName()
	{
		$version = $this->version[ 0 ] . '.' . $this->version[ 1 ];
		$versionName = 'joomla15';

		if ($version >= '4.0') {
			$versionName = 'joomla40';
			return $versionName;
		}

		if ($version >= '3.0') {
			$versionName = 'joomla30';
			return $versionName;
		}

		if ($version >= '1.6') {
			$versionName = 'joomla30';
			return $versionName;
		}

		return $versionName;
	}

	public function debug()
	{
		return __FILE__;
	}
}
