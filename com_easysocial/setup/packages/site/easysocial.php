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

// Engine is required anywhere EasySocial is used.
require_once(JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php');

ES::checkEnvironment();

// Start collecting page objects.
ES::document()->start();

// Get app
$app = JFactory::getApplication();
$input = $app->input;

// Load foundry configuration
$config = ES::config();

// get the updated token
$renewToken = $input->get('renewToken', false, 'bool');
if ($renewToken) {
	echo ES::token();
	exit;
}

// Determines if this is a cronjob request
$cron = $input->get('cron', false, 'bool');
$crondata = $input->get('crondata', false, 'bool');
$cronmobile = $input->get('cronmobile', false, 'bool');
$shortcutManifest = $input->get('shortcutmanifest', false, 'bool');

if ($shortcutManifest) {
	ES::getShortcutManifest();
	exit;
}

// Dispatch emails if necessary
if ($config->get('email.pageload') && !$cron) {
	$cronLib = ES::cron();
	$cronLib->dispatchEmails();
}

// Process cron service here.
if ($cron == true) {
	$cronLib = ES::cron();
	$cronLib->execute();
	exit;
}

if ($crondata == true) {
	$cronLib = ES::cron();
	$cronLib->executeCronDownload();
	exit;
}

if ($cronmobile == true) {
	$cronLib = ES::cron();
	$cronLib->executeCronMobile();
	exit;
}

$view = $input->get('view', '', 'word');
$task = $input->get('task', 'display', 'cmd');
$controller	= $input->get('controller', '', 'word');

ES::import('site:/controllers/controller');

// Listen for ajax calls.
ES::ajax()->listen();

if (!empty($controller)) {
	$controller	= ESJString::strtolower($controller);

	// Import controller
	$state = ES::import('site:/controllers/' . $controller);

	if (!$state) {
		throw ES::exception(JText::sprintf('Invalid controller %1$s', $controller), 500);
	}
}

$class = 'EasySocialController' . ESJString::ucfirst($controller);

// Test if the object really exists in the current context
if (!class_exists($class)) {
	throw ES::exception(JText::sprintf('Invalid controller class %1$s' , $class), 500);
}

$controller = new $class();
$controller->execute($task);
$controller->redirect();

ES::document()->end();
