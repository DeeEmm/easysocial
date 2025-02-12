<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-story-audio-form<?php echo ($isEdit && $audio->id) ? ' is-processed' : ' is-waiting'; ?>" data-audio-form data-allow-link="<?php echo $audio->canEmbed() ? 'true' : 'false'; ?>">

	<div class="es-audio-item-wrap audio-result" data-result>
		<a class="es-audio-item-remove" data-remove-audio><i class="fa fa-times"></i></a>

		<div class="es-audio-item es-media-item">
			<div class="es-audio" data-audio-preview-image>
				<?php if ($isEdit && $audio->id) { ?>
					<img class="orientation-wide" src="<?php echo $audio->getAlbumArt(); ?>" />
				<?php } ?>
			</div>
		</div>
		<div class="es-audio-item-content">
			<div class="es-audio-item-title" data-audio-preview-title><?php echo ($audio->title) ? $audio->title : JText::_('COM_ES_AUDIO_STORY_ENTER_TITLE');?></div>
			<div class="es-audio-item-title-textbox">
				<input type="text"
					class="es-story-link-title-textfield o-form-control input-sm"
					data-audio-title
					placeholder="<?php echo JText::_('COM_ES_AUDIO_STORY_TITLE_PLACEHOLDER'); ?>"
					value="<?php echo $this->html('string.escape', $audio->title); ?>" />
			</div>

			<div class="es-audio-item-artist" data-audio-preview-artist><?php echo ($audio->artist) ? $audio->artist : JText::_('COM_ES_AUDIO_STORY_ENTER_ARTIST');?></div>
			<div class="es-audio-item-artist-textbox">
				<input type="text"
					class="es-story-link-artist-textfield o-form-control input-sm"
					data-audio-artist
					placeholder="<?php echo JText::_('COM_ES_AUDIO_STORY_ARTIST_PLACEHOLDER'); ?>"
					value="<?php echo $this->html('string.escape', $audio->artist); ?>" />
			</div>

			<div class="es-audio-item-album" data-audio-preview-album><?php echo ($audio->album) ? $audio->album : JText::_('COM_ES_AUDIO_STORY_ENTER_ALBUM');?></div>
			<div class="es-audio-item-album-textbox">
				<input type="text"
					class="es-story-link-album-textfield o-form-control input-sm"
					data-audio-album
					placeholder="<?php echo JText::_('COM_ES_AUDIO_STORY_ALBUM_PLACEHOLDER'); ?>"
					value="<?php echo $this->html('string.escape', $audio->album); ?>" />
			</div>

			<div class="es-audio-item-desp <?php echo !$audio->description ? 'no-description' : ''; ?>" data-audio-preview-description><?php echo ($audio->description) ? $audio->description : JText::_('COM_ES_AUDIO_STORY_ENTER_DESC');?></div>
			<div class="es-audio-item-desp-textbox">
				<textarea class="es-story-link-description-textfield o-form-control input-sm"
					data-audio-description
					placeholder="<?php echo JText::_('COM_ES_AUDIO_STORY_DESC_PLACEHOLDER'); ?>"
					value="<?php echo $audio->description; ?>"
					></textarea>
			</div>
			<div class="audio-genre">
				<div class="form-inline">
					<div class="form-group">
						<select name="audio-genre" class="o-form-control" data-audio-genre>
							<option value="0"><?php echo JText::_('COM_ES_AUDIO_STORY_SELECT_GENRE_FOR_AUDIO');?></option>
							<?php foreach ($genres as $genre) { ?>
							<option value="<?php echo $genre->id;?>"<?php echo (($audio->genre_id && $genre->id == $audio->genre_id) || (!$audio->genre_id && $genre->default)) ? ' selected="selected"' : ''; ?>><?php echo JText::_($genre->title);?></option>
							<?php } ?>
						</select>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="es-story-audio-progress-wrap audio-upload-progress" style="text-align: center;" data-upload-progress>
		<span class="es-story-audio-status-text"><?php echo JText::_('COM_ES_AUDIO_UPLOADING_YOUR_AUDIO');?> <span data-audio-uploader-upload-text>0%</span></span>
		<div class="progress mb-5">
			<div class="bar" style="width: 0%" data-audio-uploader-upload-bar></div>
		</div>
	</div>

	<?php if ($audio->canUpload()) { ?>
	<div class="es-story-audio-progress-wrap audio-progress" data-progress>
		<span class="es-story-audio-status-text"><?php echo JText::_('COM_ES_AUDIO_ENCODING_YOUR_AUDIO');?> <span data-audio-uploader-progress-text>0%</span></span>
		<div class="progress mb-5">
			<div class="bar" style="width: 0%" data-audio-uploader-progress-bar></div>
		</div>
	</div>
	<?php } ?>

	<div class="audio-form">

		<?php if ($audio->canUpload()) { ?>
		<div class="es-audio-upload-container">
			<div data-audio-uploader class="es-audio-content">
				<div data-audio-uploader-dropsite>
					<div data-audio-uploader-button class="es-audio-upload-button">
						<span>
							<b class="add-hint">
								<i class="fa fa-upload"></i> <?php echo ES::getUploadMessage('audios');?>
							</b>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php if ($audio->canUpload() && $audio->canEmbed()) { ?>
		<div class="es-audio-form-divider">
			<span><?php echo JText::_('COM_ES_AUDIO_OR_PASTE_AUDIO_LINK');?></span>
		</div>
		<?php } ?>

		<?php if ($audio->canEmbed()) { ?>
		<div class="es-audio-share-container">
			<div class="o-form-group t-lg-mb--no">
				<div class="o-input-group">
					<input type="text" name="audio_link" class="o-form-control" data-audio-link placeholder="<?php echo JText::sprintf('COM_ES_AUDIO_FORM_SUPPORTED_PROVIDERS', $supportedProviders);?>" />
					<span class="o-input-group__btn">
						<button class="btn btn-es-default-o insert-button" type="button" data-insert-audio>
							<i class="o-loader o-loader--sm"></i> <span><?php echo JText::_('COM_ES_AUDIO_INSERT_AUDIO_BUTTON');?></span>
						</button>
					</span>

				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>


