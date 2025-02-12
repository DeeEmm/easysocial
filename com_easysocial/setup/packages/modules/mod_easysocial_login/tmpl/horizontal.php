<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if (!$my->id) { ?>
<div id="es" class="mod-es mod-es-login <?php echo $lib->getSuffix();?> <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">
	<div class="es-mod-login-wrap is-horizontal">
		<div class="es-mod-login-wrap__hd" style="background-image: url('/media/com_easysocial/images/bg-register-pattern.png');">
			<form class="es-form-login" method="post" action="<?php echo JRoute::_('index.php', true, $lib->useSecureUrl());?>">
				<div class="o-grid o-grid--gutters">
					<div class="o-grid__cell">
						<div class="o-form-group">
							<input type="text" name="username"
								placeholder="<?php echo $config->get('registrations.emailasusername') ? JText::_('MOD_EASYSOCIAL_LOGIN_EMAIL_PLACEHOLDER') : JText::_('MOD_EASYSOCIAL_LOGIN_USERNAME_PLACEHOLDER');?>" 
								class="o-form-control" />

							<?php if ($params->get('show_forget_username', true) && !$config->get('registrations.emailasusername')) { ?>
							<div class="t-lg-mt--md">
								<a href="<?php echo ESR::account(array('layout' => 'forgetUsername'));?>"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_FORGOT_USERNAME');?></a>
							</div>
							<?php } ?>
						</div>
					</div>
					<div class="o-grid__cell">
						<div class="o-form-group">
							<input type="password" name="password" placeholder="<?php echo JText::_('MOD_EASYSOCIAL_LOGIN_PASSWORD_PLACEHOLDER');?>" class="o-form-control" />

							<?php if ($params->get('show_forget_password', true)) { ?>
							<div class="t-lg-mt--md">
								<a href="<?php echo ESR::account(array('layout' => 'forgetPassword'));?>"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_FORGOT_PASSWORD');?></a>
							</div>
							<?php } ?>
						</div>
					</div>
					<div class="o-grid__cell o-grid__cell--auto-size">
						<button class="btn btn-es-primary btn-block"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_SUBMIT');?></button>
					</div>
				</div>
				<div class="o-grid">
					<?php if ($params->get('show_remember_me', true)) { ?>
					<div class="o-grid__cell o-grid__cell--auto-size <?php echo $params->get('remember_me_style', 'visible_checked') == 't-hidden' || $params->get('remember_me_style', 'visible_checked') == 'hidden_checked' ? 't-hidden' : '';?>">
						<div class="o-form-group">
							<div class="o-checkbox">
								<input type="checkbox" id="remember-me" name="remember" value="yes"
									<?php echo $params->get( 'remember_me_style' , 'visible_checked' ) == 'visible_checked' || $params->get('remember_me_style' , 'visible_checked') == 'hidden_checked' ? 'checked="checked"' : '';?>
								/>
								<label for="remember-me"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_KEEP_ME_LOGGED_IN');?></label>
							</div>
						</div>
					</div>
					<?php } ?>

					<?php if ($sso->hasSocialButtons()) { ?>
					<div class="o-grid__cell t-text--right t-lg-mb--md">
						<span class="t-lg-mr--md"><?php echo JText::_('MOD_EASYSOCIAL_LOGIN_SIGN_IN_WITH_SOCIAL_IDENTITY');?></span>

						<?php foreach ($sso->getSocialButtons('btn-sm') as $socialButton) { ?>
							<?php echo $socialButton; ?>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
				<input type="hidden" name="option" value="com_easysocial" />
				<input type="hidden" name="controller" value="account" />
				<input type="hidden" name="task" value="login" />
				<input type="hidden" name="return" value="<?php echo $return;?>" />
				<?php echo $lib->html('form.token');?>
			</form>
		</div>

		<?php if ($params->get('show_register_link', true)) { ?>
		<div class="es-mod-login-wrap__ft">
			<ul class="g-list-inline g-list-inline--dashed">				
				<li>
					<?php echo JText::sprintf('MOD_ES_LOGIN_FIRST_TIME_HERE', '<a href="' . ESR::registration() . '">' . JText::_('MOD_ES_LOGIN_REGISTER_NOW') . '</a>'); ?>
				</li>
			</ul>
		</div>
		<?php } ?>

	</div>
</div>
<?php } else { ?>
<div id="es" class="mod-es mod-es-login style-horizontal <?php echo $lib->getSuffix();?> <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">
	<form action="<?php echo JRoute::_( 'index.php' );?>" id="es-mod-login-signout-form" method="post">
		<div class="text-center">
			<a href="javascript:void(0);" onclick="document.getElementById( 'es-mod-login-signout-form' ).submit();" class="btn btn-primary">
				<?php echo JText::_( 'MOD_EASYSOCIAL_LOGIN_SIGN_OUT' );?>
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
