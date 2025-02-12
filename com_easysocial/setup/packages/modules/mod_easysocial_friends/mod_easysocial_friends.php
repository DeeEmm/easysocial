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

// If friends is disabled, we shouldn't render anything
$config = ES::config();

if (!$config->get('friends.enabled')) {
	return;
}

$lib = ES::modules($module);

// Retrieve the user's friends now.
$model = ES::model('Friends');
$limit = (int) $params->get('limit', 6);

$options = array('limit' => $limit);

// Retrieve the list of friends
if ($params->get('filter') == 'friends') {
	$friends = $model->getFriends($my->id, $options);
}

if ($params->get('filter', 'friends') == 'online') {
	$friends = $model->getOnlineFriends($my->id, $options);
}

if (!$friends) {
	return;
}

require($lib->getLayout());
