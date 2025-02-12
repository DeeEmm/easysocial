<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

// Include main engine
$engine = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
$exists = JFile::exists($engine);

if (!$exists) {
	return;
}

// Include the engine file.
require_once($engine);

$my = ES::user();

// If the user is not logged in, don't show the menu
if ($my->guest) {
	return;
}

// Load up the module engine
$lib = ES::modules($module, true);
$lib->renderComponentScripts();
$lib->addScript('script.js');


// Badges uses admin language files
ES::language()->loadAdmin();

// Get the logout return url
$logoutReturn = $lib->getLogoutReturnUrl();

$showToolbar = false;

// Determine whether we should display the toolbar or not
if ($params->get('show_friends_notifications', true) || $params->get('show_conversation_notifications', true) ||
	$params->get('show_system_notifications', true) || $params->get('show_edit', true)) {
	$showToolbar = true;
}

require($lib->getLayout());
