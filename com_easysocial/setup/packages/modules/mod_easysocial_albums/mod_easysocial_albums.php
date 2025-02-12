<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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
$config = ES::config();

// If photos is not enabled, do not proceed
if (!$config->get('photos.enabled')) {
	return;
}

// Load script to enable photo popup
$lib->renderComponentScripts();

// Module settings
$withCover = $params->get('withCover', 0);
$limit = $params->get('total', 6);
$userid = (int) $params->get('userid', 0);
$albumid = (int) $params->get('albumid', 0);

// By default we would set the includeCluster as true because following ES 1.4 behaviour.
$type = '';

$includeCluster = $params->get('includeCluster', 1);
if (!$includeCluster) {
	$type = SOCIAL_TYPE_USER;
}

$options = array();
$options['core'] = false;
$options['withCovers'] = $withCover;
$options['limit'] = $limit;
$options['order'] = 'created';
$options['direction'] = 'desc';
$options['excludeblocked'] = 1;
$options['privacy'] = true;
// $options['excludedisabled'] = true;

if ($userid) {
	$options['userId'] = (int) $userid;
}

if ($albumid) {
	$options['albumId'] = (int) $albumid;
}

// Retrieve recent albums from the site.
$model = ES::model('Albums');
$recentAlbums = $model->getAlbums('', $type, $options);

require($lib->getLayout());
