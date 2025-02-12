<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
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

$my = ES::user();

// If module is configured to display listings from logged in user, ensure that the user is logged in
if ($params->get('filter') == '3' && $my->guest) {
	return;
}

$lib = ES::modules($module);

// Load up helper file
require_once(__DIR__ . '/helper.php');

$listings = EasySocialModMarketplaceHelper::getListings($params);

if (!$listings) {
	return;
}

require($lib->getLayout());
