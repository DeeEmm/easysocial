<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-apps-item es-island <?php echo $milestone->isDue() ? 'is-due' : ''; ?> <?php echo $milestone->isCompleted() ? ' is-completed ' : ''; ?>" data-tasks-milestone-item data-id="<?php echo $milestone->id; ?>">
	<div class="es-apps-item__hd">
		<a href="<?php echo ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false); ?>" class="es-apps-item__title"><?php echo $milestone->get('title'); ?></a>

		<?php if ($cluster->isAdmin()) { ?>
		<div class="es-apps-item__action">
			<div class="o-btn-group">
				<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-xs" data-es-toggle="dropdown">
					<i class="fa fa-caret-down"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="<?php echo ESR::apps(array('layout' => 'canvas', 'customView' => 'form', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false); ?>"><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_EDIT'); ?></a>
					</li>
					<li class="divider"></li>

					<li class="mark-uncomplete">
						<a href="javascript:void(0);" data-milestone-task="unresolve"><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_MARK_INCOMPLETE'); ?></a>
					</li>
					<li class="mark-completed">
						<a href="javascript:void(0);" data-milestone-task="resolve"><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_MARK_COMPLETE'); ?></a>
					</li>

					<li class="divider"></li>
					<li>
						<a href="javascript:void(0);" data-milestone-delete><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_DELETE'); ?></a>
					</li>
				</ul>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="es-apps-item__bd">
		<div class="es-apps-item__desc">
			<?php echo $this->html('string.truncate', $milestone->getContent(), 300); ?>
		</div>
	</div>
		<div class="es-apps-item__ft es-bleed--bottom">
			<div class="o-grid">
				<div class="o-grid__cell">
					<div class="es-apps-item__meta">
						<div class="es-apps-item__meta-item">
							<ol class="g-list-inline g-list-inline--dashed">
								<li>
									<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.user', $milestone->getOwner()); ?>
								</li>

								<?php if (!$milestone->hasDueDate()) { ?>
								<li>
									<i class="fa fa-calendar"></i>&nbsp; <?php echo $milestone->getCreatedDate()->format(JText::_('DATE_FORMAT_LC1')); ?>
								</li>
								<?php } ?>

								<li>
									<i class="fa fa-tasks"></i>&nbsp; <?php echo JText::sprintf(ES::string()->computeNoun('APP_EVENT_TASKS_TOTAL_TASKS', $milestone->getTotalTasks()), $milestone->getTotalTasks()); ?>
								</li>

								<?php if ($milestone->hasDueDate()) { ?>
								<li>
									<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf('APP_EVENT_TASKS_META_DUE_ON', ES::date($milestone->due)->format(JText::_('DATE_FORMAT_LC1'))); ?></i>
								</li>
								<?php } ?>

								<?php if ($milestone->hasAssignee()) { ?>
								<li>
									<i class="fa fa-user"></i>&nbsp; <?php echo JText::sprintf('APP_EVENT_TASKS_MILESTONE_IS_RESPONSIBLE', $this->html('html.user', $milestone->getAssignee()->id, true)); ?></a>
								</li>
								<?php } ?>
							</ol>
						</div>
					</div>
				</div>
				<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
					<div class="es-apps-item__state">
						<span class="o-label o-label--danger-o due"><?php echo JText::_('APP_EVENT_TASKS_OVERDUE'); ?></span>
						<span class="o-label o-label--success-o completed"><?php echo JText::_('APP_EVENT_TASKS_COMPLETED'); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
