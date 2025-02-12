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
<?php if ($album->id && !$album->finalized && $this->my->id == $album->getCreator()->id) { ?>
<div class="o-alert o-alert--warning o-alert--icon" data-album-unfinalized-label>
	<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_UNFINALIZES_NOTICE_MESSAGE'); ?>
</div>
<?php } ?>

<div class="es-album-item es-media-group es-island <?php echo $photos ? 'has-photos' : ''; ?> <?php echo 'layout-' . $options['layout']; ?>"
	data-album-item="<?php echo $album->uuid(); ?>"
	data-album-id="<?php echo $album->id; ?>"
	data-album-nextstart="<?php echo isset($nextStart) ? $nextStart : '-1' ; ?>"
	data-album-layout="<?php echo $options['layout']; ?>"
	data-album-uid="<?php echo $lib->uid;?>"
	data-album-type="<?php echo $lib->type;?>">

	<div data-album-header class="es-media-header es-album-header">
		<?php if ($options['showToolbar']) { ?>
		<div class="es-media-header-affix-wrapper es-media-header-affix-wrapper--top" data-es-spy="affix" style="top: <?php echo $this->config->get('photos.layout.affix.offset'); ?>px;">
			<div class="o-media es-album-header__o-media">
				<?php $albumCreator = $album->getCreator(); ?>

				<div class="o-media__image<?php echo ($album->isUserAlbum() && $this->my->id == $albumCreator->id) ? ' t-visibility--hidden' : ''; ?>">
					<?php if ($albumCreator instanceof SocialPage) { ?>
					<?php echo $this->html('avatar.page', $albumCreator); ?>
					<?php } else {  ?>
					<?php echo $this->html('avatar.user', $albumCreator); ?>
					<?php } ?>
				</div>
				<div class="o-media__body">
					<div data-album-owner class="es-album-owner<?php echo ($album->isUserAlbum() && $this->my->id == $albumCreator->id) ? ' t-visibility--hidden' : ''; ?>">
						<?php echo JText::_("COM_EASYSOCIAL_ALBUMS_UPLOADED_BY"); ?>&nbsp;<?php echo $this->html('html.user', $album->getCreator()); ?>
					</div>
					<?php echo $this->includeTemplate('site/albums/layouts/menu'); ?>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->render('module', 'es-albums-before-info'); ?>

		<?php if ($options['showInfo']) { ?>
			<?php echo $this->includeTemplate('site/albums/layouts/info'); ?>
		<?php } ?>

		<?php if ($options['showForm'] && $lib->editable()) { ?>
			<?php echo $this->includeTemplate('site/albums/layouts/form'); ?>
		<?php } ?>

		<?php if ($lib->editable() && ($lib->isPhotoDeleteable() || $lib->isPhotoMoveable())) { ?>
		<div class="es-media-header-affix-wrapper" data-es-spy="affix" style="top: <?php echo !$this->config->get('photos.layout.affix.offset') ? 0 : $this->config->get('photos.layout.affix.offset') + 50; ?>px;">
			<div class="es-media-delete-all-actions"  >
				<div class="o-grid-sm o-grid-sm--center">
					<div class="o-grid-sm__cell">
						<div class="o-checkbox t-xs-ml--lg">
							<input type="checkbox" id="es-media-select-all-checkbox" data-photo-item-checkall>
							<label for="es-media-select-all-checkbox">
									<?php echo JText::_('COM_ES_SELECT_ALL'); ?>
							</label>
						</div>
					</div>
					<div class="o-grid-sm__cell o-grid-sm__cell--right">
						<div class="t-hidden" data-photo-actions-wrapper>
							<div class="o-select-group o-select-group--inline">
								<select class="o-form-control" data-photo-actions-task>
									<option><?php echo JText::_('COM_ES_BULK_ACTIONS'); ?></option>
									<?php if ($lib->isPhotoMoveable()) { ?>
									<option value="site/controllers/photos/move"
											data-confirmation="site/views/photos/confirmMove"
											data-trigger="photoMove">
											<?php echo JText::_('COM_EASYSOCIAL_MOVE_PHOTO_TO_ANOTHER_ALBUM'); ?>
									</option>
									<?php } ?>
									<?php if ($lib->isPhotoDeleteable()) { ?>
									<option value="site/controllers/photos/delete"
											data-confirmation="site/views/photos/confirmDelete"
											data-trigger="photoDelete">
											<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_DELETE_PHOTO'); ?>
									</option>
									<?php } ?>
								</select>
								<label for="" class="o-select-group__drop"></label>
							</div>

							<a href="javascript:void(0);" class="btn btn-es-primary-o" data-photo-actions-apply><?php echo JText::_('COM_ES_APPLY'); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>

	<div data-album-content class="es-album-content" data-es-photo-group="album:<?php echo $album->id; ?>">
		<?php echo $this->render('module', 'es-albums-before-photos'); ?>
		<?php if ($options['showPhotos']) { ?>
			<?php echo $this->includeTemplate('site/albums/layouts/photos'); ?>
		<?php } ?>
		<?php echo $this->render( 'module' , 'es-albums-after-photos' ); ?>
	</div>

	<?php if ($options['view'] != 'all') { ?>
	<div data-album-footer class="es-album-footer">
		<?php if ($options['showStats']) { ?>
			<?php echo $this->includeTemplate('site/albums/layouts/stats'); ?>
		<?php } ?>

		<div class="es-album-interaction o-row">

			<div class="es-album-showresponse o-col--8 o-col--top t-lg-pr--md t-xs-mb--lg t-xs-pr--no">
			<?php if ($options['showResponse']) { ?>
				<?php echo $this->includeTemplate('site/albums/layouts/response'); ?>
			<?php } ?>
			</div>

			<?php if ($options['showTags'] && $this->config->get('photos.tagging')) { ?>
			<div class="es-album-showtag o-col--4 o-col--top">
				<?php echo $this->includeTemplate('site/albums/layouts/tags'); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>

	<?php if ($options['showForm'] && $lib->editable()) { ?>
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
	<?php } ?>

	<div class="es-media-loader"></div>
</div>

