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
<div class="es-stream-apps type-tasks">
	<div class="es-stream-apps__hd">
		<div class="es-stream-apps__title"><?php echo JText::_('APP_GROUP_TASKS_STREAM_CONTENT_TASKS');?></div>
	</div>

	<div class="es-stream-apps__bd es-stream-apps--border">
		<div class="es-stream-apps__desc">
			<?php foreach ($tasks as $task) { ?>
			<div class="<?php echo $task->isCompleted() ? 'completed' : '';?>">
				<div class="o-checkbox">
					<input type="checkbox" id="task-<?php echo $task->id;?>" value="<?php echo $task->id;?>" data-task-checkbox <?php echo $task->isCompleted() ? ' checked="checked"' : '';?><?php echo !$cluster->canCompleteTask($task->user_id) ? ' disabled="disabled"' : ''; ?> data-id="<?php echo $task->id;?>" />
					<label for="task-<?php echo $task->id;?>">
						<?php echo $task->title;?> 
					</label>
				</div>
			</div>
			<ul class="g-list-inline g-list-inline--dashed t-text--muted">
				<?php if ($task->user_id) { ?>
				<li>
					<i class="fa fa-user"></i>&nbsp; <?php echo $this->html('html.user', $task->user_id);?>
				</li>
				<?php } else { ?>
				<li>
					<?php echo JText::_('COM_EASYSOCIAL_APP_TASKS_NOT_ASSIGNED_YET'); ?>
				</li>
				<?php } ?>
				<?php if ($task->hasDueDate() && $task->due && $task->state != 2) { ?>
				<li>
					<?php echo JText::sprintf('APP_EVENT_TASKS_DUE_ON', ES::date($task->due)->format(JText::_('DATE_FORMAT_LC1'))); ?>
				</li>
				<?php } ?>
			</ul>            
			<?php } ?>
		</div>
	</div>
</div>
