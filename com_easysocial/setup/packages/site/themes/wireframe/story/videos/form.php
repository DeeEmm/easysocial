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
<div class="es-story-video-form<?php echo ($isEdit && $video->id) ? ' is-processed' : ' is-waiting'; ?>" data-video-form data-allow-link="<?php echo $video->canEmbed() ? 'true' : 'false'; ?>">

	<div class="es-video-item-wrap video-result" data-result>
		<a class="es-video-item-remove" data-remove-video><i class="fa fa-times"></i></a>

		<div class="es-video-item es-media-item">
			<div class="es-video" data-video-preview-image>
			<?php if ($isEdit && $video->id) { ?>
				<img class="orientation-wide" src="<?php echo $video->getThumbnail(); ?>" />
			<?php } ?>
			</div>
		</div>
		<div class="es-video-item-content">
			<div class="es-video-item-title" data-video-preview-title><?php echo ($video->title) ? $video->title : JText::_('COM_EASYSOCIAL_STORY_VIDEOS_ENTER_TITLE');?></div>
			<div class="es-video-item-title-textbox">
				<input type="text"
					class="es-story-link-title-textfield o-form-control input-sm"
					data-video-title
					placeholder="<?php echo JText::_('COM_EASYSOCIAL_STORY_VIDEOS_TITLE_PLACEHOLDER'); ?>"
					value="<?php echo $this->html('string.escape', $video->title); ?>" />
			</div>

			<div class="es-video-item-desp <?php echo !$video->description ? 'no-description' : ''; ?>" data-video-preview-description><?php echo ($video->getDescription()) ? $video->getDescription() : JText::_('COM_EASYSOCIAL_STORY_VIDEOS_ENTER_DESC');?></div>
			<div class="es-video-item-desp-textbox">
				<textarea class="es-story-link-description-textfield o-form-control input-sm"
						data-video-description
						placeholder="<?php echo JText::_('COM_EASYSOCIAL_STORY_VIDEOS_DESC_PLACEHOLDER'); ?>"
						><?php echo $video->getDescription(); ?></textarea>
			</div>
			<div class="video-category">
				<div class="form-inline">
					<div class="form-group">
						<select name="video-category" class="o-form-control" data-video-category>
							<option value="0"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_SELECT_CATEGORY');?></option>
							<?php foreach ($categories as $category) { ?>
							<option value="<?php echo $category->id;?>"<?php echo (($video->category_id && $category->id == $video->category_id) || (!$video->category_id && $category->default)) ? ' selected="selected"' : ''; ?>><?php echo JText::_($category->title);?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>


	</div>

	<div class="es-story-video-progress-wrap video-upload-progress" style="text-align: center;" data-upload-progress>
		<span class="es-story-video-status-text"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_UPLOADING_YOUR_VIDEO');?> <span data-video-uploader-upload-text>0%</span></span>
		<div class="progress mb-5">
			<div class="bar" style="width: 0%" data-video-uploader-upload-bar></div>
		</div>
	</div>

	<?php if ($video->canUpload()) { ?>
	<div class="es-story-video-progress-wrap video-progress" data-progress>
		<span class="es-story-video-status-text"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_ENCODING_YOUR_VIDEO');?> <span data-video-uploader-progress-text>0%</span></span>
		<div class="progress mb-5">
			<div class="bar" style="width: 0%" data-video-uploader-progress-bar></div>
		</div>
	</div>
	<?php } ?>

	<div class="video-form">

		<?php if ($video->canUpload()) { ?>
		<div class="es-video-upload-container">
			<div data-video-uploader class="es-video-content">
				<div data-video-uploader-dropsite>
					<div data-video-uploader-button class="es-video-upload-button">
						<span>
							<b class="add-hint">
								<i class="fa fa-upload"></i> <?php echo ES::getUploadMessage('videos');?>
							</b>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ($video->canUpload() && $video->canEmbed()) { ?>
		<div class="es-video-form-divider">
			<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_OR_PASTE_VIDEO_LINK');?></span>
		</div>
		<?php } ?>

		<?php if ($video->canEmbed()) { ?>
		<div class="es-video-share-container">
			<div class="o-form-group">
				<div class="o-input-group">
					<input type="text" name="video_link" class="o-form-control" data-video-link placeholder="<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_LINK_PLACEHOLDER');?>" />
					<span class="o-input-group__btn">
						<button class="btn btn-es-default-o insert-button" type="button" data-insert-video>
							<i class="o-loader o-loader--sm"></i> <span><?php echo JText::_('COM_EASYSOCIAL_INSERT_VIDEO_BUTTON');?></span>
						</button>
					</span>

				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
