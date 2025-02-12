<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="queue-item is-queue t-hidden" data-uploaderQueue-item data-uploaderQueue-item-template>
	<div class="media">
		<div class="media-body">
			<div class="queue-item-info">
				<span class="queue-item-name" data-filename></span>
				<span class="queue-item-size"><span data-filesize></span><?php echo JText::_('COM_EASYSOCIAL_UNIT_KILOBYTES'); ?></span>
				<span class="queue-item-status" data-uploaderQueue-status><?php echo JText::_('COM_EASYSOCIAL_IN_QUEUE');?></span>
				<span class="queue-item-done"><?php echo JText::_('COM_EASYSOCIAL_DONE_BUTTON');?></span>
			</div>

			<div class="progress progress-success progress-striped" data-uploaderQueue-progress>
				<div class="bar" style="width: 0%" data-uploaderQueue-progressBar></div>
			</div>

			<a href="javascript:void(0);" class="attach-remove btn btn-es-default-o btn-sm pull-right" data-uploaderQueue-remove>x</a>
		</div>
	</div>
	<input type="hidden" name="upload-id[]" data-uploaderQueue-id />
</div>
