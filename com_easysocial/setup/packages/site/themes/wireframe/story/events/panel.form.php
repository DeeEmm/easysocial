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
?>
<?php if (!$category->hasPointsToCreate()) { ?>
	<div data-event-form-error class="o-alert o-alert--danger"><?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_INSUFFICIENT_POINTS', $category->getPointsToCreate()); ?></div>
<?php } else { ?>

	<div class="o-form-group">
		<input type="text" class="o-form-control" placeholder="<?php echo $titleField->get('title'); ?>" <?php echo $titleReadOnly ? 'disabled="disabled"' : ''; ?> value="<?php echo $titleField->default ? $titleField->default : ''; ?>" data-event-title />
	</div>

	<div class="o-form-group">
		<textarea name="description" id="description" class="o-form-control" placeholder="<?php echo $descriptionPlaceholder; ?>" data-event-description></textarea>
	</div>

	<div class="o-form-group" data-event-datetime-form data-yearfrom="<?php echo $yearfrom; ?>" data-yearto="<?php echo $yearto; ?>" data-allowtime="<?php echo $allowTime; ?>" data-allowtimezone="<?php echo $allowTimezone; ?>" data-dateformat="<?php echo $dateFormat; ?>" data-disallowpast="<?php echo $disallowPast; ?>" data-minutestepping="<?php echo $minuteStepping; ?>" data-dow="<?php echo $dow; ?>">
		<div class="o-row">
			<div class="o-col--6 t-lg-pr--md t-xs-pr--no t-xs-pb--lg">
				<div id="datetimepicker4" class="o-input-group" data-event-datetime="start">
					<input type="text" class="o-form-control" placeholder="<?php echo JText::_('FIELDS_EVENT_STARTEND_START_DATETIME'); ?>" data-picker />
					<input type="hidden" data-datetime />
					<span class="o-input-group__addon" data-picker-toggle>
						<i class="far fa-calendar-alt"></i>
					</span>
				</div>
			</div>

			<div class="o-col--6">
				<div id="datetimepicker4" class="o-input-group" data-event-datetime="end">
					<input type="text" class="o-form-control" placeholder="<?php echo JText::_('FIELDS_EVENT_STARTEND_END_DATETIME'); ?>" data-picker />
					<input type="hidden" data-datetime />
					<span class="o-input-group__addon" data-picker-toggle>
						<i class="far fa-calendar-alt"></i>
					</span>
				</div>
			</div>
		</div>
	</div>

	<?php if ($allowTimezone) { ?>
	<div class="o-form-group">
		<select class="o-form-control" data-event-timezone>
			<option value="UTC">UTC</option>

			<?php foreach ($timezones as $group => $zones) { ?>
				<optgroup label="<?php echo $group; ?>">
				<?php foreach ($zones as $zone) { ?>
					<option value="<?php echo $zone; ?>"><?php echo $zone; ?></option>
				<?php } ?>
				</optgroup>
			<?php } ?>
		</select>
	</div>
	<?php } ?>
	
<?php } ?>