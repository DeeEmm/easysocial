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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'video.enabled', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_ENABLE_VIDEOS'); ?>
				<?php echo $this->html('settings.toggle', 'video.uploads', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_ALLOW_VIDEO_UPLOADS'); ?>
				<?php echo $this->html('settings.toggle', 'video.embeds', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_ALLOW_VIDEO_EMBEDS'); ?>
				<?php echo $this->html('settings.toggle', 'video.counters', 'COM_ES_VIDEOS_SETTINGS_ENABLE_SIDEBAR_COUNTERS'); ?>
				<?php echo $this->html('settings.toggle', 'video.dates', 'COM_ES_VIDEOS_SETTINGS_ENABLE_ASSIGNED_DATES'); ?>
				<?php echo $this->html('settings.dropdown', 'video.sortbehavior', 'COM_ES_VIDEO_DEFAULT_SORTING_BEHAVIOR', '', [
					'created' => 'Sort by Creation Date (Default)',
					'assigned_date' => 'Sort by Assigned Date (Based on the date field)'
				]); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel <?php echo !$this->config->get('video.enabled') ? 't-hidden' : '';?>" data-video-encoding>
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_ENCODING'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_FFMPEG_PATH'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'video.ffmpeg', $this->config->get('video.ffmpeg')); ?>

						<?php if ($this->config->get('video.ffmpeg') && !JFile::exists($this->config->get('video.ffmpeg'))) { ?>
							<div class="t-text--danger">
								<b><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_SETTINGS_FFMPEG_PATH_NOT_FOUND');?></b>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_DELETE_PROCESSED_VIDEOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.delete', $this->config->get('video.delete')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_VIDEO_SIZE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'video.size', $this->config->get('video.size'), array(
								array('value' => '1080', 'text' => '1920 x 1080 (1080p)'),
								array('value' => '720', 'text' => '1280 x 720 (720p)'),
								array('value' => '480', 'text' => '854 x 480 (480p)'),
							)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CPU_LIMITING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.cpu.limiting', $this->config->get('video.cpu.limiting'), '', array('data-video-cpu-limit')); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('video.cpu.limiting') ? '' : 't-hidden';?>" data-es-video-threads>
					<?php echo $this->html('panel.label', 'COM_ES_CPU_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.text', 'video.cpu.limit', '', $this->config->get('video.cpu.limit'), array()); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_ENCODE_VIDEO_AFTER_UPLOAD'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.autoencode', $this->config->get('video.autoencode')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_MAXIMUM_AUDIO_BITRATE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'video.audiobitrate', $this->config->get('video.audiobitrate'), array(
								array('value' => '32k', 'text' => '32 kbps'),
								array('value' => '64k', 'text' => '64 kbps'),
								array('value' => '96k', 'text' => '96 kbps'),
								array('value' => '192k', 'text' => '192 kbps'),
								array('value' => '224k', 'text' => '224 kbps'),
						)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
