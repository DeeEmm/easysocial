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
<?php if (!empty($exceeded)) { ?>
<div class="fade in o-alert o-alert--warning o-alert--dismissible">
	<button data-story-attachment-clear-button class="o-alert__close" type="button">×</button>
	<strong><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_EXCEEDED');?></strong><br/><?php echo $exceeded ?>
</div>
<?php } else { ?>
<div data-album-view class="es-album-view es-media-group">
	<div data-album-content class="es-album-content">
		<div data-album-upload-button class="es-album-upload-button">
			<span>
				<b class="add-hint">
					<i class="fa fa-plus"></i>&nbsp; <?php echo JText::_("COM_EASYSOCIAL_STORY_ADD_PHOTO"); ?>
				</b>
				<b class="drop-hint">
					<i class="fa fa-upload"></i>&nbsp; <?php echo ES::getUploadMessage('photos'); ?>
				</b>
				</b>
			</span>
		</div>
		<div data-photo-item-group class="es-photo-item-group">
		<?php if (isset($data['photos']) && $data['photos']) { ?>
			<?php $isEdit = isset($edit) ? $edit : false; ?>
			<?php foreach ($data['photos'] as $photo) { ?>
				<?php echo $this->output('site/story/photos/attachment.item', array('photo' => $photo, 'isEdit' => $isEdit)); ?>
			<?php } ?>
		<?php } ?>
		</div>
	</div>

	<div class="t-hidden" data-uploader-template>
		<div id="" data-wrapper class="es-photo-upload-item es-photo-item">
			<div>
				<div>
					<table>
						<tr class="upload-status">
							<td>
								<div class="upload-title">
									<span class="upload-title-pending"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_PENDING'); ?></span>
									<span class="upload-title-preparing"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_PREPARING'); ?></span>
									<span class="upload-title-uploading"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_UPLOADING'); ?></span>
									<span class="upload-title-failed"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_FAILED'); ?> <span class="upload-details-button" data-upload-failed-link>(<?php echo JText::_('COM_EASYSOCIAL_UPLOAD_SEE_DETAILS'); ?>)</span></span>
									<span class="upload-title-done"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_DONE'); ?></span>
								</div>

								<div class="upload-filename" data-file-name></div>

								<div class="upload-progress progress progress-striped active">
									<div class="upload-progress-bar bar progress-bar-info" style="width: 0%"><span class="upload-percentage"></span></div>
								</div>

								<div class="upload-filesize"><span class="upload-filesize-total"></span> (<span class="upload-filesize-left"></span> <?php echo JText::_('COM_EASYSOCIAL_UPLOAD_LEFT'); ?>)</div>

								<div class="upload-remove-button"><i class="fa fa-times"></i></div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>
