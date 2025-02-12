<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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

// Get the layout to use.
$total = (int) $params->get('total', 10);
$popover = $params->get('popover', false);

$validateESAD = $params->get('exclude_esad', false);
$recentdays = $params->get('recentdays', 0);

// Get the layout to use.
$model = ES::model("Leaderboard");

// Should we exclude admin here
$config = ES::config();
$excludeAdmin = !$config->get('leaderboard.listings.admin');

$options = array('ordering' => 'points', 'limit' => $total, 'excludeAdmin' => $excludeAdmin, 'validateESAD' => $validateESAD, 'isPaginate' => false, 'recentdays' => $recentdays);

$users = $model->getLadder($options);

require($lib->getLayout());
