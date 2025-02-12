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
<div class="es-apps-item es-apps-item--tasks es-island <?php echo $task->state == 2 ? ' is-resolved' : ' is-unresolved';?> task-<?php echo isset($task->cluster) && $task->cluster ? $task->cluster->getType() : 'user';?>"
	data-item
	data-id="<?php echo $task->id;?>">

	<div class="es-apps-item__hd">
		<div class="o-checkbox es-apps-item__checkbox">
			<input type="checkbox" id="task-<?php echo $task->id;?>" data-item-checkbox <?php echo $task->state == 2 ? 'checked="checked" ' : '';?> <?php echo !$user->isViewer() ? 'disabled="disabled"' : '';?>/>
			<label for="task-<?php echo $task->id;?>"><?php echo $task->get('title'); ?></label>
		</div>
		<?php if ($user->isViewer()) { ?>
		<div class="es-apps-item__action">
			<div class="o-btn-group">
				<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-xs" data-es-toggle="dropdown">
					<i class="fa fa-caret-down"></i>
				</button>

				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="javascript:void(0);" data-item-delete><?php echo JText::_('APP_USER_TASKS_DELETE_TASK');?></a>
					</li>
				</ul>
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="es-apps-item__ft es-bleed--bottom">
		<div class="o-grid">
			<div class="o-grid__cell">
				<div class="es-apps-item__meta">
					<div class="es-apps-item__meta-item">
						<ol class="g-list-inline g-list-inline--dashed">
							<li>
								<i class="far fa-clock"></i>&nbsp; <?php echo ES::date($task->created)->toLapsed(); ?>
							</li>

							<?php if (isset($task->cluster) && $task->cluster) { ?>
								<?php if ($task->hasDueDate() && $task->due && $task->state != 2) { ?>
								<li>
									<?php echo JText::sprintf('APP_EVENT_TASKS_DUE_ON', ES::date($task->due)->format(JText::_('DATE_FORMAT_LC1'))); ?>
								</li>
								<?php } ?>

								<li>
									<i class="fa fa-<?php echo $task->cluster->getType() == 'event' ? 'calendar' : 'group';?>"></i>&nbsp; <?php echo $this->html('html.cluster', $task->cluster); ?>
								</li>
							<?php } ?>
						</ol>
					</div>
				</div>
			</div>
			<div class="o-grid__cell o-grid__cell--auto-size o-grid__cell--right">
				<div class="es-apps-item__state">
					<span class="o-label o-label--success-o"><?php echo JText::_('APP_USER_TASKS_RESOLVED'); ?></span>
				</div>
			</div>
		</div>
	</div>
</div>
