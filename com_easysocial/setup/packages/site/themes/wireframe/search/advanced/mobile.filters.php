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
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'discover', !$activeFilter ? true : false, 'far fa-compass'); ?>
						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_SEARCH_FILTER', 'filters', $activeFilter ? true : false, 'fa fa-folder'); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->html('mobile.filterActions',
				array($this->html('mobile.filterAction', 'COM_EASYSOCIAL_ADVANCED_SEARCH_NEW_SEARCH', $newSearchLink))
		); ?>
	</div>

	<div class="es-mobile-filter__bd">
		<div class="es-mobile-filter__group <?php echo $activeFilter ? '' : 'is-active';?>" data-es-swiper-group data-type="discover">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php foreach ($adapters as $adapter) { ?>
							<?php echo $this->html('mobile.filterTab', $adapter->getTitle(), $adapter->getLink(), $adapter->type == $type); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group <?php echo !$activeFilter ? '' : 'is-active';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php foreach ($filters as $filter) { ?>
							<?php echo $this->html('mobile.filterTab', $filter->_('title'), $filter->getPermalink(true), $activeFilter && $activeFilter->id == $filter->id,
								array('data-filter-item="custom" data-id="' . $filter->id . '"')
							); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
