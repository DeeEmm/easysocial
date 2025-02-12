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
	<width>450</width>
	<height>150</height>
	<selectors type="json">
	{
		"{deleteButton}"	: "[data-delete-button]",
		"{cancelButton}" 	: "[data-cancel-button]",
		"{deleteForm}"		: "[data-delete-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_BADGES_DELETE_USER_BADGE_DIALOG_TITLE'); ?></title>
	<content>
	<div class="">
		<form name="assignBadge" method="post" action="index.php" data-delete-form>
			<p><?php echo JText::_('COM_EASYSOCIAL_BADGES_DELETE_USER_BADGE_MESSAGE'); ?></p>

			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="users" />
			<input type="hidden" name="task" value="removeBadge" />
			<input type="hidden" name="id" value="<?php echo $id;?>" />
			<input type="hidden" name="userid" value="<?php echo $userid;?>" />
			<?php echo $this->html('form.token'); ?>

		</form>
	</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-delete-button type="button" class="btn btn-es-danger"><?php echo JText::_('COM_EASYSOCIAL_DELETE_USER_BADGE_BUTTON'); ?></button>
	</buttons>
</dialog>
