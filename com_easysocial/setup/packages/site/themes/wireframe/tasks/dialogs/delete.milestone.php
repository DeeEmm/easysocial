<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
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
	<height>100</height>
	<selectors type="json">
	{
		"{deleteButton}": "[data-delete-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-delete-milestone-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},
		"{deleteButton} click": function() {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('APP_EVENT_TASKS_CONFIRM_DELETE_MILESTONE_DIALOG_TITLE'); ?></title>
	<content>
		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-delete-milestone-form>
			<p><?php echo JText::_('APP_EVENT_TASKS_CONFIRM_DELETE_MILESTONE_DIALOG_DESC'); ?></p>
			<input type="hidden" name="id" value="<?php echo $id;?>" />
			<input type="hidden" name="return" value="<?php echo $return;?>" />
			<?php echo $this->html('form.action', 'tasks', 'deleteMilestone'); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-delete-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_DELETE_BUTTON'); ?></button>
	</buttons>
</dialog>
