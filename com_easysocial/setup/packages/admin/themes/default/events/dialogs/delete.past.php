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
	<width><?php echo $total <= 0 ? 450 : 600;?></width>
	<height><?php echo $total <= 0 ? 150 : 300;?></height>
	<selectors type="json">
	{
		"{submitButton}": "[data-submit-button]",
		"{cancelButton}": "[data-cancel-button]",
		"{info}": "[data-info]",
		"{deleting}": "[data-deleting]",
		"{progress}": "[data-progress]",
		"{progressBar}": "[data-progress-bar]",
		"{processed}": "[data-processed]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {
			this.total = <?php echo $total;?>;

			<?php if ($total <= 0) { ?>
			this.complete = true;
			<?php } ?>
		},

		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_ES_PURGE_PAST_EVENTS'); ?></title>
	<content>
		<div class="t-lg-mt--lg">
			<?php if ($total <= 0) { ?>
			<p>
				<?php echo JText::_('COM_ES_EVENTS_NOTHING_TO_PURGE'); ?>
			</p>
			<?php } ?>

			<?php if ($total > 0) { ?>
			<p data-info>
				<?php echo JText::sprintf('COM_ES_EVENTS_ITEMS_TO_PURGE', $total); ?>
			</p>

			<p class="t-hidden t-lg-mb--lg" data-deleting>
				<?php echo JText::_('COM_ES_EVENTS_DELETING_PAST_EVENTS'); ?>
			</p>

			<div class="progress t-hidden" data-progress>
				<div style="width: 0%;" class="progress-bar progress-bar-success" data-progress-bar>
					<span data-processed>0</span> / <?php echo $total;?>
				</div>
			</div>
			<?php } ?>
		</div>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-sm btn-es-default"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-submit-button type="button" class="btn btn-sm btn-es-primary"><?php echo $total > 0 ? JText::_('COM_ES_PROCEED') : JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>
