<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
if ($my->guest || !$my->hasCommunityAccess()) {
	return;
}

// Load up the module engine
$lib = ES::modules($module);
$input = ES::request();

$view = $input->get('view', '', 'cmd');
$clusterId = $input->get('id', 0, 'int');

$allowedViews = array('dashboard', 'groups', 'pages', 'events');

if (!in_array($view, $allowedViews)) {
	return;
}

$layout = $input->get('layout', '', 'cmd');

if ($view != 'dashboard' && $layout != 'item') {
	return;
}

// Load required js for notification
$lib->renderComponentScripts();
$lib->addScript('script.js');



if ($view == 'groups') {
	$context = SOCIAL_TYPE_GROUP;
} else if ($view == 'pages') {
	$context = SOCIAL_TYPE_PAGE;
} else if ($view == 'events') {
	$context = SOCIAL_TYPE_EVENT;
} else {
	$context = SOCIAL_TYPE_USER;
}

require_once(__DIR__ . '/helper.php');
$helper = new EasySocialModPostTypesHelper();
$postTypes = $helper->getPostTypes($context, $clusterId);

require($lib->getLayout());
