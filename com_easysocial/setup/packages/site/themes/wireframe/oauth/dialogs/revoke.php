<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
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
		"{revokeButton}": "[data-revoke-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{revokeForm}": "[data-oauth-revoke-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},
		"{revokeButton} click" : function()
		{
			this.revokeForm().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_REVOKE_ACCESS_DIALOG_TITLE'); ?></title>
	<content>
		<form data-oauth-revoke-form action="<?php echo JRoute::_('index.php');?>" method="post">
			<p><?php echo JText::_('COM_EASYSOCIAL_REVOKE_ACCESS_DIALOG_DESC'); ?></p>

			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="oauth" />
			<input type="hidden" name="task" value="revoke" />
			<input type="hidden" name="client" value="<?php echo $client;?>" />
			<input type="hidden" name="callback" value="<?php echo $callback;?>" />
			<input type="hidden" name="<?php echo ES::token();?>" value="1" />
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-revoke-button type="button" class="btn btn-es-danger"><?php echo JText::_('COM_EASYSOCIAL_YES_PROCEED_BUTTON'); ?></button>
	</buttons>
</dialog>
