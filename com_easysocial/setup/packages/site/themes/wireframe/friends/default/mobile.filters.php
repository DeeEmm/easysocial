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
						<?php echo $this->html('mobile.filterGroup', 'COM_ES_DISCOVER', 'filters', !$activeList, 'far fa-compass'); ?>

						<?php echo $this->html('mobile.filterGroup', 'COM_EASYSOCIAL_FRIENDS_YOUR_LIST', 'lists', $activeList, 'far fa-list-alt'); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ($user->isViewer() && $this->access->allowed('friends.list') && ES::lists()->canCreateList()) { ?>
			<?php echo $this->html('mobile.filterActions', array($this->html('mobile.filterAction', 'COM_EASYSOCIAL_FRIENDS_NEW_LIST', ESR::friends(array('layout' => 'listForm'))))); ?>
		<?php } ?>
	</div>

	<div class="es-mobile-filter__bd" data-es-group-filters>
		<div class="es-mobile-filter__group <?php echo !$activeList ? 'is-active' : '';?>" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">

						<?php echo $this->html('mobile.filterTab', $filters->all->label, $filters->all->link, (!$activeList && (!$filter || $filter == 'all' )),
												array('data-es-swiper-item', 'data-type="all"', 'title="' . $filters->all->page_title . '"')); ?>


						<?php if (!$user->isViewer()) { ?>
							<?php echo $this->html('mobile.filterTab', $filters->mutual->label, $filters->mutual->link, (!$activeList && $filter == 'mutual'),
													array('data-es-swiper-item', 'data-type="all"', 'title="' . $filters->mutual->page_title . '"')); ?>
						<?php } ?>

						<?php if ($user->isViewer()) { ?>
							<?php echo $this->html('mobile.filterTab', $filters->suggestion->label, $filters->suggestion->link, (!$activeList && $filter == 'suggest'),
													array('data-es-swiper-item', 'data-type="all"', 'title="' . $filters->suggestion->page_title . '"')); ?>

							<?php echo $this->html('mobile.filterTab', $filters->pending->label, $filters->pending->link, (!$activeList && $filter == 'pending'),
													array('data-es-swiper-item', 'data-type="all"', 'title="' . $filters->pending->page_title . '"')); ?>

							<?php echo $this->html('mobile.filterTab', $filters->sent->label, $filters->sent->link, (!$activeList && $filter == 'request'),
													array('data-es-swiper-item', 'data-type="all"', 'title="' . $filters->sent->page_title . '"')); ?>


							<?php if ($this->config->get('friends.invites.enabled')) { ?>
								<?php echo $this->html('mobile.filterTab', $filters->invites->label, $filters->invites->link, (!$activeList && $filter == 'invites'),
														array('data-es-swiper-item', 'data-type="all"', 'title="' . $filters->invites->page_title . '"')); ?>
							<?php }?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<div class="es-mobile-filter__group <?php echo $activeList ? 'is-active' : '';?>" data-es-swiper-group data-type="lists">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php if ($lists) { ?>
							<?php foreach ($lists as $list) { ?>
								<?php echo $this->html('mobile.filterTab', $list->get('title'), ESR::friends(array('listId' => $list->id)), ($activeList && $activeList->id == $list->id),
											array('data-es-swiper-item', 'data-type="list"', 'data-id="' . $list->id . '"', 'title="' . $filters->all->page_title . '"')
									); ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
