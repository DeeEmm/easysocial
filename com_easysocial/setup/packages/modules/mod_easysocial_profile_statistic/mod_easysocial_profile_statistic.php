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

$lib = ES::modules($module);

// current logged in user
$my = ES::user();

if ($my->guest) {
	// if user is a guest, do not proceed further.
	return;
}

// Determine if admins should be included in the user's listings.
$config = ES::config();

$accessModel = ES::model('AccessLogs');
$access = $my->getAccess();
$stat = array();

	// Template
	// $obj = new stdClass();
	// $obj->maxLimit = 0;
	// $obj->totalUsage = 0;
	// $obj->intervalType = 0;
	// $obj->intervalLimit = 0;
	// $obj->totalIntervalUsage = 0;
	// $obj->icon = 'fa-icon';

	// Interval type
	// 0 = no limit
	// 1 = daily
	// 2 = weekly
	// 3 = monthly
	// 4 = yearly

// conversation
if ($params->get('show_conversation')) {
	$model = ES::model('Conversations');

	$obj = new stdClass();
	$obj->maxLimit = 0;
	$obj->totalUsage = $model->getTotalSent($my->id);
	$obj->intervalType = 1;
	$obj->intervalLimit = $access->get('conversations.send.daily');
	$obj->totalIntervalUsage = $model->getTotalSentDaily($my->id);
	$obj->icon = 'fa-envelope';

	$stat['conversation'] = $obj;
}

// events
if ($params->get('show_event') && $access->get('events.create') && $config->get('events.enabled')) {
	$tmp = $access->get('events.limit');

	$obj = new stdClass();
	$obj->maxLimit = 0;
	$obj->totalUsage = $accessModel->getUsage('events.limit', $my->id);
	$obj->intervalType = $tmp->interval;
	$obj->intervalLimit = $tmp->value;
	$obj->totalIntervalUsage = $accessModel->getUsage('events.limit', $my->id, $tmp->interval);
	$obj->icon = 'fa-calendar';

	$stat['event'] = $obj;
}

// groups.limit // create
if ($params->get('show_group') && $access->get('groups.create') && $config->get('groups.enabled')) {
	$tmp = $access->get('groups.limit');

	$obj = new stdClass();
	$obj->maxLimit = 0;
	$obj->totalUsage = $accessModel->getUsage('groups.limit', $my->id);
	$obj->intervalType = $tmp->interval;
	$obj->intervalLimit = $tmp->value;
	$obj->totalIntervalUsage = $accessModel->getUsage('groups.limit', $my->id, $tmp->interval);
	$obj->icon = 'fa-users';

	$stat['group'] = $obj;
}

// pages.limit
if ($params->get('show_page') && $access->get('pages.create') && $config->get('pages.enabled')) {
	$tmp = $access->get('pages.limit');

	$obj = new stdClass();
	$obj->maxLimit = 0;
	$obj->totalUsage = $accessModel->getUsage('pages.limit', $my->id);
	$obj->intervalType = $tmp->interval;
	$obj->intervalLimit = $tmp->value;
	$obj->totalIntervalUsage = $accessModel->getUsage('pages.limit', $my->id, $tmp->interval);
	$obj->icon = 'fa-columns';

	$stat['page'] = $obj;
}

// friends.limit // make friend
if ($params->get('show_friend') && $config->get('friends.enabled')) {
	$obj = new stdClass();
	$obj->maxLimit = $access->get('friends.limit');
	$obj->totalUsage = $my->getTotalFriends() + $my->getTotalFriendRequestsSent();
	$obj->requestedFriends = $my->getTotalFriendRequestsSent();
	$obj->intervalType = 0;
	$obj->intervalLimit = 0;
	$obj->totalIntervalUsage = 0;
	$obj->icon = 'fa-users';

	$stat['friend'] = $obj;
}

// albums.total
if ($params->get('show_album')) {
	$obj = new stdClass();
	$obj->maxLimit = $access->get('albums.total');
	$obj->totalUsage = $my->getTotalAlbums(true);
	$obj->intervalType = 0;
	$obj->intervalLimit = 0;
	$obj->totalIntervalUsage = 0;
	$obj->icon = 'fa-images';

	$stat['album'] = $obj;
}

// photos.uploader.maxdaily
if ($params->get('show_photo')) {
	$obj = new stdClass();
	$obj->maxLimit = $access->get('photos.uploader.max');
	$obj->totalUsage = $my->getTotalPhotos(false, true);
	$obj->intervalType = 1;
	$obj->intervalLimit = $access->get('photos.uploader.maxdaily');
	$obj->totalIntervalUsage = $my->getTotalPhotos(true, true);
	$obj->icon = 'fa-image';

	$stat['photo'] = $obj;
}

// videos.daily
if ($params->get('show_video')) {
	$obj = new stdClass();
	$obj->maxLimit = $access->get('videos.total');
	$obj->totalUsage = $my->getTotalVideos(false, true);
	$obj->intervalType = 1;
	$obj->intervalLimit = $access->get('videos.daily');
	$obj->totalIntervalUsage = $my->getTotalVideos(true, true);
	$obj->icon = 'fa-film';

	$stat['video'] = $obj;
}

// audios.daily
if ($params->get('show_audio', true)) {
	$obj = new stdClass();
	$obj->maxLimit = $access->get('audios.total');
	$obj->totalUsage = $my->getTotalAudios(false, true);
	$obj->intervalType = 1;
	$obj->intervalLimit = $access->get('audios.daily');
	$obj->totalIntervalUsage = $my->getTotalAudios(true, true);
	$obj->icon = 'fa-music';

	$stat['audio'] = $obj;
}

// Lets format the text
foreach ($stat as $key => &$obj) {
	$obj->text_interval = false;
	$obj->text_total = false;

	if ($obj->intervalLimit) {
		$obj->text_interval = JText::sprintf('COM_ES_PROFILE_STATS_COUNTER', $obj->totalIntervalUsage, $obj->intervalLimit) . ' (' . JText::_('COM_ES_PROFILE_STATS_INTERVAL_TYPE_' . $obj->intervalType) . ')';
	}

	if ($obj->intervalLimit == 0 || $obj->intervalType != 0) {
		if ($key == 'friend') {
			$obj->text_total = $obj->maxLimit ? JText::sprintf('COM_ES_PROFILE_STATS_FRIENDS_COUNTER_MAX', $obj->totalUsage, $obj->maxLimit, $obj->requestedFriends) : JText::sprintf('COM_ES_PROFILE_STATS_FRIENDS_COUNTER_UNLIMITED', $obj->totalUsage, $obj->requestedFriends);
			continue;
		}

		$obj->text_total = $obj->maxLimit ? JText::sprintf('COM_ES_PROFILE_STATS_COUNTER_MAX', $obj->totalUsage, $obj->maxLimit) : JText::sprintf('COM_ES_PROFILE_STATS_COUNTER_UNLIMITED', $obj->totalUsage);
	}
}

require($lib->getLayout());
