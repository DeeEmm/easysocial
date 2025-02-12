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

						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', ($mobileFilter == 'discover'), 'far fa-compass'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_EVENTS_CATEGORIES_SIDEBAR_TITLE', 'categories', ($mobileFilter == 'categories'), 'fa fa-folder', true); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_EVENTS_FILTER_BY_DATE_SIDEBAR_TITLE', 'date', ($mobileFilter == 'date'), 'fa fa-clock', false); ?>

						<div class="es-mobile-filter-slider__item swiper-slide" data-swiper-filter-item>
							<a href="<?php echo ESR::events(array('layout' => 'calendar')); ?>" class="btn es-mobile-filter-slider__btn">
								<i class="far fa-calendar"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_EVENTS_CALENDAR_WIDGET_TITLE');?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if ((!$cluster && $activeUser->isViewer() && $this->my->canCreateEvents()) || ($cluster && $cluster->canCreateEvent())) { ?>
			<?php echo $this->html('mobile.filterActions', array($this->html('mobile.filterAction', 'COM_EASYSOCIAL_EVENTS_CREATE_EVENT', ESR::events($createUrl)))); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-events-filters>
		<div class="es-mobile-filter__group <?php echo $mobileFilter == 'discover' ? 'is-active' : '';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php if ($browseView) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_ALL', $filtersLink->all, ($filter == 'all' && !$activeCategory), array('data-filter-item', 'data-type="all"')); ?>

							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_FEATURED', $filtersLink->featured, ($filter == 'featured' && !$activeCategory), array('data-filter-item', 'data-type="featured"')); ?>
						<?php } else { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_ES_CREATED_EVENTS', $filtersLink->created, ($filter == 'created' && !$activeCategory), array('data-filter-item', 'data-type="created"')); ?>

							<?php echo $this->html('mobile.filterTab', 'COM_ES_PARTICIPATED_EVENTS', $filtersLink->participated, ($filter == 'participated' && !$activeCategory), array('data-filter-item', 'data-type="participated"')); ?>
						<?php } ?>

						<?php if ($showMyEvents) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_MINE', ES::event()->getFilterPermalink(array('filter' => 'mine', 'cluster' => $cluster)), ($filter == 'mine' && !$activeCategory), array('data-filter-item', 'data-type="mine"')); ?>
						<?php } ?>

						<?php if ($showPendingEvents) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_REVIEW', $filtersLink->pending, ($filter == 'pending' && !$activeCategory), array('data-filter-item', 'data-type="pending"')); ?>
						<?php } ?>

						<?php if ($showTotalInvites) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_INVITED', $filtersLink->invited, ($filter == 'invited' && !$activeCategory), array('data-filter-item', 'data-type="invited"')); ?>
						<?php } ?>

						<?php if ($browseView) { ?>
							<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_NEARBY', ESR::events(array('filter' => 'nearby')), ($filter == 'nearby' && !$activeCategory), array('data-filter-item', 'data-type="nearby"')); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($browseView) { ?>
		<div class="es-mobile-filter__group <?php echo $mobileFilter == 'date' ? 'is-active' : '';?>" data-es-swiper-group data-type="date">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_TODAY', $dateLinks->today, ($filter == 'date' && $activeDateFilter == 'today'), array('data-filter-item', 'data-type="today"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_TOMORROW', $dateLinks->tomorrow, ($filter == 'date' && $activeDateFilter == 'tomorrow'), array('data-filter-item', 'data-type="tomorrow"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_WEEK1', ES::event()->getFilterPermalink(array('filter' => 'week1', 'cluster' => $cluster)), ($filter == 'week1'), array('data-filter-item', 'data-type="week1"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_WEEK2', ES::event()->getFilterPermalink(array('filter' => 'week2', 'cluster' => $cluster)), ($filter == 'week2'), array('data-filter-item', 'data-type="week2"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_MONTH', $dateLinks->month, ($filter == 'date' && $activeDateFilter == 'month'), array('data-filter-item', 'data-type="month"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_YEAR', $dateLinks->year, ($filter == 'date' && $activeDateFilter == 'year'), array('data-filter-item', 'data-type="year"')); ?>

						<?php echo $this->html('mobile.filterTab', 'COM_EASYSOCIAL_EVENTS_FILTER_PAST', ES::event()->getFilterPermalink(array('filter' => 'past', 'cluster' => $cluster)), ($filter == 'past'), array('data-filter-item', 'data-type="past"')); ?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->html('categories.sidebar', SOCIAL_TYPE_EVENT, $activeCategory, array(), false) ?>
	</div>
</div>
