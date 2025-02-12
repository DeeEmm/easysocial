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

$my = ES::user();

if ($my->id) {
	return;
}

// Load up the module engine
$lib = ES::modules($module);
$lib->renderComponentScripts();


$config = ES::config();
$sso = ES::sso();

// Module settings
$profileId = $params->get('profile_id', $lib->getDefaultProfileId());
$registerType = $params->get('register_type', 'quick');
$splashImage = $params->get('splash_image_url', '/media/com_easysocial/images/bg-register-pattern.png');

// Since 2.0, we no longer allow them to configure which fields should appear.
$model = ES::model('Fields');

// Get a list of custom fields for quick registration
$fields = $model->getQuickRegistrationFields($profileId);

if (!empty($fields)) {
	ES::language()->loadAdmin();

	$fieldsLib = ES::fields();
	$session = JFactory::getSession();

	$registration = ES::table('Registration');
	$registration->load($session->getId());

	$data = $registration->getValues();
	$args = array(&$data, &$registration);

	$fieldsLib->trigger('onRegisterMini', SOCIAL_FIELDS_GROUP_USER, $fields, $args);
}

require($lib->getLayout());
