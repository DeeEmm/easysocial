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
<div class="o-grid__cell es-field-event-startend-box" data-startend-box>
	<div class="es-field-box">
		<div class="es-field-box-header t-text--center">
			<i class="fa fa-calendar"></i> <?php echo JText::_('FIELDS_EVENT_STARTEND_' . strtoupper($type) . '_DATETIME'); ?>
		</div>
		<div class="o-loader o-loader--top"></div>
		<p class="t-text--center es-field-event-startend-datetime" data-date data-date-utc="<?php echo isset($datetimeGMT) && $datetimeGMT ? $datetimeGMT->toSql() : ''; ?>">
			<?php if ($searchable) { ?>
			<a href="<?php echo $advancedsearchlink; ?>"><?php echo $datetime->toFormat($dateFormat); ?></a>
			<?php } else { ?>
			<?php echo $datetime->toFormat($dateFormat); ?>
			<?php } ?>
		</p>

		<?php if ($allowTimezone) { ?>
		<div class="t-text--center">
			<a href="javascript:void(0);" class="btn btn-es-default btn-block btn-sm" data-popbox data-popbox-position="bottom-right" data-popbox-id="es">
				<span data-timezone="<?php echo $timezone; ?>"><?php echo $timezone; ?></span> &nbsp;<i class="fa fa-caret-down"></i>
			</a>
		</div>

		<div style="display: none" data-popbox-content>
			<div class="t-text--center">
				<div class="es-timezone-block">
					<select class="o-form-control input-sm es-timezone-select" data-timezone-select>
						<option value="local"><?php echo JText::_('FIELDS_USER_DATETIME_LOCAL_TIMEZONE'); ?></option>
						<option value="UTC" <?php echo $timezone == 'UTC' ? 'selected="selected"' : '';?>>UTC</option>
						<?php foreach ($timezones as $group => $zones) { ?>
						<optgroup label="<?php echo $group; ?>">
							<?php foreach ($zones as $zone) { ?>
							<option value="<?php echo $zone; ?>" <?php if ($timezone === $zone) { ?>selected="selected"<?php } ?>><?php echo $zone; ?></option>
							<?php } ?>
						</optgroup>
						<?php } ?>
					</select>
				</div>


				<div class="es-timezone-block es-timezone-reset" data-timezone-reset>
					<a href="javascript:void(0);"><?php echo JText::_('FIELDS_USER_DATETIME_TIMEZONE_RESET'); ?></a>
				</div>

				<div class="es-timezone-block es-timezone-my" data-timezone-local>
					<a href="javascript:void(0);"><?php echo JText::_('FIELDS_USER_DATETIME_TIMEZONE_USE_LOCAL'); ?></a>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
