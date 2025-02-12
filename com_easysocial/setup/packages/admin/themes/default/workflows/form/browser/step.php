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
<div id="es" class="es-dialog has-footer es-dialog--workflow type-html active no-tabs" data-field-dialog data-id="<?php echo $values->id; ?>">
	<div class="es-dialog-modal" style="width: 810px; height: 560px;">
		<div class="es-dialog-header">
			<div class="es-dialog-header__grid">
				<div class="es-dialog-header__cell">
					<div class="es-wf-dialog-title"><?php echo $title; ?></div>
				</div>
			</div>
		</div>
		<div class="es-dialog-body">
			<div class="es-dialog-container">
				<div class="es-dialog-content">
					<div class="es-wf-settings-form o-form-horizontal">
					<?php foreach ($params as $name => $field) { ?>
						<div class="o-form-group">
							<label class="o-control-label">
								<strong><?php echo $field->label;?></strong>

								<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo $field->tooltip; ?>"></i>
							</label>
							
							<div class="o-control-input">
								<?php echo $this->loadTemplate('admin/fields/config/' . $field->type, array('name' => $name, 'field' => $field, 'value' => $values->get($name))); ?>
							</div>
						</div>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<div class="es-dialog-footer">
			<div class="">
				<div class="es-dialog-footer-content">
					<button data-field-cancel-button="" type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
					<button data-field-step-save-button="" type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_DONE_BUTTON'); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>