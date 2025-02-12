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
defined('_JEXEC') or die('Unauthorized Access');

// Determine how is the user's current id being set.
if (isset($userid)) {
	$title[]	= getUserAlias($userid);

	shRemoveFromGETVarsList('userid');
}

// Add the view to the list of titles
if (isset($view)) {
	addView($title , $view, '');
}

// Determine which type does the photo belong to
if (isset($type)) {

    if ($type != SOCIAL_TYPE_USER) {
		$title[] = ESJString::ucwords(JText::_('COM_EASYSOCIAL_SH404_PHOTOS_TYPE_' . strtoupper($type)));
	}

	if (isset($uid)) {

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
}

// For photos, we need to get the beautiful title
if (isset($id)) {
	$photo 	= ES::table('Photo');
	$photo->load((int) $id);

	// Remove known extensions from title
	$extensions = array('jpg' , 'png' , 'gif');

	$fragment	= JFilterOutput::stringURLSafe(ESJString::str_ireplace($extensions , '' , $photo->title));

	$fragment	= uniqueUrl($title , $id.'-'.$fragment);

	$title[] = $fragment;

	if (isset($layout) && $layout != 'item') {
		addLayout($title, $view, $layout);
	} else {
		shRemoveFromGETVarsList('layout');
	}

	shRemoveFromGETVarsList('id');
}
