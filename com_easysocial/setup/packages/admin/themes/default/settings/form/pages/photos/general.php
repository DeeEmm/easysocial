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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'photos.enabled', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ENABLE_PHOTOS'); ?>
				<?php echo $this->html('settings.toggle', 'photos.tagging', 'COM_ES_PHOTOS_ENABLE_TAGGING'); ?>
				<?php echo $this->html('settings.toggle', 'photos.location', 'COM_ES_PHOTOS_SETTINGS_ALLOW_LOCATION'); ?>
				<?php echo $this->html('settings.toggle', 'photos.import.exif', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_IMPORT_EXIF_DATA', '','', 'COM_ES_PHOTOS_SETTINGS_IMPORT_EXIF_DATA_NOTE'); ?>
				<?php echo $this->html('settings.toggle', 'photos.original', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ALLOW_VIEW_ORIGINAL'); ?>
				<?php echo $this->html('settings.toggle', 'photos.downloads', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ALLOW_DOWNLOADS'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_PHOTO_PAGINATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'photos.pagination.photo', $this->config->get('photos.pagination.photo'), '', array('class' => 'input-short text-center')); ?>
						&nbsp;<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SETTINGS_PHOTO_PAGINATION_UNIT'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ENABLE_GIF_PHOTOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'photos.gif.enabled', $this->config->get('photos.gif.enabled'),'', array('data-toggle-gif')); ?>
					</div>
					<div class="col-md-7" data-gif-message>
						<div class="o-loader o-loader--sm o-loader--inline with-text"><?php echo JText::_('COM_EASYSOCIAL_VERIFYING_API_KEY'); ?></div>
						<div role="alert" class="o-alert o-alert--success o-alert--icon o-alert--dismissible t-hidden" data-gif-success-message>
							<button type="button" class="o-alert__close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
							<strong><?php echo JText::_('COM_EASYSOCIAL_API_KEY_VERIFIED'); ?></strong>
						</div>
						<div role="alert" class="o-alert o-alert--danger o-alert--icon t-hidden" data-gif-error-message><?php echo JText::_('COM_EASYSOCIAL_API_KEY_VERIFICATION_FAILED'); ?></div>

						<div class="o-alert o-alert--info">
							<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SETTINGS_GIF_PROCESSING_DESC'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_SETTINGS_IMAGE_OPTIMIZER'); ?>

			<div class="panel-body">
				<p><?php echo JText::_('COM_ES_SETTINGS_IMAGE_OPTIMIZER_INFO');?></p>

				<div class="t-lg-mb--md">
					<a href="https://stackideas.com/dashboard/optimizer" target="_blank" class="btn btn-sm btn-primary">View Plans</a>
				</div>

				<?php echo $this->html('settings.toggle', 'photos.optimizer.enabled', 'COM_ES_PHOTOS_ENABLE_IMAGE_OPTIMIZER'); ?>

				<?php echo $this->html('settings.textbox', 'photos.optimizer.key', 'COM_ES_PHOTOS_ENABLE_IMAGE_OPTIMIZER_KEY'); ?>

				<?php echo $this->html('settings.toggle', 'photos.optimizer.cron', 'COM_ES_PROCESS_OPTIMIZATION_CRON'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_UPLOADER'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PHOTOS_SETTINGS_UPLOAD_DRIVER'); ?>

					<div class="col-md-7">
						<div class="o-select-group">
							<select id="photos_uploader_driver" name="photos.uploader.driver" class="o-form-control">
								<option value="gd" <?php echo $this->config->get('photos.uploader.driver') == 'gd' ? ' selected="selected"' : '';?>><?php echo JText::_('GD Library'); ?></option>
								<option value="imagick" <?php echo !$imagickEnabled ? 'disabled' : ''; ?> <?php echo $this->config->get('photos.uploader.driver') == 'imagick' ? ' selected="selected"' : '';?>><?php echo JText::_('ImageMagick'); ?><?php echo !$imagickEnabled ? ' (extension disabled)' : ''; ?></option>
							</select>

							<label for="photos_uploader_driver" class="o-select-group__drop"></label>
						</div>
					</div>
				</div>


				<?php echo $this->html('settings.dropdown', 'photos.uploader.quality', 'COM_ES_PHOTOS_SETTINGS_UPLOAD_QUALITY', '', [
					'50' => 'Low',
					'70' => 'Medium',
					'90' => 'High',
					'100' => 'Highest'
				]); ?>

			</div>
		</div>
	</div>
</div>
