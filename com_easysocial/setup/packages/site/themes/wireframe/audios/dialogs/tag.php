<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
		"{submit}": "[data-submit-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{form}": "[data-dialog-form]",
		"{suggest}": "[data-friends-suggest]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {
			// Implement friend suggest.
			this.suggest()
				.addController("EasySocial.Controller.Friends.Suggest", {
					includeSelf: true,
					showNonFriend: false
					<?php if ($exclusion) { ?>
					,exclusion: <?php echo ES::json()->encode($exclusion); ?>
					<?php } ?>
					<?php if ($cluster && !$cluster->isOpen()) { ?>
					,clusterId: "<?php echo $cluster->id; ?>",
					clusterType: "<?php echo $cluster->getType(); ?>"
					<?php } ?>
				});
		},

		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_AUDIO_TAG_OTHERS_DIALOG_TITLE'); ?></title>
	<content>
		<p><?php echo JText::_('COM_ES_AUDIO_TAG_OTHERS_DIALOG_CONTENT'); ?></p>

		<div class="controls textboxlist disabled" data-friends-suggest>
			<input type="text" class="input-xlarge textboxlist-textField" name="members" data-textboxlist-textField disabled />
		</div>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_INSERT_TAGS_BUTTON'); ?></button>
	</buttons>
</dialog>
