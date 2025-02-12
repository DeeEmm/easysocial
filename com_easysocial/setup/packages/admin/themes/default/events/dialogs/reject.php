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
	<width>450</width>
	<height>250</height>
	<selectors type="json">
	{
		"{submitButton}"  : "[data-reject-button]",
		"{cancelButton}"  : "[data-cancel-button]",
		"{textarea}"	: "[data-reject-message]",
		"{form}"		: "[data-reject-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init : function()
		{
			this.textarea().expandingTextarea();
		},
		"{cancelButton} click": function()
		{
			this.parent.close();
		},
		"{submitButton} click"	: function()
		{
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_EVENTS_REJECT_SELECTED_EVENT_DIALOG_TITLE'); ?></title>
	<content>
		<form name="rejectPage" method="post" action="index.php" data-reject-form>
			<p>
				<?php echo JText::_('COM_EASYSOCIAL_EVENTS_REJECT_SELECTED_EVENT_DIALOG_CONTENT');?>
			</p>
			<p style="min-height: 80px;">
				<textarea class="input-xlarge" name="reason" data-reject-message style="width: 100%;min-height: 80px;" placeholder="<?php echo JText::_('COM_EASYSOCIAL_EVENTS_REJECT_PLACEHOLDER_REASON');?>"></textarea>
			</p>
			
			<div class="t-lg-mt--xl">
				<div class="o-checkbox">
					<input type="checkbox" id="sendRejectEmail" name="email" value="1" />
					<label for="sendRejectEmail"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_REJECT_SEND_MAIL_TO_CREATOR');?></label>
				</div>

				<div class="o-checkbox">
					<input type="checkbox" id="deleteUser" name="delete" value="1" />
					<label for="deleteUser"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_REJECT_ALSO_DELETE_EVENT');?></label>
				</div>
			</div>


			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="events" />
			<input type="hidden" name="task" value="reject" />
			<input type="hidden" name="<?php echo ES::token();?>" value="1" />

			<?php foreach ($ids as $id) { ?>
			<input type="hidden" name="id[]" value="<?php echo $id; ?>" />
			<?php } ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-sm btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-reject-button type="button" class="btn btn-sm btn-es-danger"><?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON'); ?></button>
	</buttons>
</dialog>
