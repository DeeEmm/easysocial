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
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', $activeCategory ? false : true, 'far fa-compass'); ?>
						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_PAGES_CATEGORIES_SIDEBAR_TITLE', 'categories', $activeCategory ? true : false, 'fa fa-folder', true); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if (($user && $user->isViewer() && $this->my->canCreatePages()) || (!$user && $this->my->canCreatePages())) { ?>
			<?php echo $this->html('mobile.filterActions',
				array(
					$this->html('mobile.filterAction', 'COM_EASYSOCIAL_PAGES_START_YOUR_PAGE', ESR::pages(array('layout' => 'create')))
				)
			); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-page-filters>
		<div class="es-mobile-filter__group <?php echo $activeCategory ? '' : 'is-active';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">

						<?php if ($browseView) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_ALL_PAGES', $filters->all, ($filter == 'all' && !$activeCategory),
													array('data-filter-item', 'data-type="all"')); ?>

							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_FEATURED_PAGES', $filters->featured, ($filter == 'featured' && !$activeCategory),
										array('data-filter-item', 'data-type="featured"'));?>

						<?php } else { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_CREATED_PAGES', $filters->created, ($filter == 'created' && !$activeCategory),
										array('data-filter-item', 'data-type="created"'));?>

							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_PARTICIPATED_PAGES', $filters->participated, ($filter == 'participated' && !$activeCategory),
										array('data-filter-item', 'data-type="participated"'));?>
						<?php } ?>

						<?php if ($showMyPages) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_MY_PAGES', $filters->participated, ($filter == 'mine' && !$activeCategory),
										array('data-filter-item', 'data-type="mine"'));?>

							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_MY_LIKED_PAGES', ESR::pages(array('filter' => 'liked')), ($filter == 'liked' && !$activeCategory),
										array('data-filter-item', 'data-type="liked"'));?>

							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_MY_UNPUBLISHED_PAGES', ESR::pages(array('filter' => 'unpublished')), ($filter == 'unpublished' && !$activeCategory),
										array('data-filter-item', 'data-type="unpublished"'));?>
						<?php } ?>

						<?php if ($showPendingPages) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_FILTER_PENDING', $filters->pending, ($filter == 'pending' && !$activeCategory),
										array('data-filter-item', 'data-type="pending"'));?>
						<?php } ?>

						<?php if ($showInvites) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGES_INVITED', $filters->invited, ($filter == 'invited' && !$activeCategory),
										array('data-filter-item', 'data-type="invited"'));?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->html('categories.sidebar', SOCIAL_TYPE_PAGE, $activeCategory) ?>
	</div>
</div>
