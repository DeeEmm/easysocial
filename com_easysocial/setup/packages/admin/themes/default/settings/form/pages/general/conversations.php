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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_CONVERSATIONS', '', '/administrators/configuration/conversations-settings#conversationsettings'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'conversations.enabled', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ENABLE_CONVERSATIONS'); ?>
				<?php echo $this->html('settings.toggle', 'conversations.entersubmit', 'COM_ES_CONVERSATIONS_ENTER_SUBMIT'); ?>
				<?php echo $this->html('settings.toggle', 'conversations.nonfriend', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ALLOW_COMPOSE_TO_NONFRIEND_USERS'); ?>
				<?php echo $this->html('settings.toggle', 'conversations.typing', 'COM_ES_DISPLAY_USERS_TYPING_STATE'); ?>
				<?php echo $this->html('settings.toggle', 'conversations.location', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ENABLE_LOCATION'); ?>
				<?php echo $this->html('settings.toggle', 'conversations.giphy', 'COM_ES_CONVERSATIONS_SETTINGS_ENABLE_GIPHY'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_CONVERSATION_SORTING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'conversations.sorting', $this->config->get('conversations.sorting'), array(
								array('value' => 'created', 'text' => 'Creation Date'),
								array('value' => 'lastreplied', 'text' => 'Last Replied Date')
							)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_CONVERSATION_ORDERING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'conversations.ordering', $this->config->get('conversations.ordering'), array(
								array('value' => 'desc', 'text' => 'COM_ES_CONVERSATION_ORDERING_DESC'),
								array('value' => 'asc', 'text' => 'COM_ES_CONVERSATION_ORDERING_ASC')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<?php if (JPluginHelper::isEnabled('system', 'conversekit')) { ?>
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_CK_INTEGRATIONS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'conversations.conversekit.links', 'COM_ES_CK_INTEGRATIONS_MESSAGE_LINKS'); ?>
			</div>
		</div>
		<?php } ?>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ATTACHMENTS', '', '/administrators/configuration/conversations-settings#conversationattachements'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'conversations.attachments.enabled', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ALLOW_ATTACHMENTS'); ?>
				<?php echo $this->html('settings.textarea', 'conversations.attachments.types', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ATTACHMENT_TYPES'); ?>
				<?php echo $this->html('settings.textbox', 'conversations.attachments.maxsize', 'COM_EASYSOCIAL_CONVERSATIONS_SETTINGS_ATTACHMENT_MAX_SIZE', '', array('postfix' => 'COM_ES_UNIT_MB', 'size' => 7), '', 'text-center'); ?>
			</div>
		</div>
	</div>
</div>
