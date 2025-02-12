<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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

						<div class="es-mobile-filter-slider__item swiper-slide<?php echo $filter == 'all' ? ' is-active' : '';?>" data-es-swiper-item>
							<a href="<?php echo ESR::albums(); ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_ALL_ALBUMS');?>
							</a>
						</div>

						<?php if ($this->my->id) { ?>
						<div class="es-mobile-filter-slider__item swiper-slide" data-es-swiper-item>
							<a href="<?php echo ESR::albums(array('layout' => 'mine'));?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_MY_ALBUMS');?>
							</a>
						</div>

						<div class="es-mobile-filter-slider__item swiper-slide<?php echo $filter == 'favourite' ? ' is-active' : '';?>" data-es-swiper-item>
							<a href="<?php echo ESR::albums(array('layout' => 'favourite')); ?>" class="btn es-mobile-filter-slider__btn">
								<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_FAVOURITE_ALBUMS');?>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($lib->canCreateAlbums() && !$lib->exceededLimits()) { ?>
			<?php echo $this->html('mobile.filterActions',
					array($this->html('mobile.filterAction', 'COM_EASYSOCIAL_ALBUMS_NEW_ALBUM', $lib->getCreateLink()))
			); ?>
		<?php } ?>
	</div>
</div>

