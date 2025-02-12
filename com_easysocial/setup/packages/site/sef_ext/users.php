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

// view
if (isset($view)) {
    addView($title, $view, $menuView);
}

// users/filter
if (isset($filter)) {

	$title[] = ESJString::ucwords(JText::_('COM_EASYSOCIAL_ROUTER_USERS_FILTER_' . strtoupper($filter)));
	shRemoveFromGETVarsList('filter');


	// users/profiletype/id
	if (isset($id)) {

		if ($filter == 'profiletype') {
			$title[] = getProfileAlias($id);
		} else {
			$title[] = $id;
		}

		shRemoveFromGETVarsList('id');
	}

}

// users/filter
if (isset($sort)) {
	$title[] = ESJString::ucwords(JText::_('COM_EASYSOCIAL_ROUTER_USERS_SORT_' . strtoupper($sort)));
	shRemoveFromGETVarsList('sort');
}
