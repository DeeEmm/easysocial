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
?>
<div class="o-grid o-grid--gutters">
	<?php echo $this->loadTemplate('fields/event/startend/display_control', array(
		'type' => 'start',
		'datetime' => $startDatetime,
		'datetimeGMT' => $startDatetimeGMT,
		'allowTimezone' => $params->get('allow_timezone'),
		'timezone' => $params->get('allow_timezone') ? $timezone : '',
		'dateFormat' => $dateFormat,
		'timezones' => $params->get('allow_timezone') ? $timezones : '',
		'searchable' => $searchable,
		'advancedsearchlink' => $advsearchLinkStartEnd));
	?>


	<?php if ($endDatetime) { ?>
	<?php echo $this->loadTemplate('fields/event/startend/display_control', array(
		'type' => 'end',
		'datetime' => $endDatetime,
		'datetimeGMT' => $endDatetimeGMT,
		'allowTimezone' => $params->get('allow_timezone'),
		'timezone' => $params->get('allow_timezone') ? $timezone : '',
		'dateFormat' => $dateFormat,
		'timezones' => $params->get('allow_timezone') ? $timezones : '',
		'searchable' => $searchable,
		'advancedsearchlink' => $advsearchLinkStartEnd));
	?>
	<?php } ?>
</div>
