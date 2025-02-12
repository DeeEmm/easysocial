<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-mobile-info">
	<?php echo $this->render('widgets', SOCIAL_TYPE_USER, 'activities', 'mobileBeforeIntro', array($user), 'site/widgets/mobile.wrapper'); ?>


	<div class="es-mobile-filter" data-es-mobile-filters>
		<div class="es-mobile-filter__hd">
			<div class="es-mobile-filter__hd-cell is-slider">
				<div class="es-mobile-filter-slider is-end-left" data-es-sly-slider-group>
					<div class="es-mobile-filter-slider__content">

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_ACTIVITY_SIDEBAR_FILTER', 'filters', in_array($active, array('hiddenapp', 'hidden', 'hiddenactor', 'all')) ? true : false , 'fa fa-eye'); ?>

						<?php if ($apps) { ?>
						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_NOTIFICATIONS_GROUP_OTHERS', 'apps', !in_array($active, array('hiddenapp', 'hidden', 'hiddenactor', 'all')) ? true : false, 'fa fa-lock'); ?>
						<?php } ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_OTHER_LINKS', 'others', false, 'fa fa-link', true); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__bd" data-es-group-filters>
			<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="filters">
				<div class="es-mobile-filter-slider " data-es-swiper-slider>
					<div class="es-mobile-filter-slider__content" data-es-swiper-container>
						<div class="swiper-wrapper">
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES', FRoute::activities(), $active == 'all' ? true : false, array('data-sidebar-item', 'data-type="all"', 'title="' . JText::_('COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES') . '"')); ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS', FRoute::activities(array('type' => 'hiddenapp')), $active == 'hiddenapp' ? true : false, array('data-sidebar-item', 'data-type="hiddenapp"', 'title="' . JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS') . '"')); ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS', FRoute::activities(array('type' => 'hiddenactor')), $active == 'hiddenactor' ? true : false, array('data-sidebar-item', 'data-type="hiddenactor"', 'title="' . JText::_('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS') . '"')); ?>
						</div>
					</div>
				</div>
			</div>

			<?php if ($apps) { ?>
			<div class="es-mobile-filter__group t-hidden" data-es-swiper-group data-type="apps">
				<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
					<div class="es-mobile-filter-slider__content" data-es-swiper-container>
						<div class="swiper-wrapper">
							<?php foreach ($apps as $app) { ?>
								<?php echo $this->html('mobile.filterTab', $app->title, FRoute::activities(array('type' => $app->element)), $active == $app->element ? true : false, array('data-sidebar-item', 'data-type="' . $app->element . '"', 'title="' . $app->title . '"')); ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<div class="es-mobile-filter__group" data-es-swiper-group data-type="others">
			<div class="dl-menu-wrapper t-hidden">
				<div class="es-list">
					<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'edit')), 'linkTitle' => JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE'))); ?>

					<?php if ($this->config->get('privacy.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editPrivacy')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY'))); ?>
					<?php } ?>

					<?php if ($this->config->get('users.blocking.enabled') && $this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'manageBlockedUsers')), 'linkTitle' => JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_MANAGE_BLOCKED_USERS'))); ?>
					<?php } ?>

					<?php if ($this->my->hasCommunityAccess()) { ?>
						<?php echo $this->includeTemplate('site/profile/other.links', array('link' => ESR::profile(array('layout' => 'editNotifications')), 'linkTitle' => JText::_('COM_EASYSOCIAL_MANAGE_ALERTS'))); ?>
					<?php } ?>
				</div>
			</div>
		</div>

		</div>
	</div>

	<?php echo $this->render('widgets', SOCIAL_TYPE_USER, 'activities', 'mobileAfterIntro', array($user), 'site/widgets/mobile.wrapper'); ?>
</div>
