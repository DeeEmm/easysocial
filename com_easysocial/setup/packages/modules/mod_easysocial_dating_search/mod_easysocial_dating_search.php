<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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

// add module js script
$lib->addScript('script.js');

// Get the current logged in user object
$my = ES::user();
$config = ES::config();

// Module settings
$withCover = $params->get('withCover' , 0);
$limit = $params->get('total' , 6);

$defaultSort = $params->get('searchsorting', 'default');

// Load up helper file
require_once(dirname(__FILE__) . '/helper.php');

// Get fields available
$fields = EasySocialModDatingSearchHelper::getFields($params);

if (!$fields) {
	return;
}

// Get values from posted data
$jinput = JFactory::getApplication()->input;
$values = array();
$values['criterias'] = $jinput->get('criterias', null, 'default');
$values['datakeys'] = $jinput->get('datakeys', null, 'default');
$values['operators'] = $jinput->get('operators', null, 'default');
$values['conditions'] = $jinput->get('conditions', null, 'default');

$userData = array();

if ($values['criterias']) {
	for ($i = 0; $i < count($values['criterias']); $i++) {
		$criteria = $values['criterias'][$i];
		$condition = $values['conditions'][$i];
		$datakey = $values['datakeys'][$i];

		$field  = explode('|', $criteria);

		$fieldCode  = $field[0];
		$fieldType  = $field[1];

		if ($fieldType == 'address' && $datakey == 'distance') {
			$addressData = explode('|', $condition);
			$userData[$fieldType]['distance'] = isset($addressData[0]) ? $addressData[0] : '';
			$userData[$fieldType]['latitude'] = isset($addressData[1]) ? $addressData[1] : '';
			$userData[$fieldType]['longitude'] = isset($addressData[2]) ? $addressData[2] : '';
			$userData[$fieldType]['address'] = isset($addressData[3]) ? $addressData[3] : '';
		}

		$userData[$fieldType]['condition'] = $condition;
	}
}

$fieldName = false;
$fieldGender = false;
$fieldBirthday = false;
$fieldAddress = false;
$fieldGenderOptions = array();
// relationshop
$fieldRelationship = false;
$fieldRelationshipeOptions = array();

if ($params->get('searchname', 1)) {
	if ($config->get('users.displayName') == 'realname' && isset($fields['joomla_fullname'])) {
		$fieldName = $fields['joomla_fullname'];
		$fieldName->title = 'MOD_EASYSOCIAL_DATING_SEARCH_NAME_TITLE';
		$fieldName->placeholder = 'MOD_EASYSOCIAL_DATING_SEARCH_NAME_PLACEHOLDER';
	}

	if ($config->get('users.displayName') == 'username' && isset($fields['joomla_username'])) {
		$fieldName = $fields['joomla_username'];
		$fieldName->title = 'MOD_EASYSOCIAL_DATING_SEARCH_USERNAME_TITLE';
		$fieldName->placeholder = 'MOD_EASYSOCIAL_DATING_SEARCH_USERNAME_PLACEHOLDER';

	}
}

if ($params->get('searchgender', 1) && isset($fields['gender'])) {
	$fieldGender = $fields['gender'];
	$fieldGender->data = (isset($userData[$fieldGender->element]['condition'])) ? $userData[$fieldGender->element]['condition'] : '';

	$genderParams = json_decode($fieldGender->params);
	$fieldGender->custom = isset($genderParams->custom) ? $genderParams->custom : false;
	$fieldGenderOptions = EasySocialModDatingSearchHelper::getGenderOptions($fieldGender);
}

if ($params->get('searchrelation', 1) && isset($fields['relationship'])) {
	$fieldRelationship = $fields['relationship'];
	$fieldRelationship->data = (isset($userData[$fieldRelationship->element]['condition'])) ? $userData[$fieldRelationship->element]['condition'] : '';
	$fieldRelationshipeOptions = EasySocialModDatingSearchHelper::getRelationshipOptions($fieldRelationship);
}

if ($params->get('searchage', 1) && isset($fields['birthday'])) {
	$fieldBirthday = $fields['birthday'];

	$start = '';
	$end = '';
	$dates = (isset($userData[$fieldBirthday->element]['condition'])) ? $userData[$fieldBirthday->element]['condition'] : '';

	if ($dates) {
		$userDates = explode('|', $dates);
		$start = $userDates[0];
		$end = (isset($userDates[1])) ? $userDates[1] : '';
	}

	$fieldBirthday->start = $start;
	$fieldBirthday->end = $end;
	$fieldBirthday->dates = $dates;
}

if ($params->get('searchdistance', 1) && isset($fields['address'])) {
	$fieldAddress = $fields['address'];
	$searchUnit = $config->get('general.location.proximity.unit','mile');
}

require($lib->getLayout());
