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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'pages.enabled', 'COM_EASYSOCIAL_PAGES_SETTINGS_ENABLE_PAGES'); ?>
				<?php echo $this->html('settings.toggle', 'pages.invite.nonfriends', 'COM_EASYSOCIAL_SETTINGS_ALLOW_INVITE_NON_FRIENDS'); ?>
				<?php echo $this->html('settings.toggle', 'pages.feed.includeadmin', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_FEED_INCLUD_ADMIN'); ?>
				<?php echo $this->html('settings.toggle', 'pages.sharing.showprivate', 'COM_EASYSOCIAL_CLUSTERS_SETTINGS_SOCIAL_SHARING_PRIVATE'); ?>
				<?php echo $this->html('settings.toggle', 'pages.tag.nonfriends', 'COM_ES_PAGES_SETTINGS_TAG_NONFRIENDS'); ?>
				<?php echo $this->html('settings.toggle', 'pages.followers.includeadmin', 'COM_ES_DISPLAY_ADMINS_IN_FOLLOWERS'); ?>
				<?php echo $this->html('settings.toggle', 'pages.verification.enabled', 'COM_ES_PAGES_ENABLE_VERIFIED_PAGES'); ?>
				<?php echo $this->html('settings.toggle', 'pages.verification.requests', 'COM_ES_USERS_ALLOW_VERIFICATION_REQUESTS'); ?>
				<?php echo $this->html('settings.toggle', 'pages.editmoderation', 'COM_ES_PAGES_MODERATE_EDITED_PAGES'); ?>
				<?php echo $this->html('settings.toggle', 'pages.conversations', 'COM_ES_PAGES_SEND_PM'); ?>
			</div>
		</div>
	</div>
</div>
