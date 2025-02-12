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
<div class="form-uploader filesForm fileUploader">
	<?php echo $this->html('html.snackbar', JText::_('COM_EASYSOCIAL_UPLOADER_TITLE') . ' (' . JText::sprintf('COM_EASYSOCIAL_MAXIMUM_FILESIZE', $size) . ')', 'h2'); ?>

	<div>
		<?php echo JText::sprintf('COM_EASYSOCIAL_ALLOWED_FILE_EXTENSIONS', ES::makeString($this->config->get('conversations.attachments.types'), ',')); ?>
	</div>

	<div class="upload-queue" data-uploaderQueue></div>

	<div id="uploaderDragDrop">
		<div class="upload-submit" data-uploader-form>
			
			<button class="btn btn-es-default-o" data-uploader-browse>
				<i class="fa fa-upload"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_UPLOADER_UPLOAD_FILES'); ?>
			</button>

			<span class="t-lg-ml--md t-text--muted">
				<span>
					<?php echo JText::_('COM_EASYSOCIAL_UPLOADER_OR_DROP_YOUR_FILES'); ?>
				</span>
			</span>
		</div>
	</div>
</div>

<?php echo $this->html('uploader.queue'); ?>
