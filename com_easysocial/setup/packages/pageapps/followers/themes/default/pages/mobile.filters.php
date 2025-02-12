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
<div class="es-mobile-filter" data-es-slider>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'all' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="all"
						>
							<a href="<?php echo $filterLinks->all; ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('APP_PAGE_FOLLOWERS_FILTER_ALL');?>
							</a>
						</div>

						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'followers' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="followers"
						>
							<a href="<?php echo $filterLinks->followers; ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('APP_PAGE_FOLLOWERS_FILTER_FOLLOWERS');?>
							</a>
						</div>

						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'admin' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="admin"
						>
							<a href="<?php echo $filterLinks->admin; ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('APP_PAGE_FOLLOWERS_FILTER_ADMINS');?>
							</a>
						</div>

						<?php if ($page->isClosed() && ($page->isAdmin($this->my->id) || $page->isOwner($this->my->id))) { ?>
						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $active == 'pending' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="pending"
						>
							<a href="<?php echo $filterLinks->pending; ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('APP_PAGE_FOLLOWERS_FILTER_PENDING');?>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
