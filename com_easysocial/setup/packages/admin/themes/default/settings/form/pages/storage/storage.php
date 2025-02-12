<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STORAGEPATH_SETTINGS_HEADER'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.textbox', 'photos.storage.container', 'COM_EASYSOCIAL_STORAGEPATH_SETTINGS_PHOTO_STORAGE_PATH', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'avatars.storage.container', 'COM_EASYSOCIAL_STORAGEPATH_SETTINGS_AVATAR_STORAGE_PATH', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'video.storage.container', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_STORAGE_PATH', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'audio.storage.container', 'COM_ES_AUDIO_SETTINGS_STORAGE_PATH', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'comments.storage', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_STORAGE', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'conversations.attachments.storage', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_STORAGE_PATH', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'links.cache.location', 'COM_EASYSOCIAL_LINKS_SETTINGS_CACHE_LOCATION'); ?>
				<?php echo $this->html('settings.textbox', 'files.storage.container', 'COM_EASYSOCIAL_STORAGEPATH_SETTINGS_FILE_STORAGE_PATH', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
				<?php echo $this->html('settings.textbox', 'ads.storage', 'COM_ES_STORAGEPATH_ADS', '', array(), 'COM_EASYSOCIAL_STORAGE_SETTINGS_STORAGE_PATH_INFO'); ?>
			</div>
		</div>
	</div>
</div>
