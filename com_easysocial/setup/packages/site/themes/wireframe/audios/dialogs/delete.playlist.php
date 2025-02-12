<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
		"{deleteButton}": "[data-delete-button]",
		"{cancelButton}": "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_AUDIO_PLAYLIST_DELETE_DIALOG_TITLE'); ?></title>
	<content>
		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-playlist-delete-form>
			<p><?php echo JText::sprintf('COM_ES_AUDIO_CONFIRM_DELETE_PLAYLIST', $list->_('title')); ?></p>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="audios" />
			<input type="hidden" name="task" value="deletePlaylist" />
			<input type="hidden" name="id" value="<?php echo $list->id;?>" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-delete-button type="button" class="btn btn-es-danger btn-sm"><?php echo JText::_('COM_EASYSOCIAL_YES_PLEASE_DELETE_LISTS_BUTTON'); ?></button>
	</buttons>
</dialog>
