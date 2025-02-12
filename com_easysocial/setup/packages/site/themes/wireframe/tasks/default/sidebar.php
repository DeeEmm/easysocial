<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-sidebar <?php echo $moduleLib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>

		<?php if ($user && $user->isViewer()) { ?>
		<a class="btn btn-es-primary btn-block t-lg-mb--xl" href="javascript:void(0);" data-create>
			<?php echo JText::_('APP_USER_TASKS_NEW_TASK_BUTTON'); ?>
		</a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_ES_STATISTICS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-nav o-nav--stacked">
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-tasks t-lg-mr--md"></i>
							<b><?php echo $counters['total'];?></b> <?php echo JText::_('COM_ES_TASKS'); ?>
						</span>
					</li>

					<?php if ($groupTaskAppEnabled) { ?>
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-users t-lg-mr--md"></i>
							<b><?php echo $counters['group'];?></b> <?php echo JText::_('COM_ES_GROUP_TASKS'); ?>
						</span>
					</li>
					<?php } ?>

					<?php if ($eventTaskAppEnabled) { ?>
					<li class="o-nav__item t-lg-mb--sm">
						<span class="o-nav__link t-text--muted">
							<i class="es-side-widget__icon fa fa-calendar t-lg-mr--md"></i>
							<b><?php echo $counters['event'];?></b> <?php echo JText::_('COM_ES_EVENT_TASKS'); ?>
						</span>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<div class="es-side-widget">
			<?php echo $this->html('widget.title', 'COM_ES_FILTERS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice active" data-tasks-filter="all">
						<a href="javascript:void(0);" class="o-tabs__link"><?php echo JText::_('APP_USER_TASKS_FILTER_ALL');?></a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters['total'];?></div>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item has-notice" data-tasks-filter="is-resolved">
						<a href="javascript:void(0);" class="o-tabs__link"><?php echo JText::_('APP_USER_TASKS_FILTER_RESOLVED');?></a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters['resolved'];?></div>
					</li>

					<li class="o-tabs__item has-notice" data-tasks-filter="is-unresolved">
						<a href="javascript:void(0);" class="o-tabs__link"><?php echo JText::_('APP_USER_TASKS_FILTER_UNRESOLVED');?></a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters['unresolved'];?></div>
					</li>

					<?php if (!$hidePersonal) { ?>
					<li class="o-tabs__item has-notice" data-tasks-filter="task-user">
						<a href="javascript:void(0);" class="o-tabs__link"><?php echo JText::_('APP_USER_TASKS_FILTER_USER');?></a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters['user'];?></div>
					</li>
					<?php } ?>

					<?php if ($groupTaskAppEnabled) { ?>
					<li class="o-tabs__item has-notice" data-tasks-filter="task-group">
						<a href="javascript:void(0);" class="o-tabs__link"><?php echo JText::_('APP_USER_TASKS_FILTER_GROUPS');?></a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters['group'];?></div>
					</li>
					<?php } ?>

					<?php if ($eventTaskAppEnabled) { ?>
					<li class="o-tabs__item has-notice" data-tasks-filter="task-event">
						<a href="javascript:void(0);" class="o-tabs__link"><?php echo JText::_('APP_USER_TASKS_FILTER_EVENTS');?></a>
						<div class="o-tabs__bubble" data-counter><?php echo $counters['event'];?></div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

	</div>
</div>

<script>

EasySocial.require()
.script('site/apps/tasks/filter')
.done(function($) {
	$('body').implement(EasySocial.Controller.Apps.Tasks);
});

</script>
