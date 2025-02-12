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
<div class="es-wf-content <?php echo $pageNumber == 1 ? '' : 't-hidden'; ?>" data-content data-id="<?php echo $step->id; ?>">
	<div class="es-wf-content__hd">
		<div class="">
			<div class="es-wf-content-step-title" data-step-title>
				<?php echo JText::_($step->title); ?>
			</div>
			<div class="es-wf-content-step-desc" data-step-description><?php echo JText::_($step->description); ?></div>
		</div>
		<div class="es-wf-content__hd-action">
			<div class="es-wf-action">
				<a href="javascript:void(0);" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_EDIT'); ?>" data-step-edit>
					<i class="far fa-edit"></i>
				</a>

				<a href="javascript:void(0);" data-step-delete>
					<i class="fa fa-times"></i>
				</a>
			</div>
		</div>
	</div>

	<div class="es-wf-content__bd<?php echo $step->fields ? '' : ' is-empty'; ?>" data-fields-wrapper>
		<?php echo $this->includeTemplate('admin/workflows/form/steps/placeholder', ['action' => 'prepend']); ?>

		<div data-fields>
			<?php foreach ($step->fields as $field) { ?>
				<?php echo $this->includeTemplate('admin/workflows/form/steps/item', array('field' => $field)); ?>
			<?php } ?>
		</div>

		<div class="es-wf-content__empty">
			<div class="o-empty">
				<div class="o-empty__content">
					<div class="o-empty__text"><?php echo JText::_('COM_ES_WORKFLOW_EMPTY'); ?></div>
					<div class="o-empty__text t-text--muted"><?php echo JText::_('COM_ES_WORKFLOW_EMPTY_DESC'); ?></div>
				</div>
			</div>
		</div>

		<?php echo $this->includeTemplate('admin/workflows/form/steps/placeholder', ['action' => 'append']); ?>

	</div>
</div>
