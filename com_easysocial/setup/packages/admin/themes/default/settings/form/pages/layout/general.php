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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_FEATURES'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'zeroasplural.enabled', 'COM_ES_GENERAL_SETTINGS_ZERO_AS_PLURAL'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SETTINGS_LOGO'); ?>

					<div class="col-md-7">
						<div class="mb-20">
							<div class="es-img-holder">
								<div class="es-img-holder__remove <?php echo !ES::hasOverride('email_logo') ? 't-hidden' : '';?>">
									<a href="javascript:void(0);" data-image-restore data-type="email_logo">
										<i class="fa fa-times"></i>&nbsp; <?php echo JText::_('COM_ES_REMOVE'); ?>
									</a>
								</div>
								<img src="<?php echo ES::getLogo(false, true); ?>" width="120" data-image-source data-default="<?php echo ES::getLogo(true);?>" />
							</div>
						</div>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="email_logo" id="email_logo" class="input" style="width:265px;" data-uniform />
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_AVATAR_STYLE'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AVATAR_STYLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'layout.avatar.style', $this->config->get('layout.avatar.style'), array(
								array('value' => 'rounded', 'text' => 'Rounded'),
								array('value' => 'square', 'text' => 'Square')
							)); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_LOGIN_LAYOUT'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'login.static.background', 'COM_ES_LOGIN_USE_STATIC_BACKGROUND_COLOUR', '', 'data-es-login-static'); ?>

				<?php echo $this->html('settings.colorpicker', 'login.static.backgroundcolour', 'COM_ES_LOGIN_STATIC_BACKGROUND_COLOUR', '', '#333333'); ?>

				<?php echo $this->html('settings.toggle', 'login.custom.image', 'COM_EASYSOCIAL_LOGIN_SETTINGS_USE_CUSTOM_IMAGE', '', 'data-toggle-upload'); ?>

				<div class="form-group <?php echo $this->config->get('login.custom.image') ? '' : 't-hidden';?>" data-login-image data-has-image="<?php echo ES::login()->hasLoginImage(); ?>" data-default-login-image="<?php echo ES::login()->getDefaultImage(); ?>">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LOGIN_SETTINGS_IMAGE'); ?>

					<div class="col-md-7">
						<div>
							<img src="<?php echo ES::login()->getLoginImage(false, true);?>" class="" data-login-override-image />
						</div>

						<div>
							<input type="file" name="login_image" data-uniform data-login-image-upload />
							<span class="t-lg-ml--md" data-login-image-remove-wrap <?php if (!ES::login()->hasLoginImage()) { ?>style="display: none;"<?php } ?>> <?php echo JText::_( 'COM_EASYSOCIAL_OR' ); ?>
								<a href="javascript:void(0);" class="btn btn-sm btn-es-danger t-lg-ml--sm" data-login-image-remove-button>
									<?php echo JText::_('COM_EASYSOCIAL_REMOVE_LOGIN_IMAGE'); ?>
								</a>
							</span>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="col-md-6">

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_3RD_PARTY_STYLESHEETS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'styles.fontawesome', 'COM_ES_STYLESHEETS_FONT_AWESOME'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_BUTTON_COLOURS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.colorpicker', 'button.primary.bg', 'COM_ES_PRIMARY_BUTTON_BG', '', '#4A90E2'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.primary.text', 'COM_ES_PRIMARY_BUTTON_TEXT', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.success.bg', 'COM_ES_SUCCESS_BUTTON_BG', '', '#4FC251'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.success.text', 'COM_ES_SUCCESS_BUTTON_TEXT', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.danger.bg', 'COM_ES_DANGER_BUTTON_BG', '', '#F65B5B'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.danger.text', 'COM_ES_DANGER_BUTTON_TEXT', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.standard.bg', 'COM_ES_STANDARD_BUTTON_BG', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.standard.text', 'COM_ES_STANDARD_BUTTON_TEXT', '', '#333333'); ?>
			</div>
		</div>

	</div>
</div>
