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

<?php if ($user !== false) { ?>
<div class="es-mobile-info">
	<div class="es-side-widget" data-type="info">
		<?php echo $this->html('widget.title', 'COM_ES_STATISTICS'); ?>
		<div class="es-side-widget__bd">
			<ul class="o-nav o-nav--stacked">
				<li class="o-nav__item t-lg-mb--sm">
					<span class="o-nav__link t-text--muted">
						<i class="es-side-widget__icon fa fa-chart-pie t-lg-mr--md"></i>
						<b><?php echo $total;?></b> <?php echo JText::_('COM_EASYSOCIAL_POLLS');?>
					</span>
				</li>
			</ul>
		</div>
	</div>
</div>
<?php } ?>

<div class="es-mobile-filter">
	<div class="es-mobile-filter__hd">

		<?php if (($user && $user->id == $this->my->id) || $user === false) { ?>
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $filter == 'all' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="all"
						>
							<a href="<?php echo $filterLinks->all; ?>" class="btn es-mobile-filter-slider__btn">
								<i class="fa fa-chart-bar"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_POLLS_ALL_POLLS');?>
							</a>
						</div>

						<?php if ($this->my->id) { ?>
						<div class="es-mobile-filter-slider__item swiper-slide <?php echo $filter == 'mine' ? 'is-active' : '';?>"
							data-es-swiper-item
							data-filter-item="mine"
						>
							<a href="<?php echo $filterLinks->mine; ?>" class="btn es-mobile-filter-slider__btn">
								<i class="fa fa-user"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_POLLS_MY_POLLS');?>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>


		<?php if ($showCreateButton) { ?>
			<?php echo $this->html('mobile.filterActions',
					array(
						$this->html('mobile.filterAction', 'COM_EASYSOCIAL_NEW_POLL', $createButtonLink)
					)
			); ?>
		<?php } ?>
	</div>
</div>
