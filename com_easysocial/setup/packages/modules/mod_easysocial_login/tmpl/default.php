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
<?php if( !$my->id ){ ?>
<div id="es" class="es mod-es-login style-vertical module-social<?php echo $lib->getSuffix();?>">
	<form class="es-form-login" method="post" action="<?php echo JRoute::_('index.php', true, $lib->useSecureUrl());?>">

		<div class="o-form-group">
			<div class="o-input-group">
				<span class="o-input-group__addon"><i class="fa fa-user"></i></span>
				<input type="text" name="username"
					placeholder="<?php echo $config->get('registrations.emailasusername') ? JText::_('MOD_EASYSOCIAL_LOGIN_EMAIL_PLACEHOLDER') : JText::_('MOD_EASYSOCIAL_LOGIN_USERNAME_PLACEHOLDER');?>"
					class="o-form-control"
				/>
			</div>
		</div>

		<div class="o-form-group">
			<div class="o-input-group">
				<span class="o-input-group__addon"><i class="fa fa-lock"></i></span>
				<input type="password" name="password" placeholder="<?php echo JText::_('MOD_EASYSOCIAL_LOGIN_PASSWORD_PLACEHOLDER');?>" class="o-form-control" />
			</div>
		</div>

		<?php if ($config->get('general.site.twofactor')) { ?>
		<div class="o-form-group">
			<div class="o-input-group">
				<span class="o-input-group__addon"
					data-placement="bottom"
					data-title="<?php echo JText::_('MOD_EASYSOCIAL_LOGIN_TWOFACTOR_SECRET_PLACEHOLDER'); ?>"
					data-content="<?php echo JText::_('MOD_EASYSOCIAL_LOGIN_TWOFACTOR_SECRET_PLACEHOLDER_INFO'); ?>"
					data-es-provide="popover">
				<i class="fa fa-question-circle"></i></span>
				<input type="text" name="secretkey" placeholder="<?php echo JText::_('MOD_EASYSOCIAL_LOGIN_TWOFACTOR_SECRET_PLACEHOLDER', true);?>"  class="o-form-control" />
			</div>
		</div>
		<?php } ?>

		<?php if ($params->get('show_remember_me' , true)) { ?>
		<div class="o-form-group">
			<div class="o-checkbox">
				<input type="checkbox" id="es-mod-remember-me" name="remember" value="yes"
					<?php echo $params->get('remember_me_style', 'visible_checked') == 'visible_checked' || $params->get('remember_me_style', 'visible_checked') == 'hidden_checked' ? 'checked="checked"' : '';?> />
				<label for="es-mod-remember-me"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_KEEP_ME_LOGGED_IN');?></label>
			</div>
		</div>
		<?php } ?>

		<button type="submit" class="btn btn-block btn-es-primary"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_SUBMIT');?></button>

		<ul class="o-nav o-nav--stacked t-lg-mt--xl">
			<?php if ($params->get('show_register_link', true)) { ?>
			<li>
				<a href="<?php echo ESR::registration();?>"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_REGISTER_NOW');?></a>
			</li>
			<?php } ?>

			<?php if ($params->get('show_forget_username', true) && !$config->get('registrations.emailasusername')) { ?>
			<li>
				<a href="<?php echo ESR::account(array('layout' => 'forgetUsername'));?>"><?php echo JText::_( 'MOD_EASYSOCIAL_LOGIN_FORGOT_USERNAME' );?></a>
			</li>
			<?php } ?>

			<?php if ($params->get('show_forget_password', true)) { ?>
			<li>
				<a href="<?php echo ESR::account(array('layout' => 'forgetPassword'));?>"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_FORGOT_PASSWORD');?></a>
			</li>
			<?php } ?>
		</ul>

		<?php if ($sso->hasSocialButtons()) { ?>
		<div class="es-signin-social">
			<p class="">
				<b><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_SIGN_IN_WITH_SOCIAL_IDENTITY');?></b>
			</p>
			<?php foreach ($sso->getSocialButtons() as $socialButton) { ?>
			<div class="t-lg-mt--md">
				<?php echo $socialButton; ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>

		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="account" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return;?>" />
		<?php echo $lib->html('form.token');?>
	</form>
</div>
<?php } else { ?>
<div id="es" class="es mod-es-login style-vertical module-social<?php echo $lib->getSuffix();?>">
	<form action="<?php echo JRoute::_('index.php');?>" id="es-mod-login-signout-form" method="post">
		<div class="text-center">
			<a href="javascript:void(0);" onclick="document.getElementById('es-mod-login-signout-form').submit();" class="btn btn-es-default btn-block">
				<i class="fa fa-sign-out-alt"></i>&nbsp; <?php echo JText::_('MOD_EASYSOCIAL_LOGIN_SIGN_OUT');?>
			</a>
		</div>

		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="account" />
		<input type="hidden" name="task" value="logout" />
		<input type="hidden" name="return" value="<?php echo $lib->getLogoutReturnUrl(); ?>" />
		<?php echo $lib->html('form.token'); ?>
	</form>
</div>
<?php } ?>
