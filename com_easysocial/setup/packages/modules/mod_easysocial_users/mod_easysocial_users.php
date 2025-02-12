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

$lib = ES::modules($module);
$my = ES::user();

// Check filter type
$filterType = $params->get('filter' , 'recent');

// there is no friends if user is a guest.
if ($filterType == 'onlinefriends' && $my->guest) {
	return;
}


$model = ES::model('Users');
$limit = $params->get('total', 10);
$ordering = $params->get('ordering', 'registerDate');
$direction = $params->get('direction', 'desc');
$avatarSize = $params->get('avatar_size', 'default');

$options = array('ordering' => $ordering, 'direction' => $direction, 'limit' => $limit);

if ($ordering == 'connectionDate') {
	if ($my->guest || $filterType != 'onlinefriends') {
		// no point we sort by last connection date as guest has no friends.
		$options['ordering'] = 'registerDate';
	} else {
		$options['ordering'] = 'connectionDate';
	}
}

if ($filterType == 'online' || $filterType == 'onlinefriends') {
	$options['login'] = true;
	$options['frontend'] = true;

	if ($filterType == 'onlinefriends') {
		$options['friendOnly'] = true;
	}
}


if ($params->get('profileId')) {
	$profileId = $params->get('profileId');
	$options['profile'] = $profileId;
}


// Determine if admins should be included in the user's listings.
$config = ES::config();
$admin = $config->get('users.listings.admin');

$options['includeAdmin'] = $admin ? true : false;

// Check if we should only include user's with avatar.
if ($params->get('hasavatar', false) == true) {
	$options['picture']	= true;
}

// we only want published user.
$options['published']	= 1;

// exclude users that blocked the current logged in user
$options['excludeblocked'] = 1;

$inclusion = $params->get('user_inclusion', '');

if ($inclusion) {
	$inclusion = trim($inclusion);

	$options['inclusion'] = explode(',', $inclusion);
}

// need to pass in this flag so that the sql will be ligther.
$options['isModule'] = true;

$result = $model->getModuleUsers($options);

if (!$result) {
	return;
}

$ids = array();
foreach ($result as $row) {
	$ids[] = $row->id;
}

// lets preload users;
ES::user($ids);

$users = array();
foreach ($result as $row) {
	$users[] = ES::user($row->id);
}

require($lib->getLayout());
