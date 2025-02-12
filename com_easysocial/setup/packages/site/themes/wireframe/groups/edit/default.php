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
<?php if (!$group->isPending()) { ?>
	<?php echo $this->html('cover.group', $group, 'info'); ?>
<?php } ?>

<div class="es-container" data-groups-edit data-es-container>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/groups/edit/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">

		<?php echo $this->render('module' , 'es-groups-edit-before-contents'); ?>

		<div data-group-edit-fields>
			<form method="post" action="<?php echo JRoute::_('index.php'); ?>" class="es-forms" data-group-fields-form>

				<div class="tab-content">
					<div class="tab-content__item" data-group-edit-fields-content data-id="history">
						<div class="es-forms__group">
							<div class="es-forms__content">
								<?php if ($group->isDraft()) { ?>
									<?php echo $this->html('cluster.approvalHistory', $rejectedReasons); ?>
								<?php } ?>
							</div>
						</div>
					</div>

					<?php $i = 0; ?>
					<?php foreach ($steps as $step) { ?>
					<div class="tab-content__item step-<?php echo $step->id;?> <?php echo ($i == 0 && !$activeStep) || ($activeStep && $activeStep == $step->id) ? ' active' :'';?>" data-group-edit-fields-content data-id="<?php echo $step->id; ?>">
						<div class="es-forms__group">
							<div class="es-forms__content">
								<div class="o-form-horizontal">
									<?php if ($step->fields) { ?>
										<?php foreach ($step->fields as $field) { ?>
											<?php echo $this->loadTemplate('site/registration/steps/field', array('field' => $field, 'errors' => '')); ?>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
					<?php $i++; ?>
					<?php } ?>
				</div>

				<div class="es-forms__actions">
					<div class="o-form-actions">
						<?php if (!$group->isPending()) { ?>
							<a href="<?php echo $group->getPermalink();?>" class="btn btn-es-default-o t-lg-pull-left"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
							<button type="button" class="btn btn-es-primary t-lg-pull-right" data-task="update" data-group-fields-submit><?php echo JText::_('COM_ES_UPDATE');?></button>
						<?php } else { ?>
							<a href="<?php echo ESR::manage(array('layout' => 'clusters'));?>" class="btn btn-es-default-o t-lg-pull-left"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></a>

							<button type="button" class="btn btn-es-danger-o t-lg-pull-right" data-task="reject" data-group-fields-submit><?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON');?></button>
							<button type="button" class="btn btn-es-primary t-lg-pull-right" data-task="approve" data-group-fields-submit><?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON');?></button>
						<?php } ?>
					</div>
				</div>

				<input type="hidden" name="conditionalRequired" value="<?php echo ES::string()->escape($conditionalFields); ?>" data-conditional-check />
				<input type="hidden" name="Itemid" value="<?php echo $this->input->get('Itemid', 0, 'int');?>" />
				<input type="hidden" name="option" value="com_easysocial" />
				<input type="hidden" name="controller" value="groups" />
				<input type="hidden" name="task" value="update" data-task-hidden-input/>
				<input type="hidden" name="id" value="<?php echo $group->id;?>" />
				<input type="hidden" name="<?php echo ES::token();?>" value="1" />
			</form>
		</div>

		<?php echo $this->render('module', 'es-groups-edit-after-contents'); ?>
	</div>
</div>
