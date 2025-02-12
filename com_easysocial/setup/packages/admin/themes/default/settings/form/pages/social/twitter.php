<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_SOCIAL_SETTINGS_TWITTER_GENERAL', '', '/administrators/social-integrations/twitter-app-configuration'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_ALLOW_REGISTRATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.twitter.registration.enabled', $this->config->get('oauth.twitter.registration.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_TWITTER_SETTINGS_TWITTER_OAUTH_REDIRECT_URI'); ?>

					<div class="col-md-7">
						<p>Effective <b>June 12th</b>, Twitter has enforced <a href="https://twittercommunity.com/t/action-required-sign-in-with-twitter-users-must-whitelist-callback-urls/105342" target="_blank">Callback URLs to be whitelisted</a>. You will need to copy the links below and add it under the valid Callback URLs section of the Twitter app.</p>
						<?php foreach ($oauthTwitterURIs as $oauthTwitterURI) { ?>
							<div class="o-input-group t-lg-mb--md" data-es-clipboard>
								<input type="text" name="twitter-oauth-uri" class="o-form-control" value="<?php echo $oauthTwitterURI;?>" size="60" style="pointer-events:none;" data-clipboard-input />
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

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_CONSUMER_KEY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.twitter.app', $this->config->get('oauth.twitter.app')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_CONSUMER_SECRET'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'oauth.twitter.secret', $this->config->get('oauth.twitter.secret')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_REGISTRATION_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.twitter.registration.type', $this->config->get('oauth.twitter.registration.type'), array(
							array('value' => 'simplified', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_SIMPLIFIED'),
							array('value' => 'normal', 'text' => 'COM_EASYSOCIAL_FACEBOOK_SETTINGS_NORMAL')
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_PROFILE_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'oauth.twitter.profile', 'oauth.twitter.profile', $this->config->get('oauth.twitter.profile')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_ENABLE_CARDS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.twitter.card.enabled', $this->config->get('oauth.twitter.card.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_CARD_TYPE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'oauth.twitter.card.type', $this->config->get('oauth.twitter.card.type'), array(
							array('value' => 'summary_large_image', 'text' => 'COM_EASYSOCIAL_TWITTER_SETTINGS_CARD_TYPE_SUMMARY'),
							array('value' => 'summary', 'text' => 'COM_EASYSOCIAL_TWITTER_SETTINGS_CARD_TYPE_SUMMARY_LARGE')
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_IMPORT_AVATAR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.twitter.registration.avatar', $this->config->get('oauth.twitter.registration.avatar')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_TWITTER_SETTINGS_IMPORT_COVER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'oauth.twitter.registration.cover', $this->config->get('oauth.twitter.registration.cover')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
	</div>
</div>
