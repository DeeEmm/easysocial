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
<div class="es-mobile-filter" data-es-mobile-filters data-es-followers-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', true, 'far fa-compass'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-mobile-filter__bd" data-es-group-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">

						<?php echo $this->html('mobile.filterTab', $filters->followers->label, $filters->followers->link, ($filter == 'followers'),
												array('data-filter-item', 'data-type="followers"', 'title="' . $filters->followers->page_title . '"')); ?>

						<?php echo $this->html('mobile.filterTab', $filters->following->label, $filters->following->link, ($filter == 'following'),
												array('data-filter-item', 'data-type="following"', 'title="' . $filters->following->page_title . '"')); ?>

						<?php echo $this->html('mobile.filterTab', $filters->suggestion->label, $filters->suggestion->link, ($filter == 'suggest'),
												array('data-filter-item', 'data-type="suggest"', 'title="' . $filters->suggestion->page_title . '"')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
