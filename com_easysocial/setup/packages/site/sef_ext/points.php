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

// Determine how is the user's current id being set.
if( isset( $userid ) )
{
	$title[]	= getUserAlias( $userid );

	shRemoveFromGETVarsList( 'userid' );

	if (isEasysocialURLPluginEnabled()) {
		$menuView = '';
	}
}

if( isset( $view ) )
{
	addView( $title , $view, $menuView );
}

if( isset( $id ) )
{
	$points		= ES::table( 'Points' );
	$points->load( $id );

	$title[]	= $id . '-' . JFilterOutput::stringURLSafe( $points->get( 'title' ) );

	shRemoveFromGETVarsList( 'id' );
	shRemoveFromGETVarsList( 'layout' );
}

// URL: /user/achievements
if( isset( $layout ) && !isset( $id ) )
{
	addLayout( $title , $view , $layout );
}
