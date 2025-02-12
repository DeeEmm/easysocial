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
		"{approveButton}"  : "[data-approve-button]",
		"{approveUserForm}"	: "[data-users-approve-form]",
		"{cancelButton}"  : "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_USERS_APPROVE_USER_DIALOG_TITLE'); ?></title>
	<content>
		<form name="approveUser" method="post" action="index.php" data-users-approve-form>
		<p>
			<?php echo JText::_( 'COM_EASYSOCIAL_USERS_APPROVE_CONFIRMATION' );?>
		</p>

		<div class="footnote">
			<label for="sendConfirmationMail" class="t-fs--sm">
				<input type="checkbox" id="sendConfirmationMail" class="mr-5" checked="checked" name="sendConfirmationEmail" value="1" />
				<span><?php echo JText::_( 'COM_EASYSOCIAL_USERS_APPROVE_SEND_EMAIL' );?></span>
			</label>
		</div>

		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="users" />
		<input type="hidden" name="task" value="approve" />
		<input type="hidden" name="<?php echo ES::token();?>" value="1" />

		<?php foreach( $ids as $id ){ ?>
		<input type="hidden" name="id[]" value="<?php echo $id; ?>" />
		<?php } ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-sm btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-approve-button type="button" class="btn btn-sm btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?></button>
	</buttons>
</dialog>
