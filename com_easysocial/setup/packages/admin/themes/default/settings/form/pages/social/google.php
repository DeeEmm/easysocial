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
			<?php echo $this->html('panel.heading', 'COM_ES_SOCIAL_SETTINGS_GOOGLE_GENERAL', '', '/administrators/social-integrations/google-configuration'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GOOGLE_SETTINGS_GOOGLE_OAUTH_REDIRECT_URI'); ?>

					<div class="col-md-7">

						<p>You will need to copy the links below and add it under <code>Authorized redirect URIs</code> in Google console.</p>

						<?php foreach ($oauthGoogleURIs as $uri) { ?>
						<div class="o-input-group" data-es-clipboard>
							<input type="text" class="o-form-control" value="<?php echo $uri;?>" size="60" style="pointer-events:none;" data-clipboard-input />
							<span class="o-input-group__btn"
								data-clipboard-copy
								data-es-provide="tooltip"
								data-original-title="<?php echo JText::_('COM_ES_COPY_TOOLTIP')?>"
								data-title-copy="<?php echo JText::_('COM_ES_COPY_TOOLTIP')?>"
								data-title-copied="<?php echo JText::_('COM_ES_COPIED_TOOLTIP');?>"
								data-placement="left"
							>
								<a href="javascript:void(0);" class="btn btn-es-default-o">
									<i class="far fa-copy"></i>
								</a>
							</span>
						</div>
						<?php } ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'oauth.google.registration.enabled', 'COM_ES_GOOGLE_SETTINGS_ALLOW_REGISTRATION'); ?>
				<?php echo $this->html('settings.textbox', 'oauth.google.app', 'COM_ES_GOOGLE_SETTINGS_CLIENT_ID'); ?>
				<?php echo $this->html('settings.textbox', 'oauth.google.secret', 'COM_ES_GOOGLE_SETTINGS_CLIENT_SECRET'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GOOGLE_SETTINGS_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.google.registration.type', $this->config->get('oauth.google.registration.type'), array(
							array('value' => 'simplified', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_SIMPLIFIED'),
							array('value' => 'normal', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_NORMAL')
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_PROFILE_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'oauth.google.profile', 'oauth.google.profile', $this->config->get('oauth.google.profile')); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'oauth.google.registration.avatar', 'COM_ES_GOOGLE_SETTINGS_IMPORT_AVATAR'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
