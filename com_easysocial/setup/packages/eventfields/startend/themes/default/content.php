<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-startend
	data-error-start-required="<?php echo JText::_('FIELDS_EVENT_STARTEND_VALIDATION_DATETIME_START_REQUIRED', true);?>"
	data-error-end-required="<?php echo JText::_('FIELDS_EVENT_STARTEND_VALIDATION_DATETIME_END_REQUIRED', true);?>"
>
	<div class="o-grid o-grid--gutters">
		<div class="o-grid__cell t-xs-mb--lg">
			<div id="datetimepicker4" data-event-start>
				<div class="o-input-group">
					<input type="text" <?php echo $this->isMobile() ? 'readonly' : '';?> class="o-form-control" placeholder="<?php echo JText::_('FIELDS_EVENT_STARTEND_START_DATETIME'); ?>" data-picker />
					<span class="o-input-group__btn" data-picker-toggle>
						<span class="btn btn-es-default-o">
							<i class="far fa-calendar-alt"></i>
						</span>
					</span>
				</div>
				<input type="hidden" name="startDatetime" value="<?php echo $startDatetime; ?>" data-datetime />
				<input type="hidden" name="oriStartDatetime" value="<?php echo $startDatetime; ?>" />
			</div>
		</div>

		<div class="o-grid__cell">
			<div id="datetimepicker4" data-event-end>
				<div class="o-input-group">
					<input type="text" <?php echo $this->isMobile() ? 'readonly' : '';?> class="o-form-control" placeholder="<?php echo JText::_('FIELDS_EVENT_STARTEND_END_DATETIME' . (!$params->get('require_end') ? '_OPTIONAL' : '')); ?>" data-picker />
					<span class="o-input-group__btn" data-picker-toggle>
						<span class="btn btn-es-default-o">
							<i class="far fa-calendar-alt"></i>
						</span>
					</span>
				</div>
				<input type="hidden" name="endDatetime" value="<?php echo $endDatetime; ?>" data-datetime />
				<input type="hidden" name="oriEndDatetime" value="<?php echo $endDatetime; ?>" />
			</div>
		</div>
	</div>

	<?php if ($params->get('allow_timezone')) { ?>
	<div class="t-lg-mt--md">
		<select class="o-form-control" name="startendTimezone" data-event-timezone>
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
</div>
