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

// Determine the ordering of the groups
$ordering = $params->get('ordering', 'ordering');

// Default options
$options = array();

// Limit the number of groups based on the params
$options['limit'] = $params->get('display_limit', 5);
$options['ordering'] = $ordering;
$options['state'] = SOCIAL_STATE_PUBLISHED;

$categories = ES::populateCategoriesTree(SOCIAL_TYPE_EVENT, array(), $options);

if (!$categories) {
	return;
}

$lib->addScript('script.js');

require($lib->getLayout());
