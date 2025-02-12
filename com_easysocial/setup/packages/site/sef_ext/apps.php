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

// Determine which type does the video belong to
if (isset($type)) {

	if ($type != SOCIAL_TYPE_USER) {
		$title[] = ESJString::ucwords(JText::_('COM_EASYSOCIAL_SH404_TYPE_' . strtoupper($type)));
	}

	if (isset($uid)) {


		$alias = '';

		if ($type == SOCIAL_TYPE_USER) {
			$alias = getUserAlias($uid);
		}

		if ($type == SOCIAL_TYPE_GROUP) {
			$alias = getGroupAlias($uid);
		}

		if ($type == SOCIAL_TYPE_EVENT) {
			$alias = getEventAlias($uid);
		}

		if ($type == SOCIAL_TYPE_PAGE) {
			$alias = getPageAlias($uid);
		}


		$title[] = $alias;

		shRemoveFromGETVarsList('uid');
	}

	shRemoveFromGETVarsList('type');

	if (isEasysocialURLPluginEnabled()) {
		$menuView = '';
	}
}


if (isset($view)) {
	addView($title , $view, $menuView);
}

// Layouts
if (isset($layout)) {
	addLayout($title, $view, $layout);
}


// For videos, we need to get the beautiful title
if (isset($id)) {

	// Get the video alias
	$alias = getAppAlias($id);

	// Set the video alias
	$title[] = $alias;

	shRemoveFromGETVarsList('id');
}

if ( isset($filter)) {
	$title[] = $filter;

	shRemoveFromGETVarsList( 'filter' );
}


if (isset($id) && isset($customView) && $customView) {

    $appid = (int) $id;
    $tbl = ES::table('App');
    $tbl->load($appid);

    $appElement = $tbl->element;

	$appAdapter = dirname( __FILE__ ) . '/apps/' . strtolower( $appElement ) . '.php';

	if (JFile::exists($appAdapter)) {
		include $appAdapter;
    } else {
    	$title[] = $customView;
		shRemoveFromGETVarsList( 'customView' );
    }
}


if (isset($sort)) {
	$title[] = $sort;

	shRemoveFromGETVarsList( 'sort' );
}
