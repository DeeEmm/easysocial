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

// Load up helper file
require_once(__DIR__ . '/helper.php');

// check if operation hour field installed or not
$file = JPATH_ROOT . '/media/com_easysocial/apps/fields/page/hours/hours.php';
$fileExits = JFile::exists($file);

if (!$fileExits || !EasySocialModPageSearchHelper::hasHourField()) {
	return;
}

// Get the current logged in user
$my = ES::user();


$lib = ES::modules($module);
$lib->renderComponentScripts();

// add module js script
$lib->addScript('script.js');

$catIds = $params->get('category');
$authorIds = $params->get('author', array());
$ordering = $params->get('ordering', 'latest');

$categories = EasySocialModPageSearchHelper::getCategories($catIds);
$authors = EasySocialModPageSearchHelper::getCreators($authorIds);
$days = EasySocialModPageSearchHelper::getDays();
$hours = EasySocialModPageSearchHelper::getHours();

$allCategoryIds = $catIds ? implode(',', $catIds) : '';
$allAuthorsIds = $authorIds ? implode(',', $authorIds) : '';

// inputs from user.
$input = ES::request();
$pagecategory = $input->get('pagecategory', '', 'default');
$pagecreator = $input->get('pagecreator', '', 'default');
$pagedays = $input->get('day', array(), 'default');
$pagestart = $input->get('hourstart', '', 'default');
$pageend = $input->get('hourend', '', 'default');

// format user data
$data = EasySocialModPageSearchHelper::formatData($pagedays, $pagestart, $pageend);

require($lib->getLayout());
