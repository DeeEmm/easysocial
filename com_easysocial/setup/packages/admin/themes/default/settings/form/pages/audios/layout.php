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
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_SETTINGS_ITEM_LAYOUT_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'audio.layout.item.recent', 'COM_ES_AUDIO_SETTINGS_DISPLAY_RECENT_AUDIOS'); ?>
				<?php echo $this->html('settings.textbox', 'audio.layout.item.total', 'COM_ES_AUDIO_SETTINGS_TOTAL_OTHER_AUDIOS', '', [
						'postfix' => 'Audios'
					], '', 'text-center');
				?>

				<?php echo $this->html('settings.toggle', 'audio.layout.item.hits', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_HITS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.layout.item.duration', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_DURATION'); ?>
				<?php echo $this->html('settings.toggle', 'audio.layout.item.usertags', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_USER_TAGS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.layout.item.tags', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_TAGS'); ?>
				<?php echo $this->html('settings.toggle', 'audio.layout.item.navigation', 'COM_ES_AUDIO_SETTINGS_DISPLAY_NAVIGATION'); ?>
			</div>
		</div>
	</div>
</div>
