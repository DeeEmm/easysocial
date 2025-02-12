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
						<?php $i = 0; ?>
						<?php foreach ($groups as $group) { ?>
							<?php if (isset($alerts[$group]) && $alerts[$group] ) { ?>
								<?php
									$icon = 'far fa-bell';
									if ($group == 'system') {
										$icon = 'fa fa-exclamation-circle';
									}
								?>
								<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_NOTIFICATIONS_GROUP_' . strtoupper($group), $group, $i == 0 ? true : false, $icon); ?>
								<?php $i++; ?>
							<?php } ?>
						<?php } ?>

						<?php if ($customAlerts) { ?>
							<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_CUSTOM_ALERTS', 'custom', false, 'far fa-bell-o'); ?>
						<?php } ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_OTHER_LINKS', 'links', false, 'fa fa-link', true); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-mobile-filter__bd" data-es-events-filters>
		<?php $i = 0; ?>
		<?php foreach ($groups as $group) { ?>
			<?php if (isset($alerts[$group]) && $alerts[$group] ) { ?>

				<div class="es-mobile-filter__group<?php echo $i == 0 ? ' is-active': ''; ?>" data-es-swiper-group data-type="<?php echo $group; ?>">
					<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
						<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
							<div class="swiper-wrapper">
								<?php $j = 0; ?>
								<?php foreach ($alerts[$group] as $element => $alert) { ?>
									<?php echo $this->html('mobile.filterTab', $alert['title'], 'javascript:void(0)', (($j == 0 && !$activeTab) || ($activeTab == $element)), array('data-es-alert-item', 'data-type="' . $element . '"')); ?>
									<?php $j++; ?>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<?php $i++; ?>
			<?php } ?>
		<?php } ?>

		<?php if ($customAlerts) { ?>
			<div class="es-mobile-filter__group" data-es-swiper-group data-type="custom">
				<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
					<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
						<div class="swiper-wrapper">
							<?php foreach ($customAlerts as $customAlert) { ?>
								<?php echo $customAlert->sidebar;?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="es-mobile-filter__group" data-es-swiper-group data-type="links">
			<div class="dl-menu-wrapper t-hidden">
				<div class="es-list">
					<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'edit')), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE'))); ?>

					<?php if ($this->config->get('users.blocking.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'manageBlockedUsers')), 'linkTitle' => JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_MANAGE_BLOCKED_USERS'))); ?>
					<?php } ?>

					<?php if ($this->my->canConfigureMFA() && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'mfa')), 'linkTitle' => JText::_('COM_ES_PROFILE_SIDEBAR_MFA_MANAGE'))); ?>
					<?php } ?>

					<?php if ($this->config->get('privacy.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editPrivacy')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY'))); ?>
					<?php } ?>

					<?php if ($this->config->get('activity.logs.enabled')) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::activities(), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES'))); ?>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
