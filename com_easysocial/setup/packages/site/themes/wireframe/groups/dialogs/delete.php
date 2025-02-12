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
	<width>450</width>
	<height>150</height>
	<selectors type="json">
	{
		"{closeButton}": "[data-close-button]",
		"{submitButton}": "[data-submit-button]",
		"{form}": "[data-form-delete=group]",
		"{responseValue}": "[data-respond-value]"
	}
	</selectors>
	<bindings type="javascript">
	{
		clicked: false,

		"{closeButton} click": function() {
			this.parent.close();
		},

		"{submitButton} click" : function() {

			if (!this.clicked) {
				this.clicked = true;

				this.form().submit();
			}
		}
	}
	</bindings>
	<title><?php echo JText::sprintf('COM_EASYSOCIAL_GROUPS_DIALOG_CONFIRM_DELETE_TITLE', $group->getName()); ?></title>
	<content>
		<p class="t-lg-mt--md">
			<img src="<?php echo $group->getAvatar();?>" class="t-lg-ml--xl" align="right" />

			<?php echo JText::sprintf('COM_EASYSOCIAL_GROUPS_DIALOG_DELETE_CONTENT', $group->getName());?>
		</p>

		<form data-form-delete="group" method="post" action="<?php echo JRoute::_('index.php');?>">
			<?php echo $this->html('form.action', 'groups', 'delete'); ?>
			<?php echo $this->html('form.hidden', 'id', $group->id); ?>
		</form>
	</content>
	<buttons>
		<button type="button" class="btn btn-es-default btn-sm" data-close-button><?php echo JText::_('COM_ES_CANCEL');?></button>
		<button type="button" class="btn btn-es-danger btn-sm" data-submit-button><?php echo JText::_('COM_EASYSOCIAL_DELETE_GROUP_BUTTON');?></button>
	</buttons>
</dialog>
