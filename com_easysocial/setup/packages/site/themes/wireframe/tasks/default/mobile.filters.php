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
				<?php if ($user) { ?>

					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-tasks t-lg-mr--md"></i>
							<b><?php echo $counters['total'];?></b> <?php echo JText::_('COM_ES_TASKS'); ?>
						</span>
					</li>
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-users t-lg-mr--md"></i>
							<b><?php echo $counters['group'];?></b> <?php echo JText::_('COM_ES_GROUP_TASKS'); ?>
						</span>
					</li>
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-calendar t-lg-mr--md"></i>
							<b><?php echo $counters['event'];?></b> <?php echo JText::_('COM_ES_EVENT_TASKS'); ?>
						</span>
					</li>

				<?php } else { ?>

					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-tasks t-lg-mr--md"></i>
							<b><?php echo $counters['milestones'];?></b> <?php echo JText::_('COM_ES_MILESTONES'); ?>
						</span>
					</li>
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-users t-lg-mr--md"></i>
							<b><?php echo $counters['tasks'];?></b> <?php echo JText::_('COM_ES_TASKS'); ?>
						</span>
					</li>

				<?php } ?>
			</ul>
		</div>
	</div>
</div>

<?php if ($user) { ?>
<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<div class="es-mobile-filter__hd-cell is-slider">
			<div class="es-mobile-filter-slider is-end-left">
				<div class="es-mobile-filter-slider__content" >
					<?php echo $this->html('mobile.filterGroup', 'COM_ES_FILTERS', 'filters', true, 'fas fa-filter'); ?>

					<?php if ($user->isViewer()) { ?>
						<?php echo $this->html('mobile.filterGroup', 'APP_USER_TASKS_NEW_TASK_BUTTON', 'new-tasks', false, 'fa fa-tasks', false, array('data-create', 'data-disable-button="1"')); ?>
					<?php } ?>

				</div>
			</div>
		</div>
	</div>

	<div class="es-mobile-filter__bd" data-es-events-filters>
		<div class="es-mobile-filter__group is-active" data-es-swiper-group data-type="filters">
			<div class="es-mobile-filter-slider is-end-left" data-es-swiper-slider>
				<div class="es-mobile-filter-slider__content swiper-container" data-es-swiper-container>
					<div class="swiper-wrapper">
						<?php echo $this->html('mobile.filterTab', 'APP_USER_TASKS_FILTER_ALL', 'javascript:void(0)', true, array('data-tasks-filter="all"')); ?>

						<?php echo $this->html('mobile.filterTab', 'APP_USER_TASKS_FILTER_RESOLVED', 'javascript:void(0)', false, array('data-tasks-filter="is-resolved"')); ?>

						<?php echo $this->html('mobile.filterTab', 'APP_USER_TASKS_FILTER_UNRESOLVED', 'javascript:void(0)', false, array('data-tasks-filter="is-unresolved"')); ?>

						<?php if (!$hidePersonal) { ?>
						<?php echo $this->html('mobile.filterTab', 'APP_USER_TASKS_FILTER_USER', 'javascript:void(0)', false, array('data-tasks-filter="task-user"')); ?>
						<?php } ?>

						<?php echo $this->html('mobile.filterTab', 'APP_USER_TASKS_FILTER_GROUPS', 'javascript:void(0)', false, array('data-tasks-filter="task-group"')); ?>

						<?php echo $this->html('mobile.filterTab', 'APP_USER_TASKS_FILTER_EVENTS', 'javascript:void(0)', false, array('data-tasks-filter="task-event"')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
<?php } ?>

<?php if (!$user && $cluster->canCreateMilestones()) { ?>
<div class="es-mobile-filter" data-es-mobile-filters>
	<div class="es-mobile-filter__hd">
		<?php echo $this->html('mobile.filterActions',
				array(
					$this->html('mobile.filterAction', 'APP_EVENT_TASKS_NEW_MILESTONE', ESR::apps(array('layout' => 'canvas', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'customView' => 'form')))
				)
		); ?>
	</div>
</div>
<?php } ?>
