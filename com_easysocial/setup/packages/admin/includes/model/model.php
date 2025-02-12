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

class SocialModel
{
	static $models 	= array();


	public static function factory( $name )
	{
		$className	= 'EasySocialModel' . ucfirst( $name );

		if( !class_exists( $className ) )
		{
			jimport( 'joomla.application.component.model' );

			// @TODO: Properly test if the file exists before including it.
			JLoader::import( strtolower( $name ) , SOCIAL_MODELS );
		}

		$model	= JModel::getInstance( $name , 'EasySocialModel' );

		return $model;
	}

	public function getInstance( $name )
	{

		if( !isset( self::$models[ $name ] ) )
		{
			self::$models[ $name ]	= self::factory( $name );
		}

		return self::$models[ $name ];
	}

}
