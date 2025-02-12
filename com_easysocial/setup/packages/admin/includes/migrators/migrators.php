<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class SocialMigrators
{
	var $component 	= null;

	var $helper 	= null;

	/**
	 * Class constructor
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct( $component )
	{
		$this->component 		= $component;

		$fileName	= strtolower( $component );

		$helperFile	= dirname( __FILE__ ) . '/helpers/' . $fileName . '.php';

		require_once( $helperFile );
		$className	= 'SocialMigratorHelper' . ucfirst( $component );

		$this->helper	= new $className();

	}

	public static function factory( $component )
	{
		return new self( $component);
	}

	/**
	 * Maps back the call method functions to the helper.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	Method's name.
	 * @param	mixed	Arguments
	 * @return
	 */
	public function __call( $method , $args )
	{
		$refArray	= array();

		if( $args )
		{
			foreach( $args as &$arg )
			{
				$refArray[]	=& $arg;
			}
		}
		return call_user_func_array( array( $this->helper , $method ) , $refArray );
	}
}
