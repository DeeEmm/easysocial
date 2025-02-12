<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form class="es-wf" method="post" id="adminForm" name="adminForm" action="index.php" data-workflows-form data-id="<?php echo $workflow->id; ?>">

	<div class="es-wf-bar t-lg-mt--lg" style="border-radius: 6px;border: 1px solid #f0f0f1;">
		<div class="es-wf-stepbar" data-steps>
			<?php if ($steps) { ?>
				<?php $stepNumber = 0; ?>
				<?php foreach ($steps as $step) { ?>
				<?php $stepNumber++; ?>
				<div class="es-wf-stepbar__item<?php echo $stepNumber == 1 ? ' is-active' : ''; ?>" data-step-item data-id="<?php echo $step->id; ?>" data-ordering="<?php echo $step->sequence; ?>" data-isnew="<?php echo $workflow->id ? 'false' : 'true'; ?>">
					<a href="javascript:void(0);" class="es-wf-stepbar__drag">
						<i class="fa fa-bars"></i>
					</a>
					<a href="javascript:void(0);" class="es-wf-stepbar__link" data-step-title><?php echo JText::_($step->title); ?></a>
				</div>
				<?php } ?>
			<?php } ?>

			<div class="es-wf-stepbar__item" data-step-new>
				<a href="javascript:void(0);" class="es-wf-stepbar__new-step" data-es-provide="tooltip" data-original-title="<?php echo JText::_('New Step');?>">
					<i class="fa fa-plus"></i>
				</a>
			</div>
		</div>

		<div class="es-wf-bar__action">
		</div>
	</div>

	<?php if ($steps) { ?>
		<?php $pageNumber = 0; ?>
		<?php foreach ($steps as $step) { ?>
			<?php $pageNumber++; ?>
			<?php echo $this->includeTemplate('admin/workflows/form/steps/default', array('step' => $step, 'pageNumber' => $pageNumber)); ?>
		<?php } ?>
	<?php } ?>

	<!-- This is where we are going to store all the generic templates -->
	<?php echo $this->includeTemplate('admin/workflows/form/templates/default'); ?>

	<?php echo $this->includeTemplate('admin/workflows/form/browser/default'); ?>

	<div data-field-settings>
	</div>

	<input type="hidden" name="fields" value="" data-fields-saved-value />

	<input type="hidden" name="id" value="<?php echo $workflow->id;?>" />
	<input type="hidden" name="type" value="<?php echo $workflow->type;?>" />
	<input type="hidden" name="title" value="<?php echo $workflow->getTitle(); ?>" data-input-workflow-title />
	<input type="hidden" name="description" value="<?php echo $workflow->getDescription(); ?>" data-input-workflow-description />

	<?php echo $this->html('form.hidden', 'customRedirect', $customRedirect); ?>
	<?php echo $this->html('form.action', 'workflows', 'save'); ?>
</form>
