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
?>
<div data-field-datetime class="form-inline" data-error-empty="<?php echo JText::_('PLG_FIELDS_DATETIME_VALIDATION_PLEASE_SELECT_DATETIME', true);?>">

	<?php if ($params->get('calendar')) { ?>
	<div class="es-field-datetime-form with-border with-calendar mb-5" data-field-datetime-form>
		<div class="es-field-datetime-textbox">
			<i class="fa fa-calendar" data-field-datetime-icon></i>
			<label for="<?php echo $inputName; ?>-datetime" class="t-hidden">Datetime</label>
			<input id="<?php echo $inputName; ?>-datetime" class="datepicker-wrap o-form-control" data-field-datetime-select data-date="<?php echo $date; ?>" type="text" placeholder="<?php echo JText::_($params->get('placeholder')); ?>" />
		</div>

		<div class="es-field-datetime-buttons">
			<a class="es-field-datetime-remove-button" href="javascript:void(0);" data-clear><i class="fa fa-times"></i></a>
		</div>
	</div>
	<?php } else { ?>
		<div class="o-grid o-grid--gutters">
		<?php foreach ($dateHTML as $html) { ?>
			<div class="o-grid__cell">
				<?php echo $html; ?>
			</div>
		<?php } ?>
		</div>

		<?php if ($params->get('allow_time')) { ?>
		<div class="es-field-datetime-form with-border mb-5">
			<?php echo $this->loadTemplate('fields/user/datetime/form.hour', array('hour' => $dateObject->isValid() ? $dateObject->format($params->get('time_format') == 1 ? 'g' : 'G') : -1, 'params' => $params)); ?>

			<?php echo $this->loadTemplate('fields/user/datetime/form.minute', array('minute' => $dateObject->isValid() ? $dateObject->minute : -1)); ?>

			<?php if ($params->get('time_format') == 1) { ?>
			<?php echo $this->loadTemplate('fields/user/datetime/form.ampm', array('value' => $dateObject->format('a'))); ?>
			<?php } ?>
		</div>

		<?php } ?>
	<?php } ?>

	<?php if ($params->get('allow_timezone')) { ?>
	<div class="mt-5">
		<select class="o-form-control"
			name="<?php echo $inputName; ?>[timezone]"
			data-field-datetime-timezone
			data-placeholder="<?php echo JText::_('FIELDS_USER_DATETIME_SELECT_TIMEZONE'); ?>">
			<option value="UTC" <?php if ($timezone == 'UTC') { ?>selected="selected"<?php } ?>>UTC</option>
			<?php foreach ($timezones as $group => $zones) { ?>
				<optgroup label="<?php echo $group; ?>">
				<?php foreach ($zones as $zone) { ?>
					<option value="<?php echo $zone; ?>" <?php if ($timezone == $zone) { ?>selected="selected"<?php } ?>><?php echo $zone; ?></option>
				<?php } ?>
				</optgroup>
			<?php } ?>
		</select>
	</div>

	<?php } ?>

	<input type="hidden" id="<?php echo $inputName; ?>-date" name="<?php echo $inputName; ?>[date]" value="<?php echo $date; ?>" data-field-datetime-value />

	<?php if ($yearPrivacy) { ?>
		<div class="data-field-datetime-yearprivacy t-lg-mt--md">
			<div class="t-pull-right">
				<?php echo ES::privacy()->form($field->id, 'year', $this->my->id, 'field.datetime');?>
			</div>
			<h4 class="es-title"><?php echo JText::_('PLG_FIELDS_DATETIME_YEAR_PRIVACY_TITLE'); ?></h4>
			<div class="t-fs--sm">
				<?php echo JText::_('PLG_FIELDS_DATETIME_YEAR_PRIVACY_INFO'); ?>
			</div>
		</div>
	<?php } ?>
</div>
