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
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', $filter != 'profiletype' ? true : false, 'far fa-compass'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_ES_PROFILES', 'profiles', $filter == 'profiletype' ? true : false, 'fa fa-user-tag'); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($this->my->isSiteAdmin() || $this->access->allowed('search.create.filter')) { ?>
			<?php echo $this->html('mobile.filterActions',
					[
						$this->html('mobile.filterAction', 'COM_ES_CREATE_NEW_FILTER', ESR::search(['layout' => 'advanced']))
					]
			); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-group-filters>
		<div class="es-mobile-filter__group <?php echo $filter != 'profiletype' ? 'is-active' : '';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_USERS_FILTER_USERS_ALL_USERS', ESR::users(), (!$filter || $filter == 'all'),
							array('data-filter-item', 'data-type="users" data-id="all"')); ?>

						<?php if ($this->my->id && $this->config->get('friends.enabled')) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_MY_FRIENDS', ESR::users(array('filter' => 'friends')), ($filter == 'friends'),
								array('data-filter-item', 'data-type="users" data-id="friends"')); ?>
						<?php } ?>

						<?php if ($this->my->id && $this->config->get('followers.enabled')) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_MY_FOLLOWERS', ESR::users(array('filter' => 'followers')), ($filter == 'followers'),
								array('data-filter-item', 'data-type="users" data-id="followers"')); ?>
						<?php } ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_USERS_FILTER_USERS_WITH_PHOTOS', ESR::users(array('filter' => 'photos')), ($filter == 'photos'),
							array('data-filter-item', 'data-type="users" data-id="photos"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_USERS_FILTER_ONLINE_USERS', ESR::users(array('filter' => 'online')), ($filter == 'online'),
							array('data-filter-item', 'data-type="users" data-id="online"')); ?>

						<?php if ($this->config->get('users.verification.enabled')) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_USERS_FILTER_VERIFIED_USERS', ESR::users(array('filter' => 'verified')), ($filter == 'verified'),
								array('data-filter-item', 'data-type="users" data-id="verified"')); ?>
						<?php } ?>

						<?php if ($this->config->get('users.blocking.enabled') && $this->my->id) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_USERS_FILTER_BLOCKED', ESR::users(array('filter' => 'blocked')), ($filter == 'blocked'),
								array('data-filter-item', 'data-type="users" data-id="blocked"')); ?>
						<?php } ?>

						<?php if ($searchFilters) { ?>
							<?php foreach ($searchFilters as $searchFilter) { ?>
								<?php echo $this->html('mobile.filterTab', $this->html('string.escape', $searchFilter->get('title')),
									ESR::users(array('filter' => 'search', 'id' => $searchFilter->getAlias())),
									($filter == 'search' && $fid == $searchFilter->id),
									array('data-filter-item', 'data-type="search" data-id="' . $searchFilter->id . '"')
								); ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group <?php echo $filter == 'profiletype' ? 'is-active' : '';?>" data-es-swiper-group data-type="profiles">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php foreach ($profiles as $profile) { ?>
							<?php echo $this->html('mobile.filterTab', $this->html('string.escape', $profile->get('title')),
								ESR::users(array('filter' => 'profiletype', 'id' => $profile->getAlias())),
								($filter == 'profiletype' && $activeProfile && $activeProfile->id == $profile->id),
								array('data-filter-item', 'data-type="profiles" data-id="' . $profile->id . '"')
							); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
