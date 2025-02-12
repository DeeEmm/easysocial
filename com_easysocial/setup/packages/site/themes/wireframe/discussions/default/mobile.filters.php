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
<div class="es-mobile-filter" data-es-mobile-filters data-es-discussions-filter>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider-group>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_DISCUSSIONS_FILTER_ALL', 'all', ($filter == 'all'), 'fa fa-chart-bar', false, array('data-filter-item="all"'), $filterLinks->all); ?>

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_DISCUSSIONS_FILTER_UNANSWERED', 'unanswered', ($filter == 'unanswered'), 'fa fa-chart-bar', false, array('data-filter-item="unanswered"'), $filterLinks->unanswered); ?>

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_DISCUSSIONS_FILTER_RESOLVED', 'resolved', ($filter == 'resolved'), 'fa fa-chart-bar', false, array('data-filter-item="resolved"'), $filterLinks->resolved); ?>

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_DISCUSSIONS_FILTER_UNRESOLVED', 'unresolved', ($filter == 'unresolved'), 'fa fa-chart-bar', false, array('data-filter-item="unresolved"'), $filterLinks->unresolved); ?>

						<?php echo $this->html('mobile.filterGroup', 'APP_GROUP_DISCUSSIONS_FILTER_LOCKED', 'locked', ($filter == 'locked'), 'fa fa-chart-bar', false, array('data-filter-item="locked"'), $filterLinks->locked); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($showCreateButton) { ?>
			<?php echo $this->html('mobile.filterActions',
					array(
						$this->html('mobile.filterAction', 'APP_GROUP_DISCUSSIONS_CREATE_DISCUSSION', $createButtonLink)
					)
			); ?>
		<?php } ?>
	</div>
</div>
