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
						<?php foreach ($steps as $step) { ?>
							<?php echo $this->html('mobile.filterTab', $step->get('title'), 'javascript:void(0)', $i == 0 ? true : false, array('data-page-edit-fields-step', 'data-for="' . $step->id . '"', 'data-actions="1"')); ?>
							<?php $i++; ?>
						<?php } ?>

						<?php if ($page->isDraft()) { ?>
							<?php echo $this->html('mobile.filterTab', JText::_('COM_ES_APPROVAL_HISTORY'), 'javascript:void(0)', false, array('data-page-edit-fields-step', 'data-for="history"', 'data-actions="1"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
