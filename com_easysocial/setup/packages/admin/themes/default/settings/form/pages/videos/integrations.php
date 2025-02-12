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
			<?php echo $this->html('panel.heading', 'COM_ES_VIDEOS_SETTINGS_YOUTUBE_INTEGRATION'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_YOUTUBE_SHOW_RELATED_VIDEO'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.youtube.related', $this->config->get('video.youtube.related')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_LINKS_SETTINGS_ENABLED_YOUTUBE_ENHANCED_MODE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'youtube.nocookie', $this->config->get('youtube.nocookie')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKS_SETTINGS_ENABLED_YOUTUBE_API'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'youtube.api.enabled', $this->config->get('youtube.api.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKS_SETTINGS_YOUTUBE_API_KEY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'youtube.api.key', $this->config->get('youtube.api.key')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_VIDEOS_SETTINGS_TWITCH_INTEGRATION'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_TWITCH_CLIENT_ID'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'video.twitch.clientId', $this->config->get('video.twitch.clientId')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_TWITCH_CLIENT_SECRET'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'video.twitch.clientSecret', $this->config->get('video.twitch.clientSecret')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_TWITCH_APP_ACCESS_TOKEN_AUTHENTICATION'); ?>

					<div class="col-md-7">
						<?php if ($isAssociated) { ?>
							<?php echo ES::twitch()->getRevokeButton(rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_easysocial&view=settings&layout=form&page=videos&tab=integrations', true);?>

							<div class="help-block small">
								<?php echo JText::sprintf('COM_ES_VIDEOS_SETTINGS_TWITCH_APP_ACCESS_TOKEN_REVOKE_INFO', $expires);?>
							</div>
						<?php } else { ?>
							<?php echo ES::twitch()->getLoginButton();?>

							<div class="help-block small">
								<?php echo JText::_('COM_ES_VIDEOS_SETTINGS_TWITCH_APP_ACCESS_TOKEN_AUTHENTICATION_INFO');?>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_TWITCH_VIDEO_THUMBNAIL_SIZE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'video.twitch.thumbnailSize', $this->config->get('video.twitch.thumbnailSize'), array(
								array('value' => '1080', 'text' => '1920 x 1080 (1080p)'),
								array('value' => '720', 'text' => '1280 x 720 (720p)')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
