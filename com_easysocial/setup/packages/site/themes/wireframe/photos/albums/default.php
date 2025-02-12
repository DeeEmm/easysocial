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
<div class="layout-<?php echo $options['layout'];?> es-media-item es-photo-item<?php echo $photo->isFeatured() ? ' featured' : '';?>"
	data-photo-item="<?php echo $photo->uuid(); ?>"
	data-photo-id="<?php echo $photo->id; ?>"
	<?php if ($options['openInPopup']) { ?>
	data-es-photo="<?php echo $photo->id; ?>"
	<?php } ?>
>
	<div>
		<div>
			<?php if ($album->editable()) { ?>
			<div class="es-media-checkbox" data-photo-checkbox-wrapper>
				<div class="o-checkbox">
					<input type="checkbox" id="es-media-select-checkbox-<?php echo $photo->uuid(); ?>" value="<?php echo $photo->id; ?>" data-photo-item-checkbox>
					<label for="es-media-select-checkbox-<?php echo $photo->uuid(); ?>">&nbsp;</label>
				</div>
			</div>
			<?php } ?>
			<div class="es-media-header es-photo-header" data-photo-header>

				<?php if ($options['showToolbar']) { ?>
				<div class="o-media o-media--top">
					<div class="o-media__image">
						<?php echo $this->html('avatar.user', $creator); ?>
					</div>
					<div class="o-media__body">
						<div class="es-photo-owner" data-photo-owner>
							<?php echo $this->html('html.user', $creator); ?>
						</div>
						<div data-photo-album class="es-photo-album"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_FROM_ALBUM"); ?> <a href="<?php echo $album->getPermalink(); ?>"><?php echo $album->get('title'); ?></a></div>
						<?php echo $this->includeTemplate('site/photos/menu'); ?>
					</div>
				</div>
				<?php } ?>

				<?php echo $this->render('module', 'es-photos-before-info'); ?>

				<?php if ($options['showInfo']) { ?>
					<?php echo $this->includeTemplate('site/photos/info'); ?>
				<?php } ?>

				<?php if ($options['showForm'] && $album->editable()) { ?>
				<?php echo $this->includeTemplate('site/photos/form'); ?>
				<?php } ?>
			</div>

			<div data-photo-content class="es-photo-content">
				<?php echo $this->render('module', 'es-photos-before-photo'); ?>
				<div class="es-photo <?php echo $options['resizeUsingCss'] ? 'css-resizing' : ''; ?>">
					<a data-photo-image-link
					   href="<?php echo ($options['layout'] == 'dialog') ? 'javascript:void(0);' : $photo->getPermalink(); ?>"
					   title="<?php echo $this->html('string.escape', $photo->title . (($photo->caption!=='') ? ' - ' . $photo->caption : '')); ?>">
						<u data-photo-viewport>
							<b data-mode="<?php echo $options['resizeMode']; ?>"
							   data-threshold="<?php echo $options['resizeThreshold'] ?>">
								<img data-photo-image
									 src="<?php echo $photo->getSource($options['size']); ?>"
									 data-thumbnail-src="<?php echo $photo->getSource('thumbnail'); ?>"
									 data-featured-src="<?php echo $photo->getSource('featured'); ?>"
									 data-large-src="<?php echo $photo->getSource('large'); ?>"
									 data-width="<?php echo $photo->getWidth(); ?>"
									 data-height="<?php echo $photo->getHeight(); ?>"
									 onload="window.ESImage ? ESImage(this) : (window.ESImageList || (window.ESImageList=[])).push(this);" />
							</b>
							<em class="es-photo-image" style="background-image: url('<?php echo $photo->getSource($options['size']); ?>');" data-photo-image-css></em>
							<?php if ($options['showNavigation']) { ?>
								<?php echo $this->includeTemplate('site/photos/navigation'); ?>
							<?php } ?>
						</u>
					</a>
					<?php if ($lib->taggable()) { ?>
					<div class="es-photo-hint tag-hint alert">
						<?php echo JText::_("COM_EASYSOCIAL_PHOTOS_TAGS_HINT"); ?>
						<button class="btn btn-es-default" href="javascript: void(0);" data-photo-tag-button="disable">
							<i class="fa fa-check"></i> <span><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_TAGS_DONE"); ?></span>
						</button>
					</div>
					<?php } ?>
				</div>
				<?php if ($options['showTags']) { ?>
					<?php echo $this->includeTemplate('site/photos/tags'); ?>
				<?php } ?>

				<div class="o-loader o-loader--top"></div>
				<?php echo $this->render('module', 'es-photos-after-photo'); ?>
			</div>

			<div data-photo-footer class="es-photo-footer">
				<?php if ($options['showStats']) { ?>
					<?php echo $this->includeTemplate('site/photos/stats'); ?>
				<?php } ?>

				<?php echo $this->render('module', 'es-photos-after-stats'); ?>

				<div class="o-row">
					<div class="o-col--8 t-lg-pr--md t-xs-mb--lg t-xs-pr--no">
						<?php if ($options['showResponse']) { ?>
							<?php echo $this->includeTemplate('site/photos/response'); ?>
						<?php } ?>
					</div>

					<?php if ($this->config->get('photos.tagging')) { ?>
					<div class="o-col--4 o-col--top">
						<?php echo $this->render('module', 'es-photos-before-tags'); ?>

						<?php if ($options['showTags']) { ?>
							<?php echo $this->includeTemplate('site/photos/taglist'); ?>

							<div data-tag-form-wrapper>
								<div class="es-photo-tag-item layout-form" data-photo-tag-item data-photo-tag-position>
									<div class="es-photo-tag-title"><span data-photo-tag-title></span></div>
									<div class="es-photo-tag-form">
										<i></i>
										<div>
											<fieldset>
												<input data-photo-tag-input type="text"
													   class="o-form-control es-photo-tag-input"
													   placeholder="<?php echo JText::_("COM_EASYSOCIAL_WHO_IS_THIS"); ?>" />
												<span data-photo-tag-remove-button class="es-photo-tag-remove-button"><i class="fa fa-times"></i></span>
											</fieldset>
											<div class="es-photo-tag-menu" data-photo-tag-menu></div>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>

						<?php echo $this->render('module', 'es-photos-after-tags'); ?>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="es-media-loader"></div>
		</div>
	</div>
</div>
