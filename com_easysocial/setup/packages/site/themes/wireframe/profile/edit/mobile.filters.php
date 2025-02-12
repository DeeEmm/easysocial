<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_ABOUT', 'about', true, 'far fa-address-book'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_YOUR_PROFILE', 'profiles', false, 'far fa-user', true); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_OTHER_LINKS', 'others', false, 'fa fa-link', true); ?>

						<?php if ($this->my->deleteable()) { ?>
							<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_DELETE_YOUR_PROFILE_BUTTON', '', false, '', true, array('data-profile-edit-delete'), 'javascript:void(0);', 'btn btn-es-danger-o'); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-mobile-filter__bd" data-es-events-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="about">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php $i = 0; ?>
						<?php foreach ($allSteps as $step) { ?>
							<?php echo $this->html('mobile.filterTab', $step->get('title'), 'javascript:void(0)', ($i == 0 && !$activeStep) || ($activeStep && $activeStep == $step->id), array('data-profile-edit-fields-step', 'data-for="' . $step->id . '"', 'data-actions="1"')); ?>
							<?php $i++; ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group" data-es-swiper-group data-type="profiles">
			<div class="dl-menu-wrapper t-hidden">
				<div class="es-list">
					<div class="es-list__item">
						<div class="es-list-item es-island">
							<div class="es-list-item__context">
								<div class="es-list-item__hd">
									<div class="es-list-item__content">
										<div class="es-list-item__title">
											<?php echo JText::sprintf('COM_EASYSOCIAL_PROFILE_SIDEBAR_YOUR_PROFILE_INFO', '<a href="' . $profile->getPermalink() . '">' . $profile->getTitle() . '</a>');?>

											<?php if ($profilesCount > 1 && $this->my->canSwitchProfile()) { ?>
											<a href="<?php echo ESR::profile(array('layout' => 'switchProfile'));?>" class="btn btn-es-default-o btn-sm btn-block t-lg-mt--md">
												<?php echo JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_SWITCH_PROFILE');?>
											</a>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group" data-es-swiper-group data-type="others">
			<div class="dl-menu-wrapper t-hidden">
				<div class="es-list">

					<?php if ($this->config->get('users.blocking.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'manageBlockedUsers')), 'linkTitle' => JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_MANAGE_BLOCKED_USERS'))); ?>
					<?php } ?>

					<?php if ($this->my->canConfigureMFA() && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'mfa')), 'linkTitle' => JText::_('COM_ES_PROFILE_SIDEBAR_MFA_MANAGE'))); ?>
					<?php } ?>

					<?php if ($this->config->get('privacy.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editPrivacy')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY'))); ?>
					<?php } ?>

					<?php if ($this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editNotifications')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_ALERTS'))); ?>
					<?php } ?>

					<?php if ($showVerificationLink && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'submitVerification')), 'linkTitle' => JText::_('COM_ES_SUBMIT_VERIFICATION'))); ?>
					<?php } ?>

					<?php if ($this->config->get('activity.logs.enabled')) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::activities(), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES'))); ?>
					<?php } ?>

					<?php if ($this->config->get('users.download.enabled')) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'download')), 'linkTitle' => JText::_('COM_ES_GDPR_DOWNLOAD_YOUR_INFORMATION'))); ?>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
