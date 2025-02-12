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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_NOTIFICATIONS', '', '/administrators/configuration/notification-settings'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.email.enabled', 'COM_ES_ENABLE_EMAIL_NOTIFICATIONS'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.system.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_SYSTEM_NOTIFICATION'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.system.autoread', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_SYSTEM_AUTOMATICALLY_MARK_AS_READ'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.system.prependtitle', 'COM_ES_PREPEND_COUNTER_IN_TITLE'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.polling.single', 'COM_ES_NOTIFICATIONS_SETTINGS_SINGLE_POLLING_REQUEST', '', 'data-toggle-polling-single'); ?>
				<?php echo $this->html('settings.textbox', 'notifications.polling.interval', 'COM_ES_NOTIFICATIONS_SETTINGS_POLLING_INTERVAL', '', array('postfix' => 'COM_EASYSOCIAL_SECONDS', 'size' => 7, 'wrapperClass' => $this->config->get('notifications.polling.single') ? 't-hidden' : ''), '', 'text-center', 'data-polling-interval'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_NOTIFICATIONS_CONVERSATIONS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.conversation.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_CONVERSATION_NOTIFICATION'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.conversation.autoread', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CONVERSATIONS_AUTOMATICALLY_MARK_AS_READ'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_NOTIFICATIONS_FRIENDS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.friends.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_FRIEND_NOTIFICATION'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_NOTIFICATIONS_SETTINGS_SENDER', '', '/administrators/configuration/notification-settings#sendersettings'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.textbox', 'notifications.general.fromname', 'COM_ES_NOTIFICATIONS_SETTINGS_SENDER_NAME'); ?>
				<?php echo $this->html('settings.textbox', 'notifications.general.fromemail', 'COM_ES_NOTIFICATIONS_SETTINGS_SENDER_EMAIL'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_BROADCAST_SETTINGS', '', '/administrators/configuration/administrator-broadcasting'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.broadcast.popup', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_BROADCAST_POPUP'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.broadcast.sticky', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_STICKY_POPUP'); ?>
				<?php echo $this->html('settings.textbox', 'notifications.broadcast.period', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_BROADCAST_PERIOD', '', array('postfix' => 'COM_EASYSOCIAL_SECONDS', 'size' => 7), '', 'text-center'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP', '', '/administrators/configuration/notification-automated-cleanup'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.cleanup.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_ENABLE'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.cleanup.unread', 'COM_ES_NOTIFICATIONS_SETTINGS_CLEANUP_UNREAD'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'notifications.cleanup.duration', $this->config->get('notifications.cleanup.duration'), array(
								array('value' => '1', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_1_MONTHS'),
								array('value' => '3', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_3_MONTHS'),
								array('value' => '6', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_6_MONTHS'),
								array('value' => '12', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_12_MONTHS'),
								array('value' => '18', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_18_MONTHS'),
								array('value' => '24', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_24_MONTHS')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
