<?php
/**
* @package    EasySocial
* @copyright  Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class SocialFacebookPhoto
{
	public function process( &$obj , $item )
	{
		// Check if story is used
		$content 	= isset( $item[ 'story' ] ) ? $item[ 'story' ] : '';

		if( !$content )
		{
			$content	= isset( $item[ 'message' ] ) ? $item[ 'message' ] : '';
		}

		$obj->set( 'picture' 	, $item[ 'picture' ] );
		$obj->set( 'link'		, $item[ 'link' ] );
		$obj->set( 'content'	, $content );
	}
}
