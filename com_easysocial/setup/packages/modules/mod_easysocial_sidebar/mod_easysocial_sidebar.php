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

// Sidebars should never be rendered on mobile device.
// The component should render what it needs to render for mobile device
$lib = ES::modules($module);

if ($lib->isMobile()) {
	return;
}

$helper = $lib->getHelper();
$supported = $helper->isSupportedView();

if (!$supported) {
	return;
}

$adapter = $helper->getAdapter($lib);

if (!$adapter) {
	return;
}

return $adapter->render();
