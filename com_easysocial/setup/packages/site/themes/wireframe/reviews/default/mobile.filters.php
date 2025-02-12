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
<div class="es-mobile-info">
	<div class="es-side-widget">
		<?php echo $this->html('widget.title', 'COM_ES_STATISTICS'); ?>

		<div class="es-side-widget__bd">
			<ul class="o-nav o-nav--stacked">

				<li class="o-nav__item t-lg-mb--sm">
					<span class="o-nav__link t-text--muted">
						<i class="es-side-widget__icon fa fa-star t-lg-mr--md"></i>
						<?php echo JText::sprintf('APP_REVIEWS_RATINGS', $cluster->getAverageRatings()); ?>
					</span>
				</li>
				<li class="o-nav__item t-lg-mb--sm">
					<span class="o-nav__link t-text--muted">
						<i class="es-side-widget__icon fa fa-gift t-lg-mr--md"></i>
						<?php echo JText::sprintf('APP_REVIEWS_SUBMITTED_REVIEWS', $cluster->getTotalReviews()); ?>
					</span>
				</li>
			</ul>
		</div>
	</div>
</div>

<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left">
				<div class="es-mobile-filter-slider__content" >
					<?php echo $this->html('mobile.filterGroup', 'APP_USER_TASKS_FILTER_ALL', 'all', true, 'fas fa-star', false, array('data-review-filter="all"')); ?>

					<?php if (!$this->my->guest) { ?>
						<?php echo $this->html('mobile.filterGroup', $isAdmin ? 'COM_ES_PENDING' : 'COM_ES_UNDER_MODERATION_FILTER', 'pending', false, 'fas fa-edit', false, array('data-review-filter="pending"')); ?>
					<?php } ?>

				</div>
			</div>
		</div>

		<?php if ($cluster->canSubmitReview()) { ?>
			<?php echo $this->html('mobile.filterActions',
					array(
						$this->html('mobile.filterAction', 'APP_REVIEWS_SUBMIT', ESR::apps(array('layout' => 'canvas', 'customView' => 'form', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias())))
					)
			); ?>
		<?php } ?>

	</div>
</div>

