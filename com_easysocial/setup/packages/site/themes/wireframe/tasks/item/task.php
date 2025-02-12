<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-apps-item es-apps-item--tasks <?php echo $task->state == 2 ? 'is-resolved' : '';?>" data-item data-id="<?php echo $task->id; ?>">
	<div class="es-apps-item__hd">
		<div class="o-checkbox es-apps-item__checkbox">
			<input type="checkbox" id="task-<?php echo $task->id;?>" data-item-checkbox <?php echo $task->state == 2 ? ' checked="checked"' : ''; ?> <?php echo !$cluster->canCompleteTask($assignee->id) ? ' disabled="disabled"' : ''; ?> />
			<label for="task-<?php echo $task->id;?>"><?php echo $task->title; ?></label>
		</div>

		<div class="es-apps-item__action">
			<?php if ($cluster->isAdmin() || $assignee->id == $this->my->id) { ?>
			<div class="o-btn-group">
				<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-xs" data-es-toggle="dropdown">
					<i class="fa fa-caret-down"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="javascript:void(0);" data-remove><?php echo JText::_('APP_EVENT_TASKS_DELETE_TASK'); ?></a>
					</li>
					<?php if ($cluster->canEditTasks()) { ?>
					<li>
						<a href="javascript:void(0);" data-task-edit data-task-id="<?php echo $task->id; ?>"><?php echo JText::_('COM_ES_APP_TASKS_ITEM_EDIT_BUTTON'); ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		</div>
	</div>

	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<i class="fa fa-user"></i>&nbsp;
								<?php if ($assignee->id) { ?>
									 <?php echo $this->html('html.user', $assignee->id);?>
								<?php } else { ?>
									<?php echo JText::_('COM_EASYSOCIAL_APP_TASKS_NOT_ASSIGNED_YET'); ?>
								<?php } ?>
							</li>

							<?php if ($task->hasDueDate() && $task->due && $task->state != 2) { ?>
							<li>
								<i class="fa fa-calendar"></i>&nbsp; <?php echo JText::sprintf('APP_EVENT_TASKS_DUE_ON', ES::date($task->due)->format(JText::_('DATE_FORMAT_LC1'))); ?>
							</li>
							<?php } ?>

							<li class="t-text--muted">
								<i class="far fa-clock"></i>&nbsp; <?php echo ES::date($task->created)->toLapsed(); ?>
							</li>
						</ol>
					</div>
				</div>
			</div>
			<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
				<div class="es-apps-item__state">
					<span class="o-label o-label--success-o">Resolved</span>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="es-apps-task-form t-lg-mt--xl t-hidden" data-task-item-edit-form data-edit-task-id="<?php echo $task->id; ?>" data-tasks-form-wrapper></div>
