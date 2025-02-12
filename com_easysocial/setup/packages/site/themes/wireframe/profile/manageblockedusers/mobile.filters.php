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
						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_BLOCKED_USERS', 'blocked', true, 'fa fa-eye'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_OTHER_LINKS', 'others', false, 'fa fa-link', true); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-mobile-filter__bd" data-es-events-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="blocked">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<div class="es-mobile-filter-slider__item swiper-slide is-active swiper-slide-active">
							<a href="<?php echo ESR::profile(array('layout' => 'manageBlockedUsers'));?>" class="es-mobile-filter-slider__tab"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_MANAGE_BLOCKED_USERS');?></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group" data-es-swiper-group data-type="others">
			<div class="dl-menu-wrapper t-hidden">
				<div class="es-list">
					<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'edit')), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE'))); ?>

					<?php if ($this->my->canConfigureMFA() && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'mfa')), 'linkTitle' => JText::_('COM_ES_PROFILE_SIDEBAR_MFA_MANAGE'))); ?>
					<?php } ?>

					<?php if ($this->config->get('privacy.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editPrivacy')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY'))); ?>
					<?php } ?>

					<?php if ($this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editNotifications')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_ALERTS'))); ?>
					<?php } ?>

					<?php if ($this->config->get('activity.logs.enabled')) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::activities(), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES'))); ?>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>