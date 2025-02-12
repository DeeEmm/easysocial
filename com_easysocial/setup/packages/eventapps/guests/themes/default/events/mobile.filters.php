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
<div class="es-mobile-filter" data-es-slider>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">

						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'going' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="going"
						>
							<a href="<?php echo $filterLinks->going; ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('APP_EVENT_GUESTS_FILTER_GOING');?>
							</a>
						</div>
						<?php if ($event->getParams()->get('allowmaybe', true)) { ?>
							<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'maybe' ? 'is-active' : '';?>"
								data-es-swiper-item
								data-filter-item="maybe"
							>
								<a href="<?php echo $filterLinks->maybe; ?>" class="btn es-mobile-filter-slider__btn">
									<?php echo JText::_('APP_EVENT_GUESTS_FILTER_MAYBE');?>
								</a>
							</div>
						<?php } ?>
						<?php if ($event->getParams()->get('allownotgoingguest', true)) { ?>
							<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'notgoing' ? 'is-active' : '';?>"
								data-es-swiper-item
								data-filter-item="notgoing"
							>
								<a href="<?php echo $filterLinks->notgoing; ?>" class="btn es-mobile-filter-slider__btn">
									<?php echo JText::_('APP_EVENT_GUESTS_FILTER_NOTGOING');?>
								</a>
							</div>
						<?php } ?>

						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'admin' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="admin"
						>
							<a href="<?php echo $filterLinks->admin; ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('APP_EVENT_GUESTS_FILTER_ADMINS');?>
							</a>
						</div>

						<?php if ($event->isClosed() && ($event->isAdmin() || $event->isOwner())) { ?>
							<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'pending' ? 'is-active' : '';?>"
								data-es-swiper-item
								data-filter-item="pending"
							>
								<a href="<?php echo $filterLinks->pending; ?>" class="btn es-mobile-filter-slider__btn">
									<?php echo JText::_('APP_EVENT_GUESTS_FILTER_PENDING');?>
								</a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
