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
	<width>600</width>
	<height>450</height>
	<selectors type="json">
	{
		"{createButton}": "[data-create-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{content}": "[data-notes-form-content]",
		"{noteTitle}": "[data-notes-form-title]",
		"{stream}": "[data-notes-publish-stream]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title>
		<?php if ($note->id) { ?>
			<?php echo JText::_('APP_NOTES_EDIT_NOTE_DIALOG_TITLE'); ?>
		<?php } else { ?>
			<?php echo JText::_('APP_NOTES_NEW_NOTE_DIALOG_TITLE'); ?>
		<?php } ?>
	</title>
	<content>
		<div class="t-lg-m--md">
			<div class="o-form-group">
				<label for="title"><?php echo JText::_('APP_NOTES_FORM_TITLE');?></label>

				<?php echo $this->html('grid.inputbox', 'title', $note->title, 'title', array('placeholder="' . JText::_('APP_NOTES_FORM_TITLE_PLACEHOLDER') . '"', 'data-notes-form-title')); ?>
			</div>

			<div class="o-form-group">
				<label for="title"><?php echo JText::_('APP_NOTES_FORM_CONTENTS');?></label>

				<?php echo $this->html('grid.textarea', 'content', $note->content, 'content', array('placeholder="' . JText::_('APP_NOTES_FORM_CONTENTS_PLACEHOLDER') . '"', 'data-notes-form-content', 'rows="8"')); ?>
			</div>

			<?php if (($params->get('stream_create', true) && !$note->id) || ($params->get('stream_update', true) && $note->id)) { ?>
			<div class="o-checkbox t-lg-mt--md">
				<input type="checkbox" name="publish_stream" value="1" checked="checked" class="mr-5" id="data-apps-notes-publish-stream" data-notes-publish-stream />
				<label for="data-apps-notes-publish-stream"><?php echo JText::_('APP_NOTES_FORM_PUBLISH_STREAM');?></label>
			</div>
			<?php } ?>

		</div>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-create-button type="button" class="btn btn-es-primary btn-sm">
			<?php if (!$note->id) { ?>
				<?php echo JText::_('APP_NOTES_PUBLISH_NOTE_BUTTON'); ?>
			<?php } else { ?>
				<?php echo JText::_('COM_ES_UPDATE'); ?>
			<?php } ?>
		</button>
	</buttons>
</dialog>
