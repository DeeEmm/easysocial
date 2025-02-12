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
<div id="es" class="mod-es mod-es-register<?php echo $lib->isMobile() ? ' is-mobile' : '';?> <?php echo $lib->getSuffix();?>" data-mod-register>
	<div class="es-register-box-wrap">
		<div style="background-image: url('<?php echo $splashImage;?>');" class="es-register-box">

			<div class="o-row">
				<div class="o-col--6">
					<div class="es-register-box__msg">
						<div class="es-register-box__msg-title"><?php echo JText::_($params->get('heading_title', 'MOD_EASYSOCIAL_REGISTER_TITLE'));?></div>
						<div class="es-register-box__msg-desc"><?php echo JText::_($params->get('heading_desc', 'MOD_EASYSOCIAL_REGISTER_DESC')); ?></div>
					</div>
				</div>

				<div class="o-col--6">
					<div class="es-register-box__form-wrap">
						<form name="loginbox" id="loginbox" method="post" action="<?php echo JRoute::_('index.php');?>" class="es-register-box__form" data-mod-register-form>

							<fieldset class="t-lg-mb--lg">

								<?php foreach ($fields as $field) { ?>
									<?php if ($field->visible_mini_registration) { ?>
									<div class="es-register-mini-field" data-quickregistermini-fields-item><?php echo $field->output;?></div>
									<?php } ?>
								<?php } ?>

								<?php echo $lib->html('form.honeypot'); ?>

								<button type="submit" class="btn btn-es-primary btn-block" data-quickregistermini-submit><?php echo JText::_('COM_EASYSOCIAL_LOGIN_REGISTER_NOW_BUTTON');?></button>

							</fieldset>

							<?php if ($params->get('social', true) && $sso->hasSocialButtons()) { ?>
							<div class="es-register-box__divider"><span><?php echo JText::_('MOD_EASYSOCIAL_REGISTER_OR_REGISTER_WITH_YOUR_SOCIAL_IDENTITY');?></span></div>

							<div class="es-register-box__social-group t-lg-mt--lg">
								<?php foreach ($sso->getSocialButtons() as $socialButton) { ?>
								<div class="t-lg-mt--md">
									<?php echo $socialButton; ?>
								</div>
								<?php } ?>
							</div>
							<?php } ?>

							<?php echo $lib->html('form.token'); ?>
							<input type="hidden" name="option" value="com_easysocial" />
							<input type="hidden" name="controller" value="registration" />
							<input type="hidden" name="task" value="miniRegister" />
							<input type="hidden" name="redirect" value="<?php echo base64_encode(ES::getURI(true));?>" />
							<input type="hidden" name="modRegisterType" value="<?php echo $registerType; ?>" />
							<input type="hidden" name="modRegisterProfile" value="<?php echo $profileId; ?>" />
						</form>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>

<script>
EasySocial
.require()
.script('shared/fields/base', 'shared/fields/validate')
.done(function($) {

	// Implement the controller on the fields
	$('[data-quickregistermini-fields-item]').addController(EasySocial.Controller.Field.Base, {
		"userid": 0,
		"model": "registermini"
	});

	$('[data-quickregistermini-submit]').on('click', function(event) {
		event.preventDefault();

		var button = $(this);

		// Get the unique quick registration form
		var form = button.parents('[data-mod-register-form]');

		if (button.enabled()) {

			// Disable the button to prevent multiple clicks
			button.disabled(true);

			form.validate({
				"mode": "onRegisterMini"

			}).done(function() {
				button.enabled(true);
				form.submit();

			}).fail(function() {
				button.enabled(true);
			});
		}
	});
});
</script>
