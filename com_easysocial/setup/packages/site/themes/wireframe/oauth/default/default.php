<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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
			<h1><?php echo JText::_('COM_EASYSOCIAL_HEADING_SIGN_IN_WITH_SOCIAL_ACCOUNT');?></h1>
		</div>

		<div class="es-social-signon__subtitle">
			<h2>
			<?php echo JText::sprintf('COM_EASYSOCIAL_OAUTH_WELCOME_TITLE', '<b>'. $meta['name'] . '</b>');?></h2>
		</div>
	</div>

	<form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-social-signon__form">

		<?php if (isset($meta['avatar'])) { ?>
		<div class="es-social-signon__avatar">
			<?php echo $this->html('avatar.mini', $meta['name'], '', $meta['avatar'], '', '', '', false); ?>
		</div>
		<?php } ?>


		<div class="es-social-signon__form-inner">
			<div class="es-social-signon__form-title"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_OAUTH_EXISTING_USERS');?></div>
			<div class="es-social-signon__form-desp"><?php echo JText::sprintf('COM_EASYSOCIAL_REGISTRATION_OAUTH_EXISTING_USERS_DESC' , ucfirst($clientType));?></div>

			<?php if ($this->config->get('registrations.emailasusername')) { ?>
			<div class="o-form-group">
				<label for="oauth-username"><?php echo JText::_('COM_EASYSOCIAL_EMAIL');?></label>
				<input type="text" name="username" placeholder="<?php echo JText::_('COM_EASYSOCIAL_LOGIN_EMAIL_PLACEHOLDER');?>" id="oauth-username" class="o-form-control" />
			</div>
			<?php } else { ?>
			<div class="o-form-group">
				<label for="oauth-username"><?php echo JText::_('COM_EASYSOCIAL_USERNAME');?></label>
				<input type="text" name="username" placeholder="<?php echo JText::_('COM_EASYSOCIAL_USERNAME');?>" id="oauth-username" class="o-form-control" />
			</div>
			<?php } ?>

			<div class="o-form-group">
				<label for="password1"><?php echo JText::_('COM_EASYSOCIAL_PASSWORD');?></label>
				<input type="password" name="password" placeholder="<?php echo JText::_('COM_EASYSOCIAL_PASSWORD');?>" id="password1" class="o-form-control" />
			</div>

			<?php if ($importAvatar) { ?>
			<div class="o-checkbox">
				<input type="checkbox" name="importAvatar" id="importAvatar" checked="checked" />
				<label for="importAvatar"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_OAUTH_EXISTING_USERS_IMPORT_AVATAR'); ?></label>
			</div>
			<?php } ?>

			<?php if ($importCover) { ?>
			<div class="o-checkbox">
				<input type="checkbox" name="importCover" id="importCover" checked="checked" />
				<label for="importCover"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_OAUTH_EXISTING_USERS_IMPORT_COVER'); ?></label>
			</div>
			<?php } ?>

			<button class="btn btn-es-primary btn-block"><?php echo JText::_('COM_EASYSOCIAL_LINK_ACCOUNT_BUTTON');?></button>
		</div>

		<hr class="es-hr" />

		<div class="es-social-signon__form-inner">
			<div class="es-social-signon__form-title"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_OAUTH_NEW_USERS');?></div>
			<div class="es-social-signon__form-desp"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_OAUTH_NEW_USERS_DESC');?></div>
			<a href="<?php echo $createUrl;?>" class="btn btn-es-primary btn-block"><?php echo JText::_('COM_EASYSOCIAL_CREATE_ACCOUNT_BUTTON');?></a>
		</div>

		<?php echo $this->html('form.itemid'); ?>
		<?php echo $this->html('form.token'); ?>
		<input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>" />
		<input type="hidden" name="client" value="<?php echo $clientType;?>" />
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="registration" />
		<input type="hidden" name="task" value="oauthLinkAccount" />
	</form>
</div>
