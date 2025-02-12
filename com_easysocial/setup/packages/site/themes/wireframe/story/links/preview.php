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
<div class="es-story-link-item <?php echo $link && $link->images && isset($link->images[0]) ? 'has-images' : '';?>" data-story-link-item<?php echo isset($isEdit) && $isEdit ? ' data-story-link-edit' : ''; ?>>

	<?php if ($link->images && !$isEmbedExternalVideo) { ?>
		<div class="es-story-link-images" data-story-link-images>
			<?php $i = 0; ?>
			<?php foreach ($link->images as $image) { ?>
			<div class="es-story-link-image-wrapper <?php echo $i == 0 ? ' active' : '';?>" data-story-link-image-wrapper>
				<img class="es-story-link-image" src="<?php echo $image;?>" data-story-link-image />

				<div class="es-story-image-dimension" data-story-link-image-dimensions>
					<span data-image-width></span>
					<span>x</span>
					<span data-image-height></span>
				</div>
			</div>
			<?php $i++; ?>
			<?php } ?>
		</div>
	<?php } ?>


	<?php if ($link) { ?>
		<?php if ($isEmbedExternalVideo && isset($link->externalVideoProviderEmbed->html)) { ?>
			<?php echo $link->externalVideoProviderEmbed->html; ?>
		<?php } ?>

		<div class="es-story-link-col <?php echo $isEmbedExternalVideo ? 't-hidden' : ''; ?>">
			<h6 class="es-story-link-title" data-story-link-title data-default="<?php echo $link->title;?>"><?php echo $link->title;?></h6>

			<div class="es-story-link-title-textbox">
				<input type="text" class="es-story-link-title-textfield o-form-control" data-story-link-title-textfield placeholder="<?php echo JText::_('COM_EASYSOCIAL_STORY_ENTER_LINK_TITLE'); ?>" value="<?php echo $link->title;?>" />
			</div>

			<small class="es-story-link-url">
				<a href="<?php echo $link->url;?>" target="blank"><?php echo $link->url;?></a>
			</small>

			<p class="es-story-link-description <?php echo !$link->description ? 'no-description' : '';?>" data-story-link-description>
				<?php if ($link->description) { ?>
					<?php echo $link->description; ?>
				<?php } else { ?>
					<?php echo JText::_('COM_EASYSOCIAL_STORY_ENTER_LINK_DESCRIPTION'); ?>
				<?php } ?>
			</p>

			<div class="es-story-link-description-textbox">
				<textarea class="es-story-link-description-textfield o-form-control" data-story-link-description-textfield placeholder="<?php echo JText::_('COM_EASYSOCIAL_STORY_ENTER_LINK_DESCRIPTION'); ?>"><?php echo $link->description;?></textarea>
			</div>

			<?php if ($link->images && (count($link->images) > 1)) { ?>
				<div class="es-story-link-nav">
					<div class="o-btn-group">
						<button type="button" class="btn btn-es-default-o btn-sm" data-story-link-image-prev><i class="fa fa-caret-left"></i></button>
						<button type="button" class="btn btn-es-default-o btn-sm" data-story-link-image-next><i class="fa fa-caret-right"></i></button>
					</div>
					<span class="es-story-link-image-count">
						<span data-story-link-image-index>1</span><span>/</span><span data-story-link-image-total><?php echo count($link->images); ?></span>
					</span>
				</div>
			<?php } ?>

			<?php if ($allowRemoveThumbnail) { ?>
				<div class="o-checkbox">
					<input id="remove-thumbnail" type="checkbox" class="remove-thumbnail es-story-link-remove-image" data-story-link-remove-image />
					<label for="remove-thumbnail" class=""><?php echo JText::_('COM_EASYSOCIAL_STORY_LINK_DONT_SHOW_THUMBNAIL');?></label>
				</div>
			<?php } ?>

			<?php if ($link->video) { ?>
				<input type="hidden" name="videos_link" value="<?php echo $link->video;?>" data-story-link-video />
			<?php } ?>
		</div>

	<div class="es-story-link-remove-button" data-story-link-remove-button>
		 <i class="fa fa-times"></i>
	</div>
	<?php } ?>
</div>
