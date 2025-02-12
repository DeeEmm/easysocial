<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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

// Module should not be rendered when filtering by specific hashtag that doesn't exist.
$hashtag = $input->get('tag', '', 'default');

if ($hashtag) {
	return;
}

$option = $input->get('option', '', 'default');
$view = $input->get('view', '', 'cmd');

// Only render this module on the user dashboard page
if ($option !== 'com_easysocial' || $view !== 'dashboard') {
	return;
}

// Load required js for notification
$lib->renderComponentScripts();
$lib->addScript('script.js');

require_once(__DIR__ . '/helper.php');
$helper = new EasySocialModNewsfeedHelper();

$stream = ES::stream();
$createCustomFilter = false;

$start = $lib->config->get('users.dashboard.start');
$filter	= $input->get('type', $start, 'word');
$filterId = $input->get('filterid', 0, 'int');

$filterList = $helper->getFilterLists();
$streamFilterEnabled = false;

$showCustomFilters = $params->get('display_custom', true) && ($filterList && count($filterList) > 0);

require($lib->getLayout());
