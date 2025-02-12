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
<dialog>
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{approveButton}": "[data-approve-button]",
		"{approveForm}": "[data-approve-event-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},
		"{approveButton} click" : function() {
			this.approveForm().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_EVENTS_DIALOG_APPROVE_INVITATION_REQUEST_TITLE'); ?></title>
	<content>
		<p><?php echo $message;?></p>

		<form data-approve-event-form method="post" action="<?php echo JRoute::_('index.php');?>">
			<input type="hidden" name="id" value="<?php echo $event->id;?>" />

			<?php foreach ($ids as $id) { ?>
			<input type="hidden" name="userId[]" value="<?php echo $id;?>" />
			<?php } ?>

			<input type="hidden" name="controller" value="events" />
			<input type="hidden" name="task" value="approveGuest" />
			<input type="hidden" name="return" value="<?php echo $return;?>" />
			<?php echo $this->html('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-approve-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?></button>
	</buttons>
</dialog>
