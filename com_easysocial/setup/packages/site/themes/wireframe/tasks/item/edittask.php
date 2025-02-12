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
<?php if ($cluster->canEditTasks()) { ?>
	<form data-form>
		<div class="o-alert o-alert--error t-hidden" data-form-error><?php echo JText::_('APP_EVENT_TASKS_EMPTY_TITLE_ERROR'); ?></div>

		<div class="o-form-group">
			<input class="o-form-control" value="<?php echo ($task->title) ? $task->title : ''; ?>" placeholder="<?php echo JText::_('APP_EVENT_TASKS_PLACEHOLDER_TASK_TITLE', true); ?>"  data-edit-title-value />
		</div>

		<div class="o-form-group">
			<div class="o-grid o-grid--gutters">
				<div class="o-grid__cell ">
					<div class="o-control-input">
						<div class="textboxlist controls DS07326479724847607" data-members-suggest="">
							<?php if ($assignee) { ?>
								<div data-textboxlist-item="" class="textboxlist-item" data-id="<?php echo $assignee->id; ?>"><span data-textboxlist-itemcontent="" class="textboxlist-itemContent">
								<img width="16" height="16" data-suggest-avatar="" src="<?php echo $assignee->getAvatar(SOCIAL_AVATAR_MEDIUM); ?>"> <?php echo $assignee->getName(); ?>
								<input type="hidden" data-suggest-title="" value="<?php echo $assignee->getName(); ?>">
								<input type="hidden" data-suggest-id="" value="<?php echo $assignee->id; ?>" name=""></span><div data-textboxlist-itemremovebutton="" class="textboxlist-itemRemoveButton"><i class="fa fa-times"></i></div></div>

								<input autocomplete="off"  class="participants textboxlist-textField o-form-control"  data-textboxlist-textfield type="text" />
							<?php } else { ?>
								<input autocomplete="off"  class="participants textboxlist-textField o-form-control" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_TASKS_ENTER_A_NAME' );?>" data-textboxlist-textfield type="text" />
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="o-grid__cell">
					<?php echo $this->html('form.calendar', 'due', $due, 'due', array('placeholder="' . JText::_('APP_EVENT_TASKS_DUE_DATE_PLACEHOLDER', true) . '"', 'data-edit-due-value'), false, 'DD-MM-YYYY', false, true, true); ?>
				</div>
			</div>
		</div>
		<div class="es-apps-task-form__action">
			<button class="btn btn-es-primary-o t-pull-right" type="button" data-save-edit data-save-edit-task-id="<?php echo $task->id; ?>"><?php echo JText::_('COM_ES_APP_TASKS_ITEM_EDIT_SAVE_BUTTON'); ?></button>
			<button class="btn btn-es-default-o t-pull-right" type="button" data-cancel-edit data-cancel-task-id="<?php echo $task->id; ?>"><?php echo JText::_('COM_ES_APP_TASKS_ITEM_EDIT_CANCEL_BUTTON'); ?></button>
		</div>
	</form>
<?php } ?>
