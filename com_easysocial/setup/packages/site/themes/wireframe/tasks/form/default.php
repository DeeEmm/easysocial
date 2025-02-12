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
<div class="es-container">
	<div class="es-content" data-task-milestone data-id="<?php echo $milestone->id; ?>" data-uid="<?php echo $cluster->id; ?>">
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="es-forms">

			<div class="es-forms__group">
				<div class="es-forms__content">
					<div class="o-form-group">
						<input type="text" name="title" value="<?php echo $this->html('string.escape', $milestone->title); ?>" class="o-form-control"
							placeholder="<?php echo JText::_('APP_EVENT_TASKS_MILESTONE_TITLE_PLACEHOLDER', true); ?>" />
					</div>

					<div class="o-form-group">
						<div class="o-grid">
							<div class="o-grid__cell t-lg-mr--md t-xs-mb--md">
								<div class="o-control-input">
									<div class="textboxlist controls disabled" data-members-suggest>
										<?php if ($assignee) { ?>
											<div class="textboxlist-item" data-id="<?php echo $assignee->id; ?>" data-title="<?php echo $assignee->getName(); ?>" data-textboxlist-item>
												<span class="textboxlist-itemContent" data-textboxlist-itemContent>
													<img width="16" height="16" src="<?php echo $assignee->getAvatar(SOCIAL_AVATAR_SMALL);?>" />
													<?php echo $assignee->getName(); ?>
													<input type="hidden" name="user_id" value="<?php echo $assignee->id; ?>" />
												</span>
												<div class="textboxlist-itemRemoveButton" data-textboxlist-itemRemoveButton>
													<i class="fa fa-times"></i>
												</div>
											</div>
										<?php } ?>
										<input type="text" autocomplete="off" disabled class="participants textboxlist-textField o-form-control" data-textboxlist-textField
											placeholder="<?php echo ($assignee) ? ' ' : JText::_( 'APP_EVENT_TASKS_RESPONSIBILITY_OF' ); ?>" data-textboxlist-textField />
									</div>
								</div>
							</div>

							<div class="o-grid__cell">
								<?php echo $this->html('form.calendar', 'due', ES::date($milestone->due)->format('d-m-Y'), 'due', array('placeholder="' . JText::_('APP_EVENT_TASKS_DUE_DATE_FOR_MILESTONE', true) . '"'), false, 'DD-MM-YYYY', false, true, true); ?>
							</div>
						</div>
					</div>

					<div class="o-form-group">
						<div class="editor-wrap fd-cf">
							<?php echo ES::bbcode()->editor('description', $milestone->description, array('uid' => $cluster->id, 'type' => $cluster->getType())); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<a href="<?php echo $cluster->getAppPermalink('tasks'); ?>" class="t-lg-pull-left btn btn-es-default-o"><?php echo JText::_('COM_ES_CANCEL'); ?></a>

					<button type="submit" class="t-lg-pull-right btn btn-es-primary-o">
						<?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON'); ?>
					</button>
				</div>
			</div>

			<?php echo $this->html('form.action', 'tasks', 'saveMilestone'); ?>
			<input type="hidden" name="uid" value="<?php echo $cluster->id; ?>" />
			<input type="hidden" name="type" value="<?php echo $cluster->getType(); ?>" />
			<input type="hidden" name="id" value="<?php echo $milestone->id; ?>" />
		</form>
	</div>
</div>