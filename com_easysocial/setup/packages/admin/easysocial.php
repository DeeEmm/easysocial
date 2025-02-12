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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$input = $app->input;

$exitInstallation = $input->get('exitInstallation', false, 'bool');

// Check if there's a file initiated for installation
$file = JPATH_ROOT . '/tmp/easysocial.installation';

if ($exitInstallation) {
	if (JFile::exists($file)) {
		JFile::delete($file);

		return $app->redirect('index.php?option=com_easysocial');
	}
}

// new
$install = $input->get('setup', false, 'bool');

if ($install) {
	// Determines if the installation is a new installation or old installation.
	$obj = new stdClass();
	$obj->new = false;
	$obj->step = 1;
	$obj->status = 'installing';

	$contents = json_encode($obj);

	if (!JFile::exists($file)) {
		JFile::write($file, $contents);
	}
}

$active = $input->get('active', 0, 'int');

// Check if there's a file initiated for installation
$installCompleted = $input->get('active') === 'complete';

if (JFile::exists($file) || $active || $installCompleted) {
	require_once(dirname(__FILE__) . '/setup/bootstrap.php');
	exit;
}

// Check if we need to synchronize the database columns
$sync = $input->get('sync', false, 'bool');

if ($sync) {
	$input->set('task', 'sync');
	$input->set('controller', 'easysocial');
}

// If for whatever reason, ES library still doesn't exist, we need to show proper message
$mainFile = JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php';

if (!JFile::exists($mainFile)) {
	include_once(__DIR__ . '/setup.html');

	return;
}

// Engine is required anywhere EasySocial is used.
require_once($mainFile);

ES::checkEnvironment();

// Start collecting page objects.
ES::document()->start();

// We need the base controller
ES::import('admin:/controllers/controller');

// Process AJAX calls
ES::ajax()->listen();

// Get the task
$task = $input->get('task', 'display', 'cmd');

// We treat the view as the controller. Load other controller if there is any.
$controller = $input->get('controller', '', 'word');

if (!empty($controller)) {
	$controller = ESJString::strtolower($controller);
	$state = ES::import('admin:/controllers/' . $controller);

	if (!$state) {
		throw ES::exception(JText::sprintf('COM_EASYSOCIAL_INVALID_CONTROLLER', $controller), 500);
	}
}

$class = 'EasySocialController' . ESJString::ucfirst($controller);

// Test if the object really exists in the current context
if (!class_exists($class)) {
	throw ES::exception(JText::sprintf('COM_EASYSOCIAL_INVALID_CONTROLLER_CLASS_ERROR', $class), 500);
}

$controller	= new $class();
$controller->execute($task);
$controller->redirect();

ES::document()->end();
