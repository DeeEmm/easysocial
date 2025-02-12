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
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', ($filter != 'customFilter'), 'far fa-compass'); ?>

						<?php if ($canCreateFilter && $browseView) { ?>
							<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_CUSTOM_FILTERS', 'hashtags', ($filter == 'customFilter' && $activeCustomFilter), 'fa fa-hashtag'); ?>
						<?php } ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_VIDEOS_CATEGORIES', 'categories', false, 'fa fa-folder', true); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($allowCreation) { ?>
			<?php
			$filterActions = array();
			$filterActions[] = $this->html('mobile.filterAction', 'COM_EASYSOCIAL_VIDEOS_ADD_VIDEO', $createLink);

			if ($canCreateFilter) {
				$filterActions[] = $this->html('mobile.filterAction', 'COM_ES_NEW_FILTER', 'javascript:void(0);', array('data-video-create-filter', 'data-type="videos"', 'data-uid="' . $uid . '"', 'data-cluster-type="' . $type . '"'));
			}
			?>
			<?php echo $this->html('mobile.filterActions', $filterActions); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-video-filters>
		<div class="es-mobile-filter__group  <?php echo $filter != 'customFilter' ? 'is-active' : '';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_VIDEOS_FILTERS_ALL_VIDEOS', $adapter->getAllVideosLink(), ($filter == '' || $filter == 'all'),
							array('data-filter-item', 'data-type="all"'), array('title="' . $titles->all . '"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_VIDEOS_FILTERS_FEATURED_VIDEOS', $adapter->getAllVideosLink('featured'), ($filter == 'featured'),
							array('data-filter-item', 'data-type="featured"'), array('title="' . $titles->featured . '"')); ?>

						<?php if ($filtersAcl->mine) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_VIDEOS_FILTERS_MY_VIDEOS', ESR::videos(array('filter' => 'mine')), ($filter == 'mine'),
								array('data-filter-item', 'data-type="mine"'), array('title="' . $titles->mine . '"')); ?>
						<?php } ?>

						<?php if ($filtersAcl->pending) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_VIDEOS_FILTERS_PENDING_VIDEOS', ESR::videos(array('filter' => 'pending')), ($filter == 'pending'),
								array('data-filter-item', 'data-type="pending"'), array('title="' . $titles->pending . '"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($customFilters) { ?>
		<div class="es-mobile-filter__group <?php echo $filter == 'customFilter' && $activeCustomFilter ? 'is-active' : '';?>" data-es-swiper-group data-type="hashtags">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php foreach ($customFilters as $customFilter) { ?>
						<?php echo $this->html('mobile.filterTab', '#' . $customFilter->title, $customFilter->permalink, ($filter == 'customFilter' && $activeCustomFilter && $activeCustomFilter->id == $customFilter->id),
							array('data-filter-item', 'data-type="hashtag" data-tag-id="' . $customFilter->id . '"'), array('title="' . $customFilter->title . '"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->html('categories.sidebar', SOCIAL_TYPE_VIDEO, $activeCategory) ?>
	</div>
</div>
