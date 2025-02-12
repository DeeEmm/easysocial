<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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
	<height>100</height>
	<selectors type="json">
	{
		"{revertButton}": "[data-revert-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-revert-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		},
		
		"{revertButton} click" : function() {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_THEMES_CONFIRM_REVERT_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::_('COM_EASYSOCIAL_THEMES_CONFIRM_REVERT_DIALOG_CONTENTS'); ?></p>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-revert-form>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="themes" />
			<input type="hidden" name="task" value="revert" />
			<input type="hidden" name="id" value="<?php echo $id;?>" />
			<input type="hidden" name="element" value="<?php echo $element;?>" />
			<?php echo $this->html('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-revert-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_REVERT_BUTTON'); ?></button>
	</buttons>
</dialog>
