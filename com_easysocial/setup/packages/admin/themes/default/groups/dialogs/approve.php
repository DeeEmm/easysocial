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
	<width>400</width>
	<height>150</height>
	<selectors type="json">
	{
		"{approveButton}"	: "[data-approve-button]",
		"{form}"			: "[data-approve-form]",
		"{cancelButton}"	: "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},
		"{approveButton} click" : function()
		{
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_( 'COM_EASYSOCIAL_GROUPS_APPROVE_DIALOG_TITLE' ); ?></title>
	<content>
		<form name="approveUser" method="post" action="index.php" data-approve-form>
		<p class="t-lg-mb--xl">
			<?php echo JText::_('COM_EASYSOCIAL_GROUPS_APPROVE_DIALOG_CONTENT');?>
		</p>

		<div class="o-form-group">
			<div class="o-checkbox">
				<input type="checkbox" id="sendConfirmationMail" class="mr-5" checked="checked" name="email" value="1" />
				<label for="sendConfirmationMail"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_APPROVE_DIALOG_SEND_EMAIL');?></label>
			</div>
		</div>

		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="groups" />
		<input type="hidden" name="task" value="approve" />
		<input type="hidden" name="<?php echo ES::token();?>" value="1" />

		<?php foreach( $ids as $id ){ ?>
		<input type="hidden" name="id[]" value="<?php echo $id; ?>" />
		<?php } ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-approve-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?></button>
	</buttons>
</dialog>
