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
			<?php echo $this->html('panel.heading', 'COM_ES_SOCIAL_SETTINGS_APPLE_GENERAL', '', 'administrators/social-integrations/apple-app-configuration'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_APPLE_SETTINGS_ALLOW_REGISTRATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.apple.registration.enabled', $this->config->get('oauth.apple.registration.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_FACEBOOK_SETTINGS_FACEBOOK_OAUTH_REDIRECT_URI'); ?>

					<div class="col-md-7">
						<?php
						$i = 1;
						foreach ($oauthAppleURIs as $oauthAppleURI) { ?>
							<div class="o-input-group mb-10">
								<input type="text" data-oauthuri-input id="apple-oauth-uri-<?php echo $i?>" name="apple-oauth-uri" class="o-form-control" value="<?php echo $oauthAppleURI;?>" size="60" style="pointer-events:none;" />
								<span class="o-input-group__btn"
									data-oauthuri-button
									data-original-title="<?php echo JText::_('COM_ES_COPY_TOOLTIP')?>"
									data-placement="left"
									data-es-provide="tooltip"
								>
									<a href="javascript:void(0);" class="btn btn-es-default-o">
										<i class="far fa-copy"></i>
									</a>
								</span>
							</div>
						<?php $i++; } ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_APPLE_SETTINGS_CLIENT_ID'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.apple.app', $this->config->get('oauth.apple.app')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_APPLE_SETTINGS_TEAM_ID'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.apple.team', $this->config->get('oauth.apple.team')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_APPLE_SETTINGS_KEY_ID'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.apple.key', $this->config->get('oauth.apple.key')); ?>
					</div>
				</div>

				<div class="form-group" data-es-embed-icon>
					<?php echo $this->html('panel.label', 'COM_ES_APPLE_SETTINGS_KEY_FILE'); ?>

					<div class="col-md-7">
						<?php if ($this->config->get('oauth.apple.keyfile')) { ?>
							<div class="mb-20">
								<?php echo $this->config->get('oauth.apple.keyfile'); ?>
							</div>
						<?php } ?>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="apple_keyfile" id="apple_keyfile" class="input" style="width:265px;" data-uniform />
						</div>
					</div>
				</div>

				<?php if ($this->config->get('oauth.apple.keyfile') && $this->config->get('oauth.apple.app') && $this->config->get('oauth.apple.team') && $this->config->get('oauth.apple.key')) { ?>
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_APPLE_SETTINGS_CLIENT_SECRET'); ?>

						<div class="col-md-7">
							<div class="o-input-group t-lg-mb--md">
								<input type="text" data-applesecret-input id="apple-secret" name="apple-secret" class="o-form-control" value="<?php echo $this->config->get('oauth.apple.secret');?>" size="60" style="pointer-events:none;" disabled="disabled" />
								<span class="o-input-group__btn" data-applesecret-button>
									<a href="javascript:void(0);" class="btn btn-es-default-o">
										<?php echo JText::_('COM_ES_GENERATE'); ?>
									</a>
								</span>
							</div>

							<div class="t-lg-mt--lg" style="background:#f9f9fa;padding: 20px;border-radius: 6px;">
								<h3 class="t-lg-mb--lg"><?php echo JText::_('Token Generator');?></h3>

								<p><?php echo JText::_('This token is generated using <b>Web Tokens Generator</b> by StackIdeas. An active EasySocial subscription is required to use this service.'); ?></p>

								<p><?php echo JText::_('The token will be automatically renewed every 6 months via the cronjob as that is the lifespan of a token. Please remember to setup the <b>cronjobs</b> for your site if you have not done so.'); ?></p>

								<a href="https://stackideas.com/docs/easysocial/administrators/social-integrations/apple-app-configuration" target="_blank" class="btn btn-es-default-o btn-sm t-lg-mt--md">
									<i class="far fa-life-ring"></i>&nbsp; Documentation
								</a>
							</div>
						</div>
					</div>
				<?php } ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.apple.registration.type', $this->config->get('oauth.apple.registration.type'), array(
							array('value' => 'simplified', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_SIMPLIFIED'),
							array('value' => 'normal', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_NORMAL')
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LINKEDIN_SETTINGS_PROFILE_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'oauth.apple.profile', 'oauth.apple.profile', $this->config->get('oauth.apple.profile')); ?>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
