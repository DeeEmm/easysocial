<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<style>
#es .es-photo-upload-item {
  position: relative;
}
#es .es-photo-upload-item .upload-title {
  font-weight: bold;
}
#es .es-photo-upload-item .upload-title > span {
  display: none;
}
#es .es-photo-upload-item.pending .upload-title-pending {
  display: inline-block;
}
#es .es-photo-upload-item.preparing .upload-title-preparing {
  display: inline-block;
}
#es .es-photo-upload-item.uploading .upload-title-uploading {
  display: inline-block;
}
#es .es-photo-upload-item.failed .upload-title-failed {
  display: inline-block;
}
#es .es-photo-upload-item.done .upload-title-done {
  display: inline-block;
}
#es .es-photo-upload-item .upload-status td {
  vertical-align: middle;
}
#es .es-photo-upload-item table {
  width: 100%;
  height: 100%;
  border-collapse: separate;
  table-layout: fixed;
}
#es .es-photo-upload-item .upload-details {
  display: none;
}
#es .es-photo-upload-item .upload-details td {
  height: 100%;
  vertical-align: top;
  padding: 8px;
  background: #f5f5f5;
  word-wrap: break-word;
}
#es .es-photo-upload-item.show-details .upload-details {
  display: table-row;
}
#es .es-photo-upload-item .upload-details-button {
  font-size: 90%;
  color: #428bca;
  cursor: pointer;
}
#es .es-photo-upload-item .upload-details-button:hover {
  text-decoration: underline;
}
#es .es-photo-upload-item .upload-filename {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
#es .es-photo-upload-item .upload-progress {
  margin-bottom: 3px;
  height: 18px;
}
#es .es-photo-upload-item .upload-filesize {
  font-size: 90%;
  text-align: right;
}
#es .es-photo-upload-item .upload-status td {
  padding: 8px;
}
#es .es-photo-upload-item .upload-remove-button {
  position: absolute;
  top: 8px;
  right: 4px;
  cursor: pointer;
}
#es .es-photo-upload-item .upload-remove-button i {
  font-size: 11px;
  color: #428bca;
}

</style>

<div class="o-form-group" data-album-view>
	<div class="o-control-input" data-album-content>
		<div data-album-upload-button class="t-lg-mb--lg">
			<a href="javascript:void(0);" class="btn btn-es-default-o">
			<i class="fas fa-upload"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_UPLOAD_BUTTON'); ?>
			</a>
		</div>
		<div data-photo-item-group class="es-embed-list">
			<?php if ($isEdit) { ?>
				<?php foreach($photos as $photo) { ?>
					<div data-photo-item
						 data-photo-edit="<?php echo isset($isEdit) ? $isEdit : '0'; ?>"
						 data-photo-id="<?php echo $photo->id; ?>"
						 class="es-embed-container">
						<div class="es-embed-container__action" data-photo-remove-button>
							<a href="javascript:void(0);" class="es-embed-container__remove" title="Remove">x</a>
						</div>
						<div class="embed-responsive embed-responsive-16by9">
							<img data-photo-image src="<?php echo $photo->getSource('large'); ?>" alt="" class="embed-responsive-item">
						</div>
					</div>
				<?php } ?>
			<?php } ?>

		</div>
		<?php if ($isEdit) { ?>
			<input id="<?php echo $inputName; ?>[removed]" type="hidden" name="<?php echo $inputName; ?>[removed]" value="" data-removed-photos/>
		<?php } ?>
		<input id="<?php echo $inputName; ?>[photoCount]" type="hidden" name="<?php echo $inputName; ?>[photoCount]" value="<?php echo $isEdit ? count($photos) : 0; ?>" data-photos-count/>

		<div class="es-fields-error-note" data-field-error></div>
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

