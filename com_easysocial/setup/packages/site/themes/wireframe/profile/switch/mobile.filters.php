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
	<div class="es-mobile-filter__bd" data-es-events-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="about">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php $i = 0; ?>
						<?php foreach ($steps as $step) { ?>
							<?php echo $this->html('mobile.filterTab', $step->get('title'), 'javascript:void(0)', ($i == 0), array('data-profile-edit-fields-step', 'data-for="' . $step->id . '"', 'data-actions="1"')); ?>
							<?php $i++; ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
