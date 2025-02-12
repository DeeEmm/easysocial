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

// Include the engine file.
require_once($engine);
require_once(__DIR__ . '/helper.php');

$lib = ES::modules($module);

if (!$lib->my->id) {
	return;
}

$lib->addScript('script.js');
$year = $params->get('display_year', false);
$fieldKey = $params->get('fieldkey', 'BIRTHDAY');
$limit = (int) $params->get('limit', 10);

$showFriendOnlyWithoutPrivacy = $params->get('show_friends_only', false);
$options = ['showFriendOnlyWithoutPrivacy' => $showFriendOnlyWithoutPrivacy];

$model = ES::model('Users');
$limitstart = 0;
$birthdays = $model->getUpcomingBirthdays($fieldKey, $lib->my->id, $limit, null, $limitstart, $options);
$pagination = $model->getPagination(true);
$paginationData = $pagination->getData();

if (!$birthdays) {
	return;
}

$birthdays = EasySocialModBirthdaysHelper::format($birthdays, $params);

require($lib->getLayout());
