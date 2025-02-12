<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
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

$jinput = JFactory::getApplication()->input;

$component = $jinput->get('option', false, 'cmd');
$view = $jinput->get('view', false, 'cmd');
$layout = $jinput->get('layout', false, 'cmd');
$type = $jinput->get('type', SOCIAL_TYPE_USER, 'default');

if (($component != 'com_easysocial' && $view != 'search' && $layout != 'advanced') || $type != SOCIAL_TYPE_USER) {
	return;
}


$lib = ES::modules($module);

// add module js script
$lib->addScript('script.js');

// Get the current logged in user object
$my = ES::user();
$config = ES::config();

$count = $params->get('count', 50);
$uniqueKey = $params->get('addresskey', 'ADDRESS');

// Load up helper file
require_once(dirname(__FILE__) . '/helper.php');

// load ES langauge
ES::language()->loadAdmin();
ES::language()->loadSite();

$mapItems = EasySocialModAdvancedSearchMapHelper::getItems($uniqueKey, $count, $lib);

$frmLatitude = '';
$frmLongitude = '';

require($lib->getLayout());



