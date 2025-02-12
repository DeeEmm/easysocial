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
<div data-event-field-recurring class="o-form-horizonal">
	<select name="<?php echo $inputName; ?>[type]" data-recurring-type class="o-form-control es-recurring-select">
		<option value="none" <?php if (empty($value) || empty($value->type) || $value->type == 'none') { ?>selected="selected"<?php } ?>><?php echo JText::_('FIELD_EVENT_RECURRING_TYPE_NONE'); ?></option>
		<option value="daily" <?php if ($value->type == 'daily') { ?>selected="selected"<?php } ?>><?php echo JText::_('FIELD_EVENT_RECURRING_TYPE_DAILY'); ?></option>
		<option value="monthly" <?php if ($value->type == 'monthly') { ?>selected="selected"<?php } ?>><?php echo JText::_('FIELD_EVENT_RECURRING_TYPE_MONTHLY'); ?></option>
		<option value="yearly" <?php if ($value->type == 'yearly') { ?>selected="selected"<?php } ?>><?php echo JText::_('FIELD_EVENT_RECURRING_TYPE_YEARLY'); ?></option>
	</select>

	<div class="help-block" data-recurring-daily-block <?php if ($value->type !== 'daily') { ?>style="display: none;"<?php } ?>>

		<div class="es-recurring-daily-list">
			<?php foreach ($weekdays as $weekday) { ?>
			<div class="checkbox">
				<label>
					<input type="checkbox" name="<?php echo $inputName; ?>[daily][]" value="<?php echo $weekday['key']; ?>" <?php if (!empty($value->daily) && in_array($weekday['key'], $value->daily)) { ?>checked="checked"<?php } ?> />
						<?php echo $weekday['value']; ?>
				</label>
			</div>
			<?php } ?>
		</div>
	</div>

	<div class="t-lg-mb--xl" data-recurring-end-block <?php if (empty($value->type) || $value->type === 'none') { ?>style="display: none;"<?php } ?>>
		<div class="o-form-inline">
			<div class="o-form-group">
				<label><?php echo JText::_('FIELD_EVENT_RECURRING_END'); ?>:</label>
			</div>

			<div class="o-form-group">
				<div class="o-row">
					<div class="o-col--4">
						<div class="o-input-group">
							<input class="o-form-control" type="text" data-recurring-end-picker />
							<span class="o-input-group__addon" data-recurring-end-toggle="">
								<i class="fa fa-calendar"></i>
							</span>
						</div>
						<input type="hidden" name="<?php echo $inputName; ?>[end]" data-recurring-end-result value="<?php echo isset($value->end) ? $value->end : ''; ?>" />
						<input type="hidden" name="<?php echo $inputName; ?>[recurringUpdated]" data-recurring-list-updated value="0" />
					</div>

					<div class="o-col--8 t-lg-ml--md">
						<div class="help-block t-lg-mt--xl is-loading" data-recurring-schedule-loading-block>
							<?php echo $this->html('html.loading'); ?>
						</div>

						<div data-recurring-summary-block style="display: none"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-fields-error-note" data-field-error></div>
</div>
