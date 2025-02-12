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
require_once(__DIR__ . '/helper.php');

ES::initialize();
ES::language()->loadSite();

$lib = ES::modules($module);

// Get the current logged in user object
$my = ES::user();

if (!$my->id && !$params->get('show_sign_in', true)){
	return;
}

// Get menu items
$items = array();
$config = ES::config();

$loginReturn = $lib->getLoginReturnUrl($params->get('loginreturn'));
$logoutReturn = $lib->getLogoutReturnUrl();
$showRememberMe = $params->get('remember_me_style', 'visible_checked') == 'visible_checked' || $params->get('remember_me_style') == 'visible';
$checkRememberMe = $params->get('remember_me_style', 'visible_checked') == 'visible_checked' || $params->get('remember_me_style') == 'hidden_checked';
$loginPlaceholder = $config->get('general.site.loginemail') ? 'COM_EASYSOCIAL_TOOLBAR_LOGIN_NAME_OR_EMAIL' : 'COM_EASYSOCIAL_TOOLBAR_LOGIN_NAME';

$sso = ES::sso();

if ($config->get('registrations.emailasusername')) {
	$loginPlaceholder = 'COM_EASYSOCIAL_TOOLBAR_EMAIL';
}

if ($params->get('render_menus', false)) {
	$items = ModEasySocialDropdownMenuHelper::getItems($params);
}

require($lib->getLayout());
