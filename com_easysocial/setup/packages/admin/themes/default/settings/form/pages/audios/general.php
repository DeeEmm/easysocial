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
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_SETTINGS_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'audio.enabled', 'COM_ES_AUDIO_SETTINGS_ENABLE_AUDIOS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.uploads', 'COM_ES_AUDIO_SETTINGS_ALLOW_AUDIO_UPLOADS', '', 'data-audio-uploads'); ?>
				<?php echo $this->html('settings.toggle', 'audio.allowencode', 'COM_ES_AUDIO_SETTINGS_ENABLE_ENCODE_AUDIO', '', 'data-enable-encoder', '', 'data-encoder-option', (!$this->config->get('audio.uploads') ? 't-hidden' : '')); ?>
				<?php echo $this->html('settings.toggle', 'audio.downloads', 'COM_ES_AUDIO_SETTINGS_ALLOW_DOWNLOADS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.counters', 'COM_ES_AUDIO_SETTINGS_ENABLE_SIDEBAR_COUNTERS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.dates', 'COM_ES_AUDIO_SETTINGS_ENABLE_ASSIGNED_DATES'); ?>
				<?php echo $this->html('settings.dropdown', 'audio.sortbehavior', 'COM_ES_AUDIO_DEFAULT_SORTING_BEHAVIOR', '', [
					'created' => 'Sort by Creation Date (Default)',
					'assigned_date' => 'Sort by Assigned Date (Based on the date field)'
				]); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_EMBEDDING'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'audio.embeds', 'COM_ES_AUDIO_SETTINGS_ALLOW_AUDIO_EMBEDS', '', 'data-audio-embed'); ?>
				<?php echo $this->html('settings.toggle', 'audio.embed.spotify', 'COM_ES_AUDIO_SETTINGS_ALLOW_EMBEDS_SPOTIFY', '', '', '', 'data-embed-spotify'); ?>
				<?php echo $this->html('settings.toggle', 'audio.embed.soundcloud', 'COM_ES_AUDIO_SETTINGS_ALLOW_EMBEDS_SOUNDCLOUD', '', '', '', 'data-embed-soundcloud'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel <?php echo !$this->config->get('audio.allowencode') ? 't-hidden' : '';?>" data-audio-encoding>
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_SETTINGS_ENCODING'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ENCODER_PATH'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'audio.encoder', $this->config->get('audio.encoder')); ?>

						<?php if ($this->config->get('audio.encoder') && !JFile::exists($this->config->get('audio.encoder'))) { ?>
							<div class="t-text--danger">
								<b><?php echo JText::_('COM_ES_AUDIO_SETTINGS_ENCODER_PATH_NOT_FOUND');?></b>
							</div>
						<?php } ?>
						<div class="help-block">
							<?php echo JText::_('COM_ES_AUDIO_SETTINGS_ENCODER_COMPATIBLE'); ?>
						</div>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'audio.delete', 'COM_ES_AUDIO_SETTINGS_DELETE_PROCESSED_AUDIOS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.autoencode', 'COM_ES_AUDIO_SETTINGS_ENCODE_AUDIO_AFTER_UPLOAD'); ?>
				<?php echo $this->html('settings.toggle', 'audio.autoimportdata', 'COM_ES_AUDIO_SETTINGS_AUTOMATICALLY_IMPORT_METADATA'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_MAXIMUM_BITRATE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'audio.bitrate', $this->config->get('audio.bitrate'), array(
								array('value' => '32k', 'text' => '32 kbps'),
								array('value' => '64k', 'text' => '64 kbps'),
								array('value' => '96k', 'text' => '96 kbps'),
								array('value' => '192k', 'text' => '192 kbps'),
								array('value' => '224k', 'text' => '224 kbps')
						)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
