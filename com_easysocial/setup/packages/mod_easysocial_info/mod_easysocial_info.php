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

// Check if EasySocial is installed
$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
$exists = JFile::exists($file);

if (!$exists) {
	return;
}

require_once($file);

ES::language()->loadAdmin();
ES::initialize();

$lib = ES::modules($module);
$lib->addScript('module.js');

$usersModel = ES::model('Users');

$showCounterHeader = false;

// Get total users
if ($params->get('show_total_users', true)) {
	$totalUsers = $usersModel->getTotalUsers();
	$showCounterHeader = true;
}

// Get total number of pages
if ($params->get('show_total_pages', true)) {
	$pagesModel = ES::model('Pages');
	$totalPages = $pagesModel->getTotalPages(['userblock' => false, 'types' => 'all']);
	$showCounterHeader = true;
}

// Get total number of groups
if ($params->get('show_total_groups', true)) {
	$groupsModel = ES::model('Groups');
	$totalGroups = $groupsModel->getTotalGroups(['userblock' => false, 'types' => 'all']);
	$showCounterHeader = true;
}

// Total online users
if ($params->get('show_total_online', true)) {
	$totalOnline = $usersModel->getTotalOnlineUsers();
	$showCounterHeader = true;
}

// Get total events
if ($params->get('show_total_events', true)) {
	$eventsModel = ES::model('Events');
	$totalEvents = $eventsModel->getTotalEvents(['userblock' => false, 'types' => 'all']);
	$showCounterHeader = true;
}

// Get total albums
if ($params->get('show_total_albums', true)) {
	$photosModel = ES::model('Albums');
	$totalAlbums = $photosModel->getTotalAlbums();
	$showCounterHeader = true;
}

// Get total videos
if ($params->get('show_total_videos', true)) {
	$videosModel = ES::model('Videos');
	$totalVideos = $videosModel->getTotalVideos(['state' => 'all']);
	$showCounterHeader = true;
}

// Retrieve total audios
if ($params->get('show_total_audios', true)) {
	$audiosModel = ES::model('Audios');
	$totalAudios = $audiosModel->getTotalAudios(['state' => 'all']);
	$showCounterHeader = true;
}

$pendingUsers = [];

if ($params->get('show_total_pending', false) || $params->get('show_pending_users_statistic', true)) {
	$pendingUsers = $usersModel->getPendingUsers(['limit' => $params->get('pendingLimit', 5)]);
}

// Get total pending users
if ($params->get('show_total_pending', false)) {
	$totalPendingUsers = $usersModel->getPendingUsersCount();
	$showCounterHeader = true;
}

// Get reports model
if ($params->get('show_total_reports', false)) {
	$reportsModel = ES::model('Reports');
	$totalReports = $reportsModel->getReportCount();
	$showCounterHeader = true;
}

// Get pending users statistic
if ($params->get('show_pending_users_statistic', true)) {
	$totalPending = $usersModel->getPendingUsersCount();
}

// Get recent users statistic
if ($params->get('show_recent_users_statistic', true)) {
	$recentUsers = $usersModel->getUsers(['limit' => 5, 'ignoreESAD' => true, 'ordering' => 'id', 'direction' => 'desc']);
	$recentUsers = ES::user($recentUsers);

	// Get the user's address
	if ($recentUsers) {
		foreach ($recentUsers as $user) {
			$location = $user->getFieldData('ADDRESS');

			// Set a default value for location
			$user->location = false;

			if (!$location) {
				continue;
			}

			if (isset($location['city']) && $location['city']) {
				$user->location = $location['city'];
			}

			// State has a higher precedence
			if (isset($location['state']) && $location['state']) {
				$user->location = $location['state'];
			}

			// If country is available, append the data
			if (isset($location['country']) && $location['country']) {
				$user->location .= ',' . $location['country'];
			}
		}
	}
}

$config = ES::config();
$version = ES::getLocalVersion();
$lib = ES::modules($module);

require JModuleHelper::getLayoutPath('mod_easysocial_info', $params->get('layout', 'default'));
