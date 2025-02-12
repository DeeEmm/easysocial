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

$app = JFactory::getApplication();
$input = $app->input;

// Ensure that the Joomla sections don't appear.
$input->set('tmpl', 'component');

// Determines if we are now in developer mode.
$developer = $input->get('developer', false, 'bool');

if ($developer) {
	$session = JFactory::getSession();
	$session->set('easysocial.developer', true);
}

############################################################
#### Constants
############################################################
$path = __DIR__;

define('SI_IDENTIFIER_SHORT', 'easysocial');
define('SI_LANG', 'COM_ES');
define('SI_CONTROLLER_PREFIX', 'EasySocialController');
define('SI_IDENTIFIER', 'com_' . SI_IDENTIFIER_SHORT);
define('SI_ADMIN', JPATH_ROOT . '/administrator/components/' . SI_IDENTIFIER);
define('SI_ADMIN_MANIFEST', SI_ADMIN . '/' . SI_IDENTIFIER_SHORT . '.xml');
define('SI_SETUP', SI_ADMIN . '/setup');
define('SI_PACKAGES', $path . '/packages');
define('SI_CONFIG', $path . '/config');
define('SI_THEMES', $path . '/themes');
define('SI_CONTROLLERS', $path . '/controllers');
define('SI_DOWNLOADER', 'https://stackideas.com/updater/services/download/' . SI_IDENTIFIER_SHORT);
define('SI_VERIFIER', 'https://stackideas.com/updater/verify');
define('SI_MANIFEST', 'https://stackideas.com/updater/manifests/' . SI_IDENTIFIER_SHORT);
define('SI_SETUP_URL', JURI::base() . 'components/' . SI_IDENTIFIER . '/setup');
define('SI_TMP', $path . '/tmp');
define('SI_BETA', false);
define('SI_KEY', '12345678901234567890123456789012');
define('SI_INSTALLER', 'full');

// Only when SI_PACKAGE is running on full package, the SI_PACKAGE should contain the zip's filename
define('SI_PACKAGE', 'com_easysocial_4.0.14_component_pro.zip');

// Get the current version
$contents = file_get_contents(SI_ADMIN_MANIFEST);
$parser = simplexml_load_string($contents);

$version = $parser->xpath('version');
$version = (string) $version[0];

define('SI_HASH', md5($version));

function t($constant)
{
	return JText::_(SI_LANG . '_' . $constant);
}

############################################################
#### Process controller
############################################################
$controller = $input->get('controller', '', 'cmd');
$task = $input->get('task', '');
$method = $input->get('method', '', 'cmd');

if (!empty($controller)) {

	$file = strtolower($controller) . '.' . strtolower($task) . '.php';
	$file = SI_CONTROLLERS . '/' . $file;

	require_once($file);

	$className = SI_CONTROLLER_PREFIX . ucfirst($controller) . ucfirst($task);
	$controller = new $className();

	if ($method && method_exists($controller, $method)) {
		return $controller->$method();
	}

	return $controller->execute();
}

############################################################
#### Initialization
############################################################
$contents = file_get_contents(SI_CONFIG . '/installation.json');
$steps = json_decode($contents);

############################################################
#### Workflow
############################################################
$active = $input->get('active', 0, 'default');

if ($active === 'complete') {
	$activeStep = new stdClass();

	$activeStep->title = JText::_('Installation Completed');
	$activeStep->template = 'complete';

	// Assign class names to the step items.
	if ($steps) {
		foreach ($steps as $step) {
			$step->className = ' done';
		}
	}

	// make sure we load our es lib here
	$libFile = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
	include_once($libFile);

	// check if system has unsynced media privacy. #3289
	$model = ES::model('Maintenance');
	$unsyncedPrivacyCount = $model->getMediaPrivacyCounts();

	// Remove installation temporary file
	JFile::delete(JPATH_ROOT . '/tmp/easysocial.installation');

} else {

	if ($active == 0) {
		$active = 1;
		$stepIndex = 0;
	} else {
		$active += 1;
		$stepIndex = $active - 1;
	}

	// Get the active step object.
	$activeStep = $steps[$stepIndex];

	// Assign class names to the step items.
	foreach ($steps as $step) {
		$step->className = $step->index == $active || $step->index < $active ? ' current' : '';
		$step->className .= $step->index < $active ? ' done' : '';
	}
}

require(SI_THEMES . '/default.php');
