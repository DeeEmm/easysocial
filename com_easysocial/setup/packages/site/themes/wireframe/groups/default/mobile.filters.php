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
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', true, 'far fa-compass'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_GROUPS_CATEGORIES_SIDEBAR_TITLE', 'categories', false, 'fa fa-folder', true); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($user->isViewer() && $this->my->canCreateGroups()) { ?>
			<?php echo $this->html('mobile.filterActions', array($this->html('mobile.filterAction', 'COM_EASYSOCIAL_GROUPS_START_YOUR_GROUP', ESR::groups(array('layout' => 'create'))))); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-group-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php if ($browseView) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_GROUPS_FILTER_ALL_GROUPS', $filters->all, ($filter == 'all' && !$activeCategory),
													array('data-filter-item', 'data-type="all"')); ?>

							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_GROUPS_FILTER_FEATURED_GROUPS', $filters->featured, ($filter == 'featured' && !$activeCategory),
													array('data-filter-item', 'data-type="featured"')); ?>
						<?php } else { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_GROUPS_FILTER_CREATED_GROUPS', $filters->created, ($filter == 'created' && !$activeCategory),
													array('data-filter-item', 'data-type="created"')); ?>

							<?php echo $this->html('mobile.filterTab', 'COM_ES_GROUPS_FILTER_PARTICIPATED_GROUPS', $filters->participated, ($filter == 'participated' && !$activeCategory),
													array('data-filter-item', 'data-type="participated"')); ?>
						<?php } ?>

						<?php if ($filtersAcl->mine) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_GROUPS_FILTER_MY_GROUPS', $filters->mine, ($filter == 'mine' && !$activeCategory),
													array('data-filter-item', 'data-type="mine"')); ?>
						<?php } ?>

						<?php if ($filtersAcl->pending) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_GROUPS_FILTER_PENDING', $filters->pending, ($filter == 'pending' && !$activeCategory),
													array('data-filter-item', 'data-type="pending"')); ?>
						<?php } ?>

						<?php if ($filtersAcl->invites) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_GROUPS_INVITED', $filters->invited, ($filter == 'invited' && !$activeCategory),
													array('data-filter-item', 'data-type="invited"')); ?>
						<?php } ?>

						<?php if ($filtersAcl->nearby) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_NEARBY_GROUPS', ESR::groups(array('filter' => 'nearby')), ($filter == 'nearby' && !$activeCategory),
													array('data-filter-item', 'data-type="nearby"')); ?>
						<?php } ?>

						<?php if ($filtersAcl->unpublished) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_UNPUBLISHED_GROUPS', ESR::groups(array('filter' => 'unpublished')), ($filter == 'unpublished' && !$activeCategory),
													array('data-filter-item', 'data-type="unpublished"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->html('categories.sidebar', SOCIAL_TYPE_GROUP, $activeCategory) ?>
	</div>

</div>
