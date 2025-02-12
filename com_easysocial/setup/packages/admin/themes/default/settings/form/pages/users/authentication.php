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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_USERS_SETTINGS_AUTHENTICATION'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_ALLOW_LOGIN_WITH_EMAIL'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'general.site.loginemail', $this->config->get('general.site.loginemail')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_USE_EMAIL_AS_USERNAME'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'registrations.emailasusername', $this->config->get('registrations.emailasusername')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_LOGIN_REDIRECTION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.menus', 'general.site.login', $this->config->get('general.site.login'), array(
								JText::_('COM_EASYSOCIAL_USERS_SETTINGS_MENU_GROUP_CORE') => array(JHtml::_('select.option', 'null', JText::_('COM_EASYSOCIAL_USERS_SETTINGS_STAY_SAME_PAGE')))
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_LOGOUT_REDIRECTION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.menus', 'general.site.logout', $this->config->get('general.site.logout'), array(
								JText::_('COM_EASYSOCIAL_USERS_SETTINGS_MENU_GROUP_CORE') => array(JHtml::_('select.option', 'null', JText::_('COM_EASYSOCIAL_USERS_SETTINGS_STAY_SAME_PAGE')))
						)); ?>
					</div>
				</div>

				<?php if (!ES::isJoomla42()) { ?>
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_USERS_SETTINGS_ENABLE_2_FACTOR_AUTHENTICATION'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.toggler', 'general.site.twofactor', $this->config->get('general.site.twofactor')); ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>

</div>
