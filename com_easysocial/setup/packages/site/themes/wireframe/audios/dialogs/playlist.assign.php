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
	<width>450</width>
	<height>200</height>
	<selectors type="json">
	{
		"{insertButton}": "[data-insert-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{suggest}": "[data-audios-suggest]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {

			// Implement audio suggest.
			this.suggest()
				.addController("EasySocial.Controller.Audios.Suggest");
		},

		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_AUDIO_PLAYLIST_ADD_DIALOG_TITLE'); ?></title>
	<content>
		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-list-assignAudios>

			<div data-assignAudios-notice></div>

			<p class="t-lg-mb--xl"><?php echo JText::_('COM_ES_AUDIO_PLAYLIST_ADD_DIALOG_CONTENT'); ?></p>

			<div class="controls textboxlist disabled" data-audios-suggest>
				<input type="text" class="input-xlarge textboxlist-textField" name="audios" data-textboxlist-textField disabled />
			</div>

			<input type="hidden" name="id" value="<?php echo $list->id;?>" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-insert-button type="button" class="btn btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_ADD_BUTTON'); ?></button>
	</buttons>
</dialog>
