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
		"{form}": "[data-form-delete=ad]",
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
	<title><?php echo JText::sprintf('COM_ES_AD_DIALOG_CONFIRM_DELETE_TITLE', $ad->title); ?></title>
	<content>
		<p class="t-lg-mt--md">
			<?php echo JText::sprintf('COM_ES_AD_DIALOG_DELETE_CONTENT', $ad->title);?>
		</p>

		<form data-form-delete="ad" method="post" action="<?php echo JRoute::_('index.php');?>">
			<?php echo $this->html('form.action', 'ads', 'delete'); ?>
			<?php echo $this->html('form.hidden', 'id', $ad->id); ?>
		</form>
	</content>
	<buttons>
		<button type="button" class="btn btn-es-default btn-sm" data-close-button><?php echo JText::_('COM_ES_CANCEL');?></button>
		<button type="button" class="btn btn-es-danger btn-sm" data-submit-button><?php echo JText::_('COM_ES_AD_DELETE_BUTTON');?></button>
	</buttons>
</dialog>
