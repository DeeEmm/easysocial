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

if ($my->guest) {
	return;
}

$lib = ES::modules($module);

if (!$lib->config->get('friends.enabled')) {
	return;
}

// Get list of friends by the current user.
$limit = $params->get('limit', 6);

// Determine if widget should refresh the list automatically when user click on request friend button.
$refresh = $params->get('refresh_list', true);

// Determine if we should display view all button
$showMore = $params->get('showall_link', true);

$model = ES::model('Friends');
$result = $model->getSuggestedFriends($my->id, $limit);

if (!$result) {
	return;
}

require($lib->getLayout());
