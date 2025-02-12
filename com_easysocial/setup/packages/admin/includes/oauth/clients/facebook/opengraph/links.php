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

class SocialFacebookLinks
{
	public function process( &$obj , $data )
	{
		// Get the title of the link
		$title 		= isset( $item[ 'name' ] ) ? $item[ 'name' ] : '';
		$desc 		= isset( $item[ 'description' ] ) ? $item[ 'description' ] : '';
		$link 		= isset( $item[ 'link' ] ) ? $item[ 'link' ] : '';
		$picture 	= isset( $item[ 'picture' ] ) ? $item[ 'picture' ] : '';

		$content 	= isset( $item[ 'story' ] ) ? $item[ 'story' ] : '';

		// Likes
		if( isset( $item[ 'application' ][ 'namespace' ] ) && $item[ 'application' ][ 'namespace' ] == 'likes' )
		{
			$content 	= 'Likes an article.';
		}

		$obj->set( 'title'		, $title );
		$obj->set( 'content'	, $content );
		$obj->set( 'link'		, $link );
		$obj->set( 'picture'	, $picture );
		$obj->set( 'desc'		, $desc );
		$obj->set( 'link'		, $item[ 'link' ] );
	}
}
