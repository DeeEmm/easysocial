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
			<?php echo $this->html('panel.heading', 'COM_ES_REGISTRATION_ANTISPAM'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'registrations.honeypot', 'COM_ES_REGISTRATION_ENABLE_HONEYPOT_REGISTRATIONS'); ?>
				<?php echo $this->html('settings.toggle', 'honeypot.conversations', 'COM_ES_REGISTRATION_ENABLE_HONEYPOT_CONVERSATIONS'); ?>
				<?php echo $this->html('settings.toggle', 'honeypot.stream', 'COM_ES_HONEYPOT_ENABLE_ACTIVITIES'); ?>
				<?php echo $this->html('settings.toggle', 'honeypot.profile', 'COM_ES_HONEYPOT_ENABLE_PROFILE'); ?>
				<?php echo $this->html('settings.toggle', 'honeypot.logging', 'COM_ES_REGISTRATION_ENABLE_HONEYPOT_LOGGING'); ?>
				<?php echo $this->html('settings.toggle', 'honeypot.autoswitch', 'COM_ES_REGISTRATION_HONEYPOT_AUTOSWITCH_KEY'); ?>
				<?php echo $this->html('settings.textbox', 'registrations.honeypotkey', 'COM_ES_REGISTRATION_HONEYPOT_KEY', '', array('attributes' => $this->config->get('honeypot.autoswitch') ? 'disabled="disabled"' : ''), 'COM_ES_HONEYPOT_AUTOSWITCH_USED'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
