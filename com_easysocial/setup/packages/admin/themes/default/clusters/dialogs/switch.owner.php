<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-submit-form]",
		"{cancelButton}": "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function()
		{
			this.parent.close();
		},
		"{submitButton} click" : function()
		{
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_CLUSTERS_CONFIRM_OWNER_DIALOG_TITLE'); ?></title>
	<content>
		<form name="approveUser" method="post" action="index.php" data-submit-form>
			<p>
				<?php echo JText::sprintf('COM_ES_CLUSTERS_CONFIRM_OWNER_DIALOG_CONTENT', '<b>' . $user->getName() . '</b>');?>
			</p>

			<?php if ($type != 'audios' && $type != 'videos') { ?>
				<div class="o-form-group">
					<div class="o-checkbox">
						<input type="checkbox" id="giveAdminRights" class="mr-5" checked="checked" name="adminRights" value="1" />
						<label for="giveAdminRights"><?php echo JText::_('COM_ES_CLUSTERS_CHANGE_OWNERSHIP_ADMIN_RIGHTS');?></label>
					</div>
				</div>
			<?php } ?>

			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="<?php echo $type; ?>" />
			<input type="hidden" name="task" value="switchOwner" />
			<input type="hidden" name="<?php echo ES::token();?>" value="1" />
			<input type="hidden" name="userId" value="<?php echo $user->id;?>" />

			<?php foreach ($ids as $id){ ?>
			<input type="hidden" name="ids[]" value="<?php echo $id;?>" />
			<?php } ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SWITCH_OWNER_BUTTON'); ?></button>
	</buttons>
</dialog>
