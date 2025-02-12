<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>600</width>
	<height>650</height>
	<selectors type="json">
	{
		"{createButton}"	: "[data-create-button]",
		"{updateButton}"	: "[data-update-button]",
		"{cancelButton}"	: "[data-cancel-button]",
		"{title}"			: "[data-schedule-title]",
		"{description}"		: "[data-schedule-description]",
		"{reminder}"		: "[data-schedule-reminder]",
		"{stream}"			: "[data-schedule-stream]",
		"{start}"			: "[data-schedule-start]",
		"{end}"				: "[data-schedule-end]",
		"{allDay}"			: "[data-schedule-allday]",
		"{id}"				: "[data-schedule-id]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_( 'APP_CALENDAR_CREATE_NEW_SCHEDULE_DIALOG_TITLE' ); ?></title>
	<content>
		<div>
			<p><?php echo JText::_('APP_CALENDAR_CREATE_NEW_SCHEDULE_DIALOG_INFO'); ?></p>

			<div class="o-form-horizontal">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'APP_CALENDAR_CREATE_NEW_SCHEDULE_TITLE'); ?>

					<div class="o-control-input">
						<?php echo $this->html('grid.inputbox', 'title', $calendar->title, 'title', array('data-schedule-title')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'APP_CALENDAR_CREATE_NEW_SCHEDULE_DESCRIPTION'); ?>

					<div class="o-control-input">
						<?php echo $this->html('grid.textarea', 'description', $calendar->description, '', array('data-schedule-description')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'APP_CALENDAR_CREATE_NEW_SCHEDULE_STARTDATE'); ?>

					<div class="o-control-input">
						<?php echo $this->html( 'form.calendar' , 'date_start' , $calendar->getStartDate()->toMySQL( false ) , 'date_start' , 'data-schedule-start' , true, $format); ?>
					</div>
				</div>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'APP_CALENDAR_CREATE_NEW_SCHEDULE_ENDDATE'); ?>

					<div class="o-control-input">
						<?php echo $this->html( 'form.calendar' , 'date_end' , $calendar->getEndDate()->toMySQL( false ) , 'date_end' , 'data-schedule-end' , true, $format); ?>
					</div>
				</div>

				<?php if (!$calendar->id && $params->get('stream_create', true) || ($calendar->id && $params->get('stream_update', true))) { ?>
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'APP_CALENDAR_PUBLISH_STREAM'); ?>

					<div class="o-control-input">
						<?php echo $this->html( 'grid.boolean' , 'stream' , true , 'stream' , 'data-schedule-stream'); ?>
					</div>
				</div>
				<?php } ?>

				<div class="o-form-group">
					<?php echo $this->html('form.label', 'APP_CALENDAR_ALL_DAY'); ?>

					<div class="o-control-input">
						<?php echo $this->html( 'grid.boolean' , 'allday' , $calendar->all_day , 'allday' , 'data-schedule-allday'); ?>
					</div>
				</div>

				<input type="hidden" name="id" value="<?php echo $calendar->id;?>" data-schedule-id />
			</div>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>

		<?php if (!$calendar->id) { ?>
		<button data-create-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('APP_CALENDAR_CREATE_BUTTON'); ?></button>
		<?php } else { ?>
		<button data-update-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_ES_UPDATE'); ?></button>
		<?php } ?>
	</buttons>
</dialog>
