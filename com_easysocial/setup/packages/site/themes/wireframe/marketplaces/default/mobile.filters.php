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
<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', $activeCategory ? false : true, 'far fa-compass'); ?>
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_MARKETPLACES_CATEGORIES_SIDEBAR_TITLE', 'categories', $activeCategory ? true : false, 'fa fa-folder', true); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ((!$cluster && $activeUser->isViewer() && $this->my->canCreateListing()) || ($cluster && $cluster->canCreateListing())) { ?>
			<?php echo $this->html('mobile.filterActions',
				array(
					$this->html('mobile.filterAction', 'COM_ES_MARKETPLACES_ADD_LISTING', ESR::marketplaces($createUrl))
				)
			); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-marketpalce-filters>

		<div class="es-mobile-filter__group <?php echo $activeCategory ? '' : 'is-active';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php foreach ($filters as $key => $filter) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_' . $key, $filters->{$key}, ($filter == $key && !$activeCategory), array('data-filter-item', 'data-type="' . $key . '"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->html('categories.sidebar', SOCIAL_TYPE_MARKETPLACE, $activeCategory) ?>
	</div>
</div>
