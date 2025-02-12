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
<div class="es-login t-lg-mb--lg" data-dashboard-guest-login>
	<div class="es-login-box" style="<?php echo $this->config->get('login.static.background') ? 'background-color:' . $this->config->get('login.static.backgroundcolour') . ';' : ES::login()->getLoginImage(true); ?>">
		<div class="o-row">
			<div class="o-col--6">
				<div class="es-login-box__msg">
				<?php if ($showRegistrations) { ?>
					<?php if ($this->config->get('registrations.mini.enabled') && !$this->isMobile()) { ?>
						<form method="post" action="<?php echo JRoute::_('index.php');?>" data-registermini-form>
							<div class="register-wrap">
								<div class="es-login-box__msg-title"><?php echo JText::_('COM_EASYSOCIAL_LOGIN_NO_ACCOUNT');?></div>

								<div class="es-login-box__msg-desc">
									<?php echo JText::_('COM_EASYSOCIAL_LOGIN_REGISTER_NOW');?>
								</div>

								<?php foreach ($fields as $field) { ?>
									<div class="es-register-mini-field" data-registermini-fields-item><?php echo $field->output;?></div>
								<?php } ?>

								<?php if ($this->config->get('registrations.honeypot')) { ?>
									<?php echo $this->html('form.honeypot'); ?>
								<?php } ?>

								<div class="t-text--center">
									<button class="btn btn-es-primary btn-block" type="button" data-registermini-submit>
										<?php echo JText::_('COM_EASYSOCIAL_LOGIN_REGISTER_NOW_BUTTON');?>
									</button>
								</div>
							</div>

							<?php echo $this->html('form.action', 'registration', 'miniRegister'); ?>
							<?php if (isset($modRegisterProfile) && $modRegisterProfile) { ?>
							<input type="hidden" name="modRegisterProfile" value="<?php echo $modRegisterProfile; ?>" />
							<?php } ?>
						</form>
					<?php } else { ?>
						<div class="register-wrap">
							<div class="es-login-box__msg-title">
								<?php echo JText::_('COM_EASYSOCIAL_LOGIN_NO_ACCOUNT');?>
							</div>

							<div class="es-login-box__msg-desc"><?php echo JText::_('COM_EASYSOCIAL_LOGIN_REGISTER_NOW');?></div>

							<div class="t-text--center">
								<a href="<?php echo ESR::registration();?>" class="btn btn-es-primary btn-block btn-lg" type="button" data-registermini-submit>
									<i class="fa fa-globe-americas"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_LOGIN_REGISTER_NOW_BUTTON');?>
								</a>
							</div>
						</div>
					<?php } ?>
				<?php } else { ?>
					<div class="register-wrap">
						<div class="es-login-box__msg-title">
							<?php echo JText::_('COM_EASYSOCIAL_WELCOME_TO_YOUR_SOCIAL_SITE');?>
						</div>

						<div class="es-login-box__msg-desc">
							<?php echo JText::_('COM_EASYSOCIAL_WELCOME_TO_YOUR_SOCIAL_SITE_INFO');?>
						</div>
					</div>
				<?php } ?>
				</div>
			</div>

			<div class="o-col--6">
				<div class="es-login-box__form-wrap">
					<form name="loginbox" id="loginbox" method="post" action="<?php echo JRoute::_( 'index.php' );?>" class="es-login-box__form">
						<div class="es-login-box__form-title">
							<?php echo JText::_('COM_EASYSOCIAL_LOGIN_ALREADY_HAVE_ACCOUNT');?>
						</div>

						<fieldset class="t-lg-mt--lg">
							<?php echo $this->html('form.floatinglabel', $usernamePlaceholder, 'username'); ?>

							<?php echo $this->html('form.floatinglabel', 'COM_EASYSOCIAL_TOOLBAR_PASSWORD', 'password', 'password'); ?>

							<?php if ($this->config->get('general.site.twofactor')) { ?>
								<?php echo $this->html('form.floatinglabel', 'COM_EASYSOCIAL_LOGIN_TWOFACTOR_SECRET', 'secretkey', 'text', '', '', false, '', true); ?>
							<?php } ?>

							<div class="o-grid-sm">
								<div class="o-grid-sm__cell">
									<div class="xes-login-box__rmb">
										<div class="o-checkbox">
											<input type="checkbox" id="es-quick-remember" type="checkbox" name="remember" value="1" />
											<label for="es-quick-remember">
												<?php echo JText::_('COM_EASYSOCIAL_LOGIN_REMEMBER_YOU');?>
											</label>
										</div>
									</div>
								</div>
								<div class="o-grid-sm__cell o-grid-sm__cell--right">
									<button type="submit" class="btn btn-es-primary-o" data-login-submit><i class="fa fa-lock"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_LOGIN_BUTTON');?></button>
								</div>
							</div>

							<hr />

							<div class="t-text--center t-lg-mb--md">
								<?php if ($this->config->get('registrations.emailasusername')) { ?>
									<a href="<?php echo ESR::account(array('layout' => 'forgetPassword')); ?>"> <?php echo JText::_('COM_EASYSOCIAL_LOGIN_FORGOT_PASSWORD_FULL'); ?></a>
								<?php } else { ?>
									<a href="<?php echo ESR::account(array('layout' => 'forgetUsername'));?>"> <?php echo JText::_('COM_EASYSOCIAL_LOGIN_FORGOT_USERNAME');?></a> /
									<a href="<?php echo ESR::account(array('layout' => 'forgetPassword'));?>"> <?php echo JText::_('COM_EASYSOCIAL_LOGIN_FORGOT_PASSWORD');?></a>
								<?php } ?>
							</div>

							<?php if ($sso->hasSocialButtons()) { ?>
							<hr class="es-hr" />

							<div class="t-text--center t-lg-mb--md">
								<?php echo JText::_('COM_EASYSOCIAL_SIGN_IN_WITH_SOCIAL_IDENTITY');?>
							</div>

							<div class="es-login-social-container">

								<?php if ($sso->isEnabled('facebook')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('facebook', 'default', '', $returnUrl); ?>
								</div>
								<?php } ?>

								<?php if ($sso->isEnabled('twitter')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('twitter', 'default', '', $returnUrl); ?>
								</div>
								<?php } ?>

								<?php if ($sso->isEnabled('linkedin')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('linkedin', 'default', '', $returnUrl); ?>
								</div>
								<?php } ?>

								<?php if ($sso->isEnabled('google')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('google', 'default', '', $returnUrl); ?>
								</div>
								<?php } ?>

								<?php if ($sso->isEnabled('apple')) { ?>
								<div class="es-login-social-container__cell">
									<?php echo $sso->getLoginButton('apple', 'default', '', $returnUrl); ?>
								</div>
								<?php } ?>

							</div>
							<?php } ?>
						</fieldset>

						<?php echo $this->html('form.action', 'account', 'login'); ?>
						<?php echo $this->html('form.hidden', 'return', $returnUrl); ?>
						<?php echo $this->html('form.hidden', 'returnFailed', base64_encode(ES::getURI(true))); ?>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
