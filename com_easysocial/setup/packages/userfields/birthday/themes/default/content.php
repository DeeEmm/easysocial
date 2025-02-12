<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-datetime>

	<?php if ($params->get('birthday_calendar')) { ?>
	<div class="es-field-datetime-form with-border with-calendar mb-5" data-field-datetime-form>
		<div class="es-field-datetime-textbox">
			<i class="fa fa-calendar" data-field-datetime-icon></i>
			<label for="birthday" class="t-hidden"><?php echo JText::_($params->get('placeholder')); ?></label>
			<input id="birthday" class="datepicker-wrap o-form-control input-sm" data-field-datetime-select data-date="<?php echo $date; ?>" type="text" placeholder="<?php echo JText::_($params->get('placeholder')); ?>" />
		</div>

		<div class="es-field-datetime-buttons">
			<a class="es-field-datetime-remove-button" href="javascript:void(0);" data-clear><i class="fa fa-times"></i></a>
		</div>
	</div>
	<?php } else { ?>
	<div class="o-grid o-grid--gutters">
		<?php foreach ($dateHTML as $type => $html) { ?>
			<?php if ($yearPrivacy && $type == 'year') { ?>
				<?php continue; ?>
			<?php } ?>
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
	<div class="t-lg-mt--md">
		<div class="o-select-group">
			<select
				class="o-form-control"
				name="<?php echo $inputName; ?>[timezone]"
				id="<?php echo $inputName; ?>_timezone"
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
			<label for="<?php echo $inputName; ?>_timezone" class="o-select-group__drop"></label>
		</div>
	</div>
	<?php } ?>

	<input type="hidden" id="<?php echo $inputName; ?>-date" name="<?php echo $inputName; ?>[date]" value="<?php echo $date; ?>" data-field-datetime-value />

	<?php if ($yearPrivacy) { ?>
	<div class="data-field-datetime-yearprivacy t-lg-mt--md">
		<div class="t-pull-right<?php echo !$params->get('birthday_calendar') ? ' t-lg-mt--lg t-lg-ml--xs' : '';?>">
			<?php echo ES::privacy()->form($field->id, 'birthday.year', $user->id, 'field.birthday.year');?>
		</div>
		<?php if (!$params->get('birthday_calendar')) { ?>
		<div class="t-pull-right t-lg-mt--lg">
			<?php echo $yearDropdown; ?>
		</div>
		<?php } ?>
		<h4 class="es-title"><?php echo JText::_('PLG_FIELDS_BIRTHDAY_YEAR_PRIVACY_TITLE'); ?></h4>
		<div class="t-fs--sm">
			<?php echo JText::_('PLG_FIELDS_BIRTHDAY_YEAR_PRIVACY_INFO'); ?>
		</div>
	</div>
	<?php } ?>

	<div class="es-fields-error-note" data-field-error></div>
</div>
