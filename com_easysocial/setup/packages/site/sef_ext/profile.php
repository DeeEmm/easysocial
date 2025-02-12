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
// /view/profile/alias
if (isset($id)) {

    $userAlias = getUserAlias($id);

    $title[] = JFilterOutput::stringURLSafe($userAlias);

    shRemoveFromGETVarsList('id');
    shRemoveFromGETVarsList('layout');
    shRemoveFromGETVarsList('view');
}


// /view
if (!isset($id) && isset($view)) {
    addView($title, $view, $menuView);
}

// /view/layout
if (isset($layout)) {
    addLayout($title, $view, $layout);
}

if (isset($appId) && $appId) {

	$title[] = getAppAlias($appId);

	shRemoveFromGETVarsList('appId');
}
