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
<div class="es-social-signon has-avatar">
	<div class="es-social-signon__hd">
		<div class="es-social-signon__title">
			<h1><?php echo JText::_('COM_EASYSOCIAL_HEADING_OAUTH_REQUIRE_ADDITIONAL_DETAILS');?></h1>
		</div>

		<div class="es-social-signon__subtitle"></div>
	</div>

	<form action="<?php echo JRoute::_('index.php');?>" class="es-social-signon__form" method="post" data-oauth-preferences>
		<?php if (isset($meta['avatar'])) { ?>
		<div class="es-social-signon__avatar">
			<?php echo $this->html('avatar.mini', $meta['name'], '', $meta['avatar'], 'lg', '', '', false); ?>
		</div>
		<?php } ?>

		<div class="es-social-signon__form-inner">

			<?php if (!$nameExists) { ?>
				<div class="o-form-group">
					<label for="oauth-firstname"><?php echo JText::_('COM_ES_OAUTH_REGISTRATION_FIRSTNAME');?></label>
					<input name="oauth-firstname" type="text" placeholder="<?php echo JText::_('COM_ES_OAUTH_REGISTRATION_FIRSTNAME_PLACEHOLDER', true);?>" id="oauth-firstname" class="o-form-control" value="" />
				</div>

				<div class="o-form-group">
					<label for="oauth-lastname"><?php echo JText::_('COM_ES_OAUTH_REGISTRATION_LASTNAME');?></label>
					<input name="oauth-lastname" type="text" placeholder="<?php echo JText::_('COM_ES_OAUTH_REGISTRATION_LASTNAME_PLACEHOLDER', true);?>" id="oauth-lastname" class="o-form-control" value="" />
				</div>
			<?php } ?>
			<?php if (!$this->config->get('registrations.emailasusername')) { ?>
			<div class="o-form-group">
				<label for="oauth-username"><?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_USERNAME');?></label>
				<input name="oauth-username" type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_USERNAME_PLACEHOLDER', true);?>" id="oauth-username" class="o-form-control" value="<?php echo $username;?>" />

				<?php if ($usernameExists) { ?>
				<div class="error small mt-5">
					<?php echo JText::_( 'COM_EASYSOCIAL_OAUTH_REGISTRATION_USERNAME_ERRORS'); ?>
				</div>
				<?php } ?>
			</div>
			<?php } ?>

			<div class="o-form-group <?php echo $emailExists || !$validEmail ? ' t-text--danger' : '';?>">
				<label for="oauth-email">
					<?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_EMAIL');?>:
				</label>
				<input name="oauth-email" type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_EMAIL_PLACEHOLDER', true);?>" id="oauth-email" class="o-form-control" value="<?php echo $email;?>" />

				<?php if ($emailExists || ($this->config->get('registrations.emailasusername') && $usernameExists)) { ?>
				<p class="t-fs--sm text-error">
					<?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_EMAIL_ERRORS'); ?>
				</p>
				<?php } ?>
			</div>

			<div class="o-form-group">
				<label for="oauth-password">
					<?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_PASSWORD');?>:
				</label>
				<input name="oauth-password" type="password" id="oauth-password" class="o-form-control" value="" />

				<div class="t-fs--sm t-lg-mt--md"><?php echo JText::_('COM_EASYSOCIAL_OAUTH_REGISTRATION_PASSWORD_NOTE');?></div>
			</div>

			<?php if ($importCover || $importAvatar) { ?>
			<div class="o-form-group">
				<label for="import-avatar"><?php echo JText::_('COM_EASYSOCIAL_OAUTH_IMPORT_AVATAR_' . strtoupper($clientType));?></label>
				<?php echo $this->html('form.toggler', 'import', true); ?>
			</div>
			<?php } ?>

			<button class="btn btn-es-primary btn-block"><?php echo JText::_('COM_EASYSOCIAL_COMPLETE_REGISTRATION_BUTTON');?></button>
		</div>

		<?php echo $this->html('form.token'); ?>
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="registration" />
		<input type="hidden" name="task" value="oauthCreateAccount" />
		<input type="hidden" name="client" value="<?php echo $clientType;?>" />
		<input type="hidden" name="profile" value="<?php echo $profileId;?>" />
		<input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>" />
		<?php echo $this->html('form.itemid'); ?>
	</form>
</div>
