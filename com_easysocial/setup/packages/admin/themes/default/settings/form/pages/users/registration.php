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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_USERS_SETTINGS_REGISTRATION'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'registrations.enabled', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_ALLOW_REGISTRATIONS'); ?>

				<?php echo $this->html('settings.toggle', 'registrations.steps.progress', 'COM_ES_REGISTRATION_SETTINGS_DISPLAY_STEPS_BAR'); ?>

				<?php echo $this->html('settings.toggle', 'registrations.mini.enabled', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_DISPLAY_MINI_REGISTRATION'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_MODE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'registrations.mini.mode', $this->config->get('registrations.mini.mode'), array(
								array('value' => 'quick', 'text' => 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_QUICK'),
								array('value' => 'full', 'text' => 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_FULL'),
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_MINI_REGISTRATION_PROFILE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'registrations.mini.profile', '', $this->config->get('registrations.mini.profile'), array('default' => true)); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'registrations.email.password', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_SHOW_CLEAR_PASSWORD'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_REGISTRATION_SETTINGS_PROFILE_TYPE_SELECTION_LAYOUT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'registrations.profiles.selection.layout', $this->config->get('registrations.profiles.selection.layout'), array(
								array('value' => 'list', 'text' => 'COM_ES_LIST_LAYOUT'),
								array('value' => 'dropdown', 'text' => 'COM_ES_DROPDOWN_LAYOUT'),
						)); ?>
					</div>
				</div>
				<?php echo $this->html('settings.toggle', 'registrations.layout.avatar', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_SHOW_PROFILE_AVATAR'); ?>

				<?php echo $this->html('settings.toggle', 'registrations.layout.users', 'COM_EASYSOCIAL_THEMES_WIREFRAME_FIELD_SHOW_USERS_REGISTERED_IN_PROFILE'); ?>

				<?php echo $this->html('settings.toggle', 'registrations.email.reconfirmation', 'COM_EASYSOCIAL_REGISTRATION_SETTINGS_ALLOW_REGISTRATIONS_SHOW_EMAIL_RECONFIRMATION'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_REGISTRATION_REDIRECTION'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'registrations.redirection', 'COM_ES_REGISTRATION_SETTINGS_ENABLE_COM_USER_REDIRECTION'); ?>
			</div>
		</div>
	</div>
</div>
