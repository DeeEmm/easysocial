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

class SocialFacebookCheckin
{
	public function process( &$obj , $item , $currentUserId )
	{
		// Check for the content
		$content 	= isset( $item[ 'message' ] ) ? $item[ 'message' ]  : '';
		$picture	= isset( $item[ 'picture' ] ) ? $item[ 'picture' ] : '';
		$title		= isset( $item[ 'name' ] ) ? $item[ 'name' ] : '';
		$place 		= isset( $item[ 'place' ] ) ? $item[ 'place' ] : '';

		if( !$content )
		{
			$content	= isset( $item[ 'story' ] ) ? $item[ 'story' ] : '';
		}

		if( $place )
		{
			$place 	= ES::json()->encode( $place );
		}

		// if( $item[ 'from' ][ 'id' ] != $currentUserId )
		// {
		// 	$content 	= JText::_( 'was tagged at ' . $title );
		// }exit;

		$obj->set( 'place' , $place );
		$obj->set( 'picture' , $picture );
		$obj->set( 'title' 		, $title );
		$obj->set( 'content' , $content );
	}
}
