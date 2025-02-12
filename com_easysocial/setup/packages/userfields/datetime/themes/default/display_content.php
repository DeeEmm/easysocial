<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($params->get('allow_timezone')) { ?>
<div class="pull-right t-lg-mr--md">
	<div class="profile-data-timezone" data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_('FIELDS_USER_DATETIME_TOGGLE_TIMEZONE'); ?>">
		<a href="javascript:void(0);" class="profile-data-timezone-toggle btn btn-es btn-notext" data-popbox data-popbox-position="bottom-right">
			<i class="far fa-clock"></i>
			<span class="caret"></span>
		</a>
	</div>

	<div style="display: none" data-popbox-content>
		<div class="text-center">
			<div class="es-timezone-block">
				<select class="o-form-control es-timezone-select" data-timezone-select>
					<option value="local"><?php echo JText::_('FIELDS_USER_DATETIME_LOCAL_TIMEZONE'); ?></option>
					<option value="UTC" <?php if ($timezone === 'UTC') { ?>selected="selected"<?php } ?>>UTC</option>
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
</div>
<?php } ?>

<div style="display: none;" data-loading><?php echo JText::_('FIELDS_USER_DATETIME_TIMEZONE_CHECKING'); ?></div>

<div data-date data-date-utc="<?php echo $dateObject->toSql(); ?>">
	<?php echo (isset($advancedsearchlink) && $advancedsearchlink) ? '<a href="' . $advancedsearchlink . '">' : ''; ?>
	<?php echo $date; ?>
	<?php echo (isset($advancedsearchlink) && $advancedsearchlink) ? '</a>' : ''; ?>
</div>

<?php if ($params->get('allow_timezone')) { ?>
<div data-timezone="<?php echo $timezone; ?>">
	<?php echo $timezone; ?>
</div>
<?php } ?>

<?php if ($allowYearSettings) { ?>
	<div class="data-field-datetime-yearprivacy t-lg-mt--md">
		<div class="t-pull-right">
			<?php echo ES::privacy()->form($field->id, 'year', $this->my->id, 'field.datetime');?>
		</div>
		<h4 class="es-title"><?php echo JText::_('PLG_FIELDS_DATETIME_YEAR_PRIVACY_TITLE'); ?></h4>
		<div>
			<?php echo JText::_('PLG_FIELDS_DATETIME_YEAR_PRIVACY_INFO'); ?>
		</div>
	</div>
<?php } ?>
