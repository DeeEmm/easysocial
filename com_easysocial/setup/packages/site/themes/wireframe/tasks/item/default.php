<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container" data-tasks-item data-id="<?php echo $milestone->id; ?>" data-uid="<?php echo $cluster->id; ?>" data-task-cluster-type="<?php echo $cluster->cluster_type; ?>">
	<div class="es-content <?php echo $milestone->isDue() ? 'is-due' : ''; ?> <?php echo $milestone->isCompleted() ? 'is-completed' : ''; ?>" data-tasks-wrapper>
		<div class="es-entry-actionbar es-island">
			<div class="o-grid-sm">
				<div class="o-grid-sm__cell">
					<a href="<?php echo $cluster->getAppPermalink('tasks'); ?>" class="btn btn-es-default-o btn-sm">&larr; <?php echo JText::_('COM_EASYSOCIAL_OTHER_MILESTONES'); ?></a>
				</div>

				<?php if ($cluster->isAdmin()) { ?>
				<div class="o-grid-sm__cell">
					<div class="o-btn-group pull-right">
						<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-sm" data-es-toggle="dropdown">
							<i class="fa fa-ellipsis-h"></i>
						</button>

						<ul class="dropdown-menu dropdown-menu-right">
							<li>
								<a href="<?php echo ESR::apps(array('layout' => 'canvas', 'customView' => 'form', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'milestoneId' => $milestone->id), false); ?>"><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_EDIT'); ?></a>
							</li>

							<li class="mark-uncomplete">
								<a href="javascript:void(0);" data-milestone-mark-incomplete><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_MARK_INCOMPLETE'); ?></a>
							</li>
							<li class="mark-completed">
								<a href="javascript:void(0);" data-milestone-mark-complete><?php echo JText::_('APP_EVENT_TASKS_MILESTONE_MARK_COMPLETE'); ?></a>
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
		</div>

		<div class="es-apps-entry-section es-island">
			<div class="es-apps-entry">
				<div class="es-apps-entry__hd">
					<div class="es-apps-entry__title"><?php echo $milestone->title; ?></div>
				</div>

				<div class="es-apps-entry__ft es-bleed--middle">
					<div class="o-grid">
						<div class="o-grid__cell">
							<div class="es-apps-entry__meta">
								<div class="es-apps-entry__meta-item">
									<ol class="g-list-inline g-list-inline--dashed">
										<li>
											<i class="fa fa-user"></i>&nbsp; <?php echo JText::sprintf('APP_EVENT_TASKS_META_CREATED_BY', $this->html('html.user', $milestone->owner_id)); ?>
										</li>
										<li>
											<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf('APP_EVENT_TASKS_META_CREATED_ON', ES::date($milestone->created)->format(JText::_('DATE_FORMAT_LC3'))); ?>
										</li>

										<?php if ($milestone->hasDueDate()) { ?>
										<li>
											<i class="fa fa-calendar"></i> <?php echo JText::sprintf('APP_EVENT_TASKS_META_DUE_ON', ES::date($milestone->due)->format(JText::_('DATE_FORMAT_LC3'))); ?>
										</li>
										<?php } ?>
									</ol>
								</div>
							</div>
						</div>
						<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
							<div class="es-apps-entry__state">
								<span class="o-label o-label--danger-o due"><?php echo JText::_('APP_EVENT_TASKS_OVERDUE'); ?></span>
								<span class="o-label o-label--success-o completed"><?php echo JText::_('APP_EVENT_TASKS_COMPLETED'); ?></span>
							</div>
						</div>
					</div>
				</div>

				<div class="es-apps-entry__bd">
					<div class="es-apps-entry__desc">
						<?php echo $milestone->getContent(); ?>
					</div>

					<div class="es-apps-tasks-stats">
						<div class="es-apps-tasks-stats__title"><?php echo JText::_('COM_ES_STATISTICS');?></div>
						<div class="es-apps-tasks-stats__chart">
							<div class="progress es-apps-task-progress">
								<div class="progress-bar progress-bar-success" style="width:<?php echo $percentage;?>%;"></div>
							</div>
						</div>
						<div class="es-apps-tasks-stats__desc">
							<?php echo JText::sprintf('COM_ES_TASKS_STATS', $totalClosed, $total); ?>
						</div>
					</div>

					<?php if ($cluster->canCreateTasks()) { ?>
					<div class="es-apps-task-form t-lg-mt--xl" data-tasks-form-wrapper>
						<form data-form>
							<div class="o-alert o-alert--error t-hidden" data-form-error><?php echo JText::_('APP_EVENT_TASKS_EMPTY_TITLE_ERROR'); ?></div>

							<div class="o-form-group">
								<input class="o-form-control" placeholder="<?php echo JText::_('APP_EVENT_TASKS_PLACEHOLDER_TASK_TITLE', true); ?>" data-form-input />
							</div>

							<div class="o-form-group">
								<div class="o-grid o-grid--gutters">
									<div class="o-grid__cell ">
										<div class="o-control-input">
											<div class="textboxlist controls DS07326479724847607" data-members-suggest="">
												<input autocomplete="off" class="participants textboxlist-textField o-form-control" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_TASKS_ENTER_A_NAME' );?>" data-textboxlist-textfield type="text" />
											</div>
										</div>
									</div>
									<div class="o-grid__cell">
										<?php echo $this->html('form.calendar', 'due', '', 'due', array('placeholder="' . JText::_('APP_EVENT_TASKS_DUE_DATE_PLACEHOLDER', true) . '"', 'data-form-due'), false, 'DD-MM-YYYY', false, true, true); ?>
									</div>
								</div>
							</div>
							<div class="es-apps-task-form__action">
								<button class="btn btn-es-default-o t-pull-right" type="button" data-create><?php echo JText::_('APP_EVENT_TASKS_CREATE'); ?></button>
							</div>
						</form>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<div class="es-apps-entry-section">
			<div class="es-apps-entry-section__content">
				<ul class="o-tabs o-tabs--horizontal-flat t-lg-mt--md t-lg-mb--md">
					<li class="o-tabs__item active"
							data-view-open-tasks
							 >
						<a href="#open" class="o-tabs__link" data-es-toggle="tab"><?php echo JText::sprintf('APP_EVENT_TASKS_TAB_OPEN_TASKS', '<span data-tasks-open-counter>' . $totalOpen . '</span>'); ?></a>
					</li>
					<li class="o-tabs__item">
						<a href="#closed" class="o-tabs__link" data-es-toggle="tab"><?php echo JText::sprintf('APP_EVENT_TASKS_TAB_COMPLETED_TASKS', '<span data-tasks-closed-counter>' . $totalClosed . '</span>'); ?></a>
					</li>
				</ul>
			</div>
		</div>

		<div class="es-apps-entry-section">
			<div class="es-apps-entry-section__content es-island">
				<div class="tab-content" >
					<div class="tab-pane active" id="open" >
						<div class="milestone-tasks">
							<div class="tasks-list" data-tasks-list data-task-select-open>
								<?php if ($openTasks) { ?>
									<?php foreach ($openTasks as $task) { ?>
										<?php echo $this->loadTemplate('site/tasks/item/task', array('task' => $task, 'assignee' => ES::user($task->user_id), 'cluster' => $cluster)); ?>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="tab-pane" id="closed">
						<div class="milestone-tasks">
							<div class="tasks-list" data-tasks-completed>
								<?php if ($closedTasks) { ?>
									<?php foreach ($closedTasks as $task) { ?>
										<?php echo $this->loadTemplate('site/tasks/item/task', array('task' => $task, 'assignee' => ES::user($task->user_id), 'cluster' => $cluster)); ?>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
