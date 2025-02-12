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

<?php if ($this->config->get('conversations.attachments.enabled')) { ?>
<div class="es-convo-composer__attachment t-hidden" data-es-attachment-form>
	<div class="es-convo-composer__attachment-note">
		<div id="uploaderDragDrop" class="es-upload-wrapper">
			<div class="es-upload-wrapper__note-pop">
				<a href="javascript:void(0);"
					data-popbox data-popbox-id="es"
					data-popbox-component="es-convo-composer-popbox"
					data-popbox-type="--convo-note"
					data-popbox-position="top-right"
					data-popbox-target="[data-es-convo-note-dropdown]"
					data-popbox-offset="4"
					data-popbox-toggle="click"
					>
					<i class="fa fa-info-circle"></i>
				</a>
				<div class="t-hidden" data-es-convo-note-dropdown>
					<div class="es-convo-popbox-content">
						<?php if ($this->config->get('conversations.attachments.maxsize')) { ?>
						<div class="es-convo-composer__attachment-note">
							<?php echo JText::_('COM_EASYSOCIAL_UPLOADER_TITLE');?>&nbsp;&#58;
							<span class=""><?php echo JText::_('COM_EASYSOCIAL_UPLOADER_MAX_SIZE');?>:
							<?php echo $this->config->get('conversations.attachments.maxsize');?><?php echo JText::_('COM_EASYSOCIAL_UNIT_MEGABYTES');?></span>
						</div>
						<?php } ?>

						<div class="es-convo-composer__attachment-note">
							<?php echo JText::_('COM_EASYSOCIAL_UPLOADER_ALLOWED_FILE_EXTENSION'); ?>&nbsp;&#58;
							<span class=""><?php echo ES::makeString($this->config->get('conversations.attachments.types'), ','); ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="es-upload-wrapper__note" data-uploader-form>
				<div class="es-upload-wrapper__note">
					<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_DROP_YOUR_FILE_HERE'); ?>
					<a href="javascript:void(0);" data-uploader-browse>
						 <?php echo JText::_('COM_EASYSOCIAL_UPLOADER_UPLOAD_FILES'); ?>
					</a>
				</div>

			</div>

			<div class="es-upload-wrapper__attachment-list">
				<div class="upload-queue" data-uploaderQueue></div>

				<div class="es-upload-wrapper__attachment-item queue-item is-queue t-hidden" data-uploaderQueue-item data-uploaderQueue-item-template>
					<div href="javascript:void(0);" class="es-upload-wrapper__attachment-remove" data-uploaderQueue-remove></div>
					<span class="es-upload-wrapper__attachment-item-name" data-filename></span>
					<span class="es-upload-wrapper__attachment-item-state t-text--success" data-upload-state data-error="<?php echo JText::_('COM_ES_ERROR');?>">
						<?php echo JText::_('COM_ES_UPLOADING');?> ...
					</span>
					<input type="hidden" name="upload-id[]" data-uploaderQueue-id />
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>
