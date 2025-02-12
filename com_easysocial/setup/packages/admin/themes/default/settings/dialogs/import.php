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
		"{submitButton}"	: "[data-submit-button]",
		"{importForm}"		: "[data-import-settings-form]",
		"{cancelButton}"	: "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_( 'COM_EASYSOCIAL_SETTINGS_IMPORT_SETTINGS_DIALOG_TITLE' ); ?></title>
	<content>
		<form data-import-settings-form method="post" action="index.php" enctype="multipart/form-data">
			<p><?php echo JText::_( 'COM_EASYSOCIAL_SETTINGS_IMPORT_SETTINGS_DIALOG_CONFIRMATION' ); ?></p>

			<input name="settings_file" type="file" />
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="settings" />
			<input type="hidden" name="task" value="import" />
			<input type="hidden" name="page" value="<?php echo $page;?>" />
			<?php echo $this->html( 'form.token' ); ?>
		</form>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_IMPORT_SETTINGS_BUTTON'); ?></button>
	</buttons>
</dialog>
