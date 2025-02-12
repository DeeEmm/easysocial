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

// Get the current logged in user
$my = ES::user();

// If module is configured to display pages from logged in user, ensure that the user is logged in
if ($params->get('filter') == '3' && $my->guest) {
	return;
}

$lib = ES::modules($module);

// Only load the script if the action button is enabled
if ($params->get('display_actions', true)) {
	$lib->renderComponentScripts();
}

// Load up helper file
require_once(__DIR__ . '/helper.php');

$pages = EasySocialModPagesHelper::getPages($params);

if (!$pages) {
	return;
}

$coverSize = $params->get('cover_size', 'thumbnail');

require($lib->getLayout());
