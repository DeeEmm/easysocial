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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

$timeformat = $params->get('agenda_timeformat', '12') == '24' ? JText::_('COM_EASYSOCIAL_DATE_DMY24H') : JText::_('COM_EASYSOCIAL_DATE_DMY12H');

?>
<dialog>
	<width>600</width>
	<height>650</height>
	<selectors type="json">
	{
		<?php if ($user->isViewer()) { ?>
			"{deleteButton}" : "[data-delete-button]",
			"{editButton}" : "[data-edit-button]",
		<?php } else { ?>
			"{viewButton}" : "[data-view-button]",
		<?php } ?>
		"{cancelButton}" : "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>

	<title><?php echo $calendar->get('title'); ?></title>
	<content>
		<h2><?php echo $calendar->get('title'); ?></h2>
		<div class="pull-right">
			<?php echo ES::privacy()->form($calendar->id, 'calendar', $calendar->user_id, 'apps.calendar', false, $streamId, array(), array('linkStyle' => 'button')); ?>
		</div>
		<div class="event-time" style="color: #ccc;">
			<?php echo $calendar->getStartDate()->format($timeformat); ?> - <?php echo $calendar->getEndDate()->format($timeformat);?>
		</div>
		<hr />

		<p class="mt-20"><?php echo str_replace( "\n", '<br />', $calendar->get('description')); ?></p>
	</content>
	<buttons>
		<?php if ($user->isViewer()) { ?>
			<button data-delete-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_DELETE_BUTTON'); ?></button>
			<button data-edit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('APP_CALENDAR_EDIT_BUTTON'); ?></button>
		<?php } else { ?>
			<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
			<a href="<?php echo $app->getPermalink('canvas', array('userId' => $user->id, 'customView' => 'item', 'schedule_id' => $calendar->id));?>" class="btn btn-es-primary btn-sm"><?php echo JText::_('APP_CALENDAR_VIEW_BUTTON'); ?></a>
		<?php } ?>
	</buttons>
</dialog>
