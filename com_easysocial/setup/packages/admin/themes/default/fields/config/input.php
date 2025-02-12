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

echo $this->html(
	'grid.inputbox',
	'config_' . $name,
	$value,
	'config_' . $name,
	[
		'data-fields-config-param',
		'data-fields-config-param-field-' . $name,
		'data-name="' . $name . '"',
		'class' => 'o-form-control'
	]
);
