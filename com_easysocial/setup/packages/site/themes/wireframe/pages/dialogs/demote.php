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
		"{closeButton}"	: "[data-close-button]",
		"{revokeAdminButton}": "[data-revoke-admin-button]",
		"{form}": "[data-demote-page-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function()
		{
			this.parent.close();
		},

		"{revokeAdminButton} click": function()
		{
			this.form().submit()
		} 
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PAGES_DIALOG_REVOKE_ADMIN_TITLE'); ?></title>
	<content>
		<p><?php echo JText::sprintf('COM_EASYSOCIAL_PAGES_DIALOG_REVOKE_ADMIN_CONTENT', $user->getName(), $user->getName());?></p>
		<form data-demote-page-form method="post" action="<?php echo JRoute::_('index.php');?>">
			<input type="hidden" name="id" value="<?php echo $page->id;?>" />
			<input type="hidden" name="userId" value="<?php echo $user->id;?>" />
			<input type="hidden" name="controller" value="pages" />
			<input type="hidden" name="task" value="demote" />
			<input type="hidden" name="return" value="<?php echo $return;?>" />
			<?php echo $this->html('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
		<button data-revoke-admin-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_REVOKE_ADMIN_BUTTON'); ?></button>
	</buttons>
</dialog>
