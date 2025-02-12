<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-dropdown-menu <?php echo $lib->getSuffix();?>">
	<?php if ($lib->my->id) { ?>
	<div class="dropdown_">
		<div class="dropdown-toggle_ fd-cf" data-es-toggle="dropdown" data-lock-scroll="0">
			<div class="o-media">
				<div class="o-media__image">
					<?php echo $lib->html('avatar.user', $lib->my, 'default', true, true, '', false); ?>
				</div>
				<div class="o-media__body t-text--left mod-es-dropdown-menu__body">
					<span class="mod-es-dropdown-menu__name">
					<?php echo JText::_('MOD_EASYSOCIAL_DROPDOWN_MENU_HI');?>, <b><?php echo $lib->my->getName();?></b></span> <i class="i-chevron i-chevron--down t-lg-ml--sm t-lg-mt--sm"></i>
				</div>
			</div>
		</div>

		<ul class="dropdown-menu dropdown-menu-full mod-es-dropdown-menu__dropdown-menu">
			<?php if ($params->get('show_my_profile', true)) { ?>
			<li>
				<a href="<?php echo $my->getPermalink();?>">
					<?php echo JText::_('MOD_EASYSOCIAL_DROPDOWN_MENU_MY_PROFILE');?>
				</a>
			</li>
			<?php } ?>

			<?php if ($params->get('show_account_settings', true)) { ?>
			<li>
				<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>">
					<?php echo JText::_('MOD_EASYSOCIAL_DROPDOWN_MENU_ACCOUNT_SETTINGS');?>
				</a>
			</li>
			<?php } ?>

			<?php if ($items) { ?>
				<?php if ($params->get('show_my_profile') || $params->get('show_account_settings')) { ?>
				<li class="divider"></li>
				<?php } ?>
				<?php foreach ($items as $item) { ?>
					<li class="menu-<?php echo $item->id;?>" style="padding-left: <?php echo $item->padding; ?>px;">
						<?php if ($item->type == 'separator') { ?>
						<span><?php echo $item->title;?></span>
						<?php } else { ?>
						<a href="<?php echo $item->flink;?>"><?php echo $item->title;?></a>
						<?php } ?>
					</li>
				<?php } ?>
			<?php } ?>

			<?php if ($params->get('show_sign_out', true)) { ?>
			<li class="divider"></li>
			<li>
				<a href="javascript:void(0);" onclick="document.getElementById('es-dropdown-logout-form').submit();"><?php echo JText::_('MOD_EASYSOCIAL_DROPDOWN_MENU_SIGN_OUT');?></a>
				<form class="logout-form" action="<?php echo JRoute::_('index.php');?>" id="es-dropdown-logout-form" method="post">
					<input type="hidden" name="return" value="<?php echo $logoutReturn;?>" />
					<input type="hidden" name="option" value="com_easysocial" />
					<input type="hidden" name="controller" value="account" />
					<input type="hidden" name="task" value="logout" />
					<input type="hidden" name="view" value="" />
					<?php echo $lib->html('form.token'); ?>
				</form>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } else { ?>
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm"
			data-module-dropdown-login-wrapper
			data-popbox=""
			data-popbox-id="es"
			data-popbox-component="popbox--navbar"
			data-popbox-type="navbar-signin"
			data-popbox-toggle="click"
			data-popbox-target=".mod-popbox-dropdown"
			data-popbox-position="<?php echo $params->get('popbox_position', 'bottom'); ?>"
			data-popbox-collision="<?php echo $params->get('popbox_collision', 'flip'); ?>"
			data-popbox-offset="<?php echo $params->get('popbox_offset', 10); ?>"
			rel="nofollow"
		>
			<i class="fa fa-lock"></i>&nbsp; <?php echo JText::_('MOD_EASYSOCIAL_DROPDOWN_MENU_SIGN_IN');?>
		</a>

		<?php if ($params->get('register_button', true)) { ?>
		<a href="<?php echo ESR::registration(); ?>" class="btn btn-es-primary btn-sm">
			<i class="fa fa-globe"></i>&nbsp; <?php echo JText::_('MOD_EASYSOCIAL_DROPDOWN_MENU_REGISTER'); ?>
		</a>
		<?php } ?>

		<div data-module-dropdown-login class="mod-popbox-dropdown" style="display:none;">
			<div class="popbox-dropdown">
				<div class="popbox-dropdown__hd">
					<div class="o-flag o-flag--rev">
						<div class="o-flag__body">
							<div class="popbox-dropdown__title"><?php echo JText::_('COM_EASYSOCIAL_SIGN_IN');?></div>
						</div>
					</div>
				</div>

				<div class="popbox-dropdown__bd">
					<form action="<?php echo JRoute::_('index.php');?>" method="post" class="popbox-dropdown-signin">

						<?php echo ES::themes()->html('form.floatinglabel', $loginPlaceholder, 'username'); ?>

						<?php echo ES::themes()->html('form.floatinglabel', 'COM_EASYSOCIAL_TOOLBAR_PASSWORD', 'password', 'password'); ?>

						<?php if ($config->get('general.site.twofactor')) { ?>
							<?php echo ES::themes()->html('form.floatinglabel', 'COM_EASYSOCIAL_LOGIN_TWOFACTOR_SECRET', 'secretkey', 'text', '', '', false, '', true); ?>
						<?php } ?>

						<div class="o-row">
							<div class="o-col o-col--8" <?php echo $showRememberMe ? '' : 'style="display: none;"'; ?>>
								<div class="o-checkbox">
									<input type="checkbox" name="remember" id="es-mod-remember"<?php echo $checkRememberMe ? ' checked="checked"' : ''; ?> />
									<label for="es-mod-remember"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_REMEMBER_ME');?></label>
								</div>
							</div>
							<div class="o-col">
								<button class="btn btn-es-primary t-lg-pull-right" data-login-submit><?php echo JText::_('COM_EASYSOCIAL_LOGIN_BUTTON');?></button>
							</div>
						</div>

						<?php if ($sso->hasSocialButtons()) { ?>
						<div class="popbox-dropdown__social t-lg-mt--md">
							<?php foreach ($sso->getSocialButtons() as $socialButton) { ?>
							<div class="t-text--center t-lg-mt--md">
								<?php echo $socialButton; ?>
							</div>
							<?php } ?>
						</div>
						<?php } ?>

						<input type="hidden" name="option" value="com_easysocial" />
						<input type="hidden" name="controller" value="account" />
						<input type="hidden" name="task" value="login" />
						<input type="hidden" name="return" value="<?php echo $loginReturn;?>" />
						<?php echo $lib->html('form.token');?>
					</form>
				</div>

				<div class="popbox-dropdown__ft">
					<ul class="g-list-inline g-list-inline--dashed t-text--center">
						<?php if (!$config->get('registrations.emailasusername')) { ?>
						<li>
							<a href="<?php echo ESR::account(array('layout' => 'forgetUsername'));?>" class="popbox-dropdown__note">
								<?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_FORGOT_USERNAME');?>
							</a>
						</li>
						<?php } ?>
						<li>
							<a href="<?php echo ESR::account(array('layout' => 'forgetPassword'));?>" class="popbox-dropdown__note">
								<?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_FORGOT_PASSWORD');?>
							</a>
						</li>
					</ul>

				</div>
			</div>
		</div>
	<?php } ?>
</div>
