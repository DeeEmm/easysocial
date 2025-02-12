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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STREAM_SETTINGS_STORY_FORM'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'stream.story.backgrounds', 'COM_ES_ENABLE_CUSTOM_BACKGROUNDS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.story.enablelimits', 'COM_ES_LIMIT_STORY_PANELS'); ?>
				<?php echo $this->html('settings.textbox', 'stream.story.limit', 'COM_ES_STORY_FORM_ITEMS_LIMIT', '', array('postfix' => 'Items', 'size' => 7), '', 'text-center'); ?>
				<?php echo $this->html('settings.toggle', 'stream.story.favourite', 'COM_ES_STORY_ALLOW_USER_TO_SET_FAVOURITES', ''); ?>
				<?php echo $this->html('settings.toggle', 'stream.story.mentions', 'COM_EASYSOCIAL_STREAM_SETTINGS_DISPLAY_MENTIONS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.story.location', 'COM_EASYSOCIAL_STREAM_SETTINGS_DISPLAY_LOCATION'); ?>
				<?php echo $this->html('settings.toggle', 'stream.story.moods', 'COM_EASYSOCIAL_STREAM_SETTINGS_ENABLE_MOODS'); ?>
				<?php echo $this->html('settings.toggle', 'stream.story.giphy', 'COM_ES_STREAM_SETTINGS_ENABLE_GIPHY'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STREAM_SETTINGS_LAYOUT'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'stream.content.truncate', 'COM_EASYSOCIAL_STREAM_SETTINGS_TRUNCATION'); ?>
				<?php echo $this->html('settings.textbox', 'stream.content.truncatelength', 'COM_EASYSOCIAL_STREAM_SETTINGS_TRUNCATION_LENGTH', '', [
					'postfix' => 'COM_EASYSOCIAL_CHARACTERS'
				], '', 'text-center'); ?>


				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ACTIVITY_STREAM_LOCATION_DISPLAY_STYLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.location.style', $this->config->get('stream.location.style'), [
								['value' => 'inline', 'text' => 'COM_ES_STREAM_LOCATION_INLINE'],
								['value' => 'popup', 'text' => 'COM_ES_STREAM_LOCATION_POPUP']
							], 'stream-location-style', ['data-location-style']); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
