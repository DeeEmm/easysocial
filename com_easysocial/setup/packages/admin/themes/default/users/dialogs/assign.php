<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>550</width>
	<height>200</height>
	<selectors type="json">
	{
		"{assignButton}"  : "[data-assign-button]",
		"{cancelButton}"  : "[data-cancel-button]",
		"{assignForm}"		: "[data-users-assign-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_USERS_ASSIGN_USER_GROUP_DIALOG_TITLE'); ?></title>
	<content>
		<form name="approveUser" method="post" action="index.php" data-users-assign-form>
			<p>
				<?php echo JText::_('COM_EASYSOCIAL_USERS_ASSIGN_USER_GROUP_CONFIRMATION');?>
			</p>

			<div class="form-group">
				<label for="total" class="col-md-3"><?php echo JText::_('COM_EASYSOCIAL_USERS_SELECT_USER_GROUP');?></label>
				<div class="col-md-9">
					<?php echo $this->html('form.usergroups' , 'gid'); ?>
				</div>
			</div>

			<?php foreach ($ids as $id) { ?>
			<input type="hidden" name="cid[]" value="<?php echo $id;?>" />
			<?php } ?>

			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="users" />
			<input type="hidden" name="task" value="assign" />
			<?php echo $this->html( 'form.token' ); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-assign-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_ASSIGN_BUTTON'); ?></button>
	</buttons>
</dialog>
