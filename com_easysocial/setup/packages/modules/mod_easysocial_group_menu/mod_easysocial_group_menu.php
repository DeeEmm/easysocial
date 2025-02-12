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

$lib = ES::modules($module);

// Get the current logged in user object
$my = ES::user();
$input = ES::request();

// This module will only appear on group pages
$view = $input->get('view', '', 'cmd');;
$layout = $input->get('layout', '', 'cmd');
$id = $input->get('id', '', 'int');
$uid = $input->get('uid', '', 'int');
$type = $input->get('type', '', 'string');

$groupView = false;
$allowedViews = array('albums', 'videos', 'audios', 'events');

if ($uid && $type == SOCIAL_TYPE_GROUP && in_array($view, $allowedViews)) {
	$groupView = true;
	$id = $uid;
}

if (($view != 'groups' || $layout != 'item' || !$id) && !$groupView) {
	return;
}

$group = ES::group($id);

// Ensure that the group really exists before rendering the module
if (!$group->id) {
	return;
}

$cover = $group->getCoverData();
$apps = $lib->getClusterApps($group);

require($lib->getLayout());
