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
	<height>180</height>
	<selectors type="json">
	{
		"{selection}"		: "[data-move-selection]",
		"{confirmButton}"	: "[data-move-confirm]",
		"{cancelButton}"	: "[data-move-cancel]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click" : function() {
			EasySocial.dialog().close();
		},
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_TITLE'); ?></title>
	<content>
		<?php if (!$steps) { ?>
		<p><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_NO_PAGE'); ?></p>
		<?php } else { ?>
		<p><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_SELECT_PAGE'); ?></p>
		<div>
			<select class="form-control" data-move-selection>
				<?php foreach ($steps as $step) { ?>
				<option value="<?php echo $step->id;?>"><?php echo $step->_('title');?></option>
				<?php } ?>
			</select>
		</div>
		<?php } ?>
	</content>
	<?php if ($steps) { ?>
	<buttons>
		<button data-move-cancel type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_CANCEL'); ?></button>
		<button data-move-confirm type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_MOVE_FIELD_BUTTON'); ?></button>
	</buttons>
	<?php } ?>
</dialog>
