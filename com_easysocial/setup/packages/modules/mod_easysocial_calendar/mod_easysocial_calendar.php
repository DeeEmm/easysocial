<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
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

ES::initialize();

// Include the engine file.
require_once($engine);

$lib = ES::modules($module);
$input = ES::request();

// We don't want to render calendar if the user is not allowed to use easysocial
if ($lib->config->get('general.site.lockdown.enabled') && $lib->my->guest) {
	return;
}

// Determines if the calendar should be real hyperlinks or not
$view = $input->get('view', '', 'cmd');
$layout = $input->get('layout', '', 'cmd');

$filter = $params->get('filter', 'all');
$categoryId = $params->get('categoryId', 0);
$clusterId = 0;

if ($filter === 'group') {
	$clusterId = $params->get('groupId', 0);
	$filter = 'cluster';
}

if ($filter === 'page') {
	$clusterId = $params->get('pageId', 0);
	$filter = 'cluster';
}

$useRealHyperlinks = ($view != 'events') || ($view == 'events' && $layout != 'item') ? 'true' : 'false';

require($lib->getLayout());
