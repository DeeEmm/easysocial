<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
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
	<width>500</width>
	<height>200</height>
	<selectors type="json">
	{
		"{noButton}": "[data-no-button]",
		"{yesButton}": "[data-yes-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-alerts-form]",
		"{resetInput}": "[data-setting-reset]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_ALERTS_RESET_USERSETTINGS_DIALOG_TITLE'); ?></title>
	<content>
		<div class="clearfix">
			<form name="switchProfile" method="post" action="index.php" data-alerts-form>
				<p><?php echo JText::_('COM_EASYSOCIAL_ALERTS_RESET_USERSETTINGS_DIALOG_DESC');?><br /></p>

				<p><?php echo JText::_('COM_EASYSOCIAL_ALERTS_RESET_USERSETTINGS_DIALOG_INSTRUCTION');?></p>

				<input type="hidden" name="option" value="com_easysocial" />
				<input type="hidden" name="controller" value="alerts" />
				<input type="hidden" name="task" value="<?php echo $task; ?>" />
				<input type="hidden" name="reset" data-setting-reset value="0" />
				<input type="hidden" name="<?php echo ES::token();?>" value="1" />

				<?php foreach ($ids as $id) { ?>
				<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
				<?php } ?>
			</form>
		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-inverse btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-yes-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_ALERTS_RESET_USERSETTINGS_DIALOG_YES'); ?></button>
		<button data-no-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_ALERTS_RESET_USERSETTINGS_DIALOG_NO'); ?></button>
	</buttons>
</dialog>
