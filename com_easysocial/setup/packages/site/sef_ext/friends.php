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
	if (!empty($userid)) {
		$title[]	= getUserAlias( $userid );

		if (isEasysocialURLPluginEnabled()) {
			$menuView = '';
		}
	}

	shRemoveFromGETVarsList( 'userid' );
}

if (isset($view)) {
	addView( $title , $view, $menuView);
}

if( isset( $listId ) )
{
	$list 		= ES::table( 'List' );
	$list->load( $listId );

	$alias 		= JFilterOutput::stringURLSafe( $list->title );
	$title[]	= $alias;

	shRemoveFromGETVarsList( 'listId' );
}

if( isset( $layout ) )
{
	addLayout( $title,  $view , $layout );
}

if( isset( $filter ) )
{
	$title[] 	= ESJString::ucwords( JText::_( 'COM_EASYSOCIAL_ROUTER_FRIENDS_FILTER_' . strtoupper( $filter ) ) );

	shRemoveFromGETVarsList( 'filter' );
}
