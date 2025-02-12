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

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & Sh404sefFactory::getConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// Load up foundry library
$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
require_once($file);

// Include common methods
require_once(dirname(__FILE__)  . '/common.php');

ES::language()->loadSite();

$config = ES::config();

// remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');

if (!empty($Itemid)) {
	shRemoveFromGETVarsList('Itemid');
}

if (!empty($limit)) {
	shRemoveFromGETVarsList('limit');
}

if (!empty($limitstart)) {
	shRemoveFromGETVarsList('limitstart');
}

if (!empty($_ts)) {
	shRemoveFromGETVarsList('_ts');
}

// start by inserting the menu element title (just an idea, this is not required at all)
$task = isset($task) ? $task : null;
$Itemid = isset($Itemid) ? $Itemid : null;


// prepare the menu item view for later reference.
$menuView = '';
if ($Itemid) {
	$xMenu = JFactory::getApplication()->getMenu('site')->getItem($Itemid);
	$menuView = (isset($xMenu->query['view']) && $xMenu->query['view']) ? $xMenu->query['view'] : '';
}

// Get the component prefix that is configured in SH404
$prefix = shGetComponentPrefix($option);
$prefix = empty($prefix) ? getMenuTitle($option , $task , $Itemid , null , $shLangName) : $prefix;
$prefix = empty($prefix) || $prefix == '/' ? JText::_('COM_EASYSOCIAL_SH404_DEFAULT_ALIAS') : $prefix;


// let check if we need the menu alias or not.
$skipPrefix = false;


$isEasySocialUrlPluginInstalled = JPluginHelper::getPlugin('system', 'easysocialurl');
$isEasySocialUrlPluginEnabled = JPluginHelper::isEnabled('system', 'easysocialurl');

if ($isEasySocialUrlPluginInstalled && $isEasySocialUrlPluginEnabled) {

	$xUid = isset($uid) && $uid ? $uid : null;
	$xUtype = isset($type) && $type ? $type : null;

	$xView = isset($view) && $view ? preg_replace('/[^A-Z0-9_\.-]/i', '', $view) : '';
	$xUserid = isset($userid) && $userid ? $userid : '';
	$xId = isset($id) && $id ? $id : '';

	if ($xView == 'profile' && $xId) {
		$skipPrefix = true;
	}

	if (($xView == 'albums') && (($xUid && $xUtype && $xUtype == 'user' && !$xId) || $xUserid)) {
		$skipPrefix = true;
	}

	$xViews = array('apps', 'videos', 'friends', 'groups', 'events', 'pages', 'followers', 'points', 'badges');
	if (in_array($xView, $xViews) && (($xUid && $xUtype && $xUtype == 'user') || $xUserid)) {
		$skipPrefix = true;
	}
}

if (! $skipPrefix) {
	// Add the prefix
	addPrefix($title, $prefix);
}


// If view is set, pass the url builders to the view
if (isset($view)) {
	$adapter = dirname(__FILE__) . '/' . strtolower($view) . '.php';
	$adapterExists = JFile::exists($adapter);

	// Probably the view has some custom stuffs to perform.
	if ($adapterExists) {
		include($adapter);
	} else {

		$addView = true;
		$xView = '';
		$xLayout = '';

		if ($Itemid) {
			$xMenu = JFactory::getApplication()->getMenu('site')->getItem($Itemid);
			$xView = (isset($xMenu->query['view']) && $xMenu->query['view']) ? $xMenu->query['view'] : '';
			$xLayout = (isset($xMenu->query['layout']) && $xMenu->query['layout']) ? $xMenu->query['layout'] : '';
		}

		if ($xView && !$xLayout) {
			// Add the view to the list of titles
			addView($title, $view, $xView);
		} else {
			addView($title, $view);
		}

		// If layout is set, pass the url builders to the view
		if (isset($layout)) {
			addLayout($title , $view , $layout, $Itemid);
		}
	}
}

// Interesting stuffs
// NEW: ask sh404sef to create a short URL for this SEF URL (pageId)
// shMustCreatePageId('set', true);


// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef) {
	$string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString, (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), (isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------

