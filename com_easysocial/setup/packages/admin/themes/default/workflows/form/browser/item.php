<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
$pageNumber = 0;
?>
<div id="es" class="es-dialog has-footer es-dialog--workflow type-html active" data-field-dialog data-id="<?php echo $fieldId; ?>" data-appid="<?php echo $app->id; ?>">
	<div class="es-dialog-modal" style="width: 810px; height: 560px;">

		<div class="es-dialog-header">
			<div class="es-dialog-header__grid">
				<div class="es-dialog-header__cell">
					<div class="es-wf-dialog-title">
						<?php echo $app->_('title');?>
					</div>
				</div>
				<?php if ($showConditional) { ?>
				<div class="es-dialog-header__cell">
					<div class="t-lg-pull-right">
						<div class="o-checkbox">
							<input type="checkbox" id="conditional" name="conditional" value="1" <?php echo $conditions ? 'checked="checked"' : ''; ?> data-field-is-conditional>
							<label for="conditional">
								<?php echo JText::_('This is a conditional custom field'); ?>
							</label>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>

			<div class="es-dialog-tabs es-dialog-tabs--space-evenly">
				<?php foreach ($tabs as $key => $tab) { ?>
					<?php if ($tab != 'conditional' && isset($params->$tab)) { ?>
						<?php $pageNumber++; ?>
						<?php if ($pageNumber != 1) { ?>
						<div class="es-dialog-tabs__item divider"></div>
						<?php } ?>
						<div class="es-dialog-tabs__item<?php echo $pageNumber == 1 ? ' active' : ''; ?>" data-field-tab data-id="<?php echo $pageNumber . '-' . $tab; ?>">
							<a href="javascript:void(0);" class="es-dialog-tabs__link">
								<?php echo $params->$tab->title; ?>
							</a>
						</div>
					<?php } ?>
				<?php } ?>
				<div class="es-dialog-tabs__item divider"></div>
				<div class="es-dialog-tabs__item<?php echo $conditions ? '' : ' t-hidden'; ?>" data-field-tab data-id="conditional-rule">
					<a href="javascript:void(0);" class="es-dialog-tabs__link">
						<?php echo JText::_('Conditional Rules'); ?>
					</a>
				</div>
			</div>
		</div>

		<?php $pageNumber = 0; ?>
		<div class="es-dialog-body">
			<div class="es-dialog-container">
			<?php foreach ($tabs as $key => $tab) { ?>
				<?php if ($tab != 'conditional' && isset($params->$tab)) { ?>
				<?php $pageNumber++; ?>
				<div class="es-dialog-content<?php echo $pageNumber == 1 ? '' : ' t-hidden'; ?>" data-field-content data-id="<?php echo $pageNumber . '-' . $tab; ?>">
					<div class="es-wf-settings-form o-form-horizontal">
					<?php foreach ($params->$tab->fields as $name => $field) { ?>
						<?php if (isset($field->subfields)) { ?>
							<?php foreach ($field->subfields as $subname => $subfield) { ?>
								<div class="o-form-group">
									<label class="o-control-label">
										<strong><?php echo isset($subfield->label) ? $subfield->label : $field->label . ': ' . $subname; ?></strong>
											
										<?php if (isset($subfield->tooltip)) { ?>
											<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo $subfield->tooltip; ?>"></i>
										<?php } ?>
									</label>

									<div class="o-control-input">
										<?php echo $this->loadTemplate('admin/fields/config/' . $field->type, array('name' => $name . '_' . $subname, 'field' => $subfield, 'value' => $values->get($name . '_' . $subname))); ?>
									</div>
								</div>
							<?php } ?>
						<?php } else { ?>
							<div class="o-form-group">
								<label class="o-control-label">
									<strong><?php echo $field->label; ?></strong>

									<?php if (isset($field->tooltip)) { ?>
									<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo $field->tooltip; ?>"></i>
									<?php } ?>
								</label>

								<div class="o-control-input">
									<?php echo $this->loadTemplate('admin/fields/config/' . $field->type, array('name' => $name, 'field' => $field, 'value' => $values->get($name))); ?>

									<?php if (isset($field->info)) { ?>
									<div class="alert t-lg-mt--sm"><?php echo $field->info; ?></div>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
					</div>
				</div>
				<?php } ?>
			<?php } ?>
				<div class="es-dialog-content t-hidden" data-field-content data-id="conditional-rule">
					<div class="o-alert o-alert--warning"><?php echo JText::sprintf('COM_ES_CONDITIONAL_FIELD_INFO', '<a href="https://stackideas.com/docs/easysocial/administrators/configuration/conditional-fields" target="_blank">', '</a>'); ?></div>
					<div class="es-wf-settings-form o-form-horizontal">
						<?php if ($params->conditional) { ?>
							<?php foreach ($params->conditional->fields as $name => $field) { ?>
								<?php if (isset($field->subfields)) { ?>
									<?php foreach ($field->subfields as $subname => $subfield) { ?>
										<div class="o-form-group">
											<label class="o-control-label">
												<strong><?php echo isset($subfield->label) ? $subfield->label : $field->label . ': ' . $subname; ?></strong>
													
												<?php if (isset($subfield->tooltip)) { ?>
													<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo $subfield->tooltip; ?>"></i>
												<?php } ?>
											</label>

											<div class="o-control-input">
												<?php echo $this->loadTemplate('admin/fields/config/' . $field->type, array('name' => $name . '_' . $subname, 'field' => $subfield, 'value' => $values->get($name . '_' . $subname))); ?>
											</div>
										</div>
									<?php } ?>
								<?php } else { ?>
									<div class="o-form-group">
										<label class="o-control-label">
											<strong><?php echo $field->label; ?></strong>

											<?php if (isset($field->tooltip)) { ?>
											<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo $field->tooltip; ?>"></i>
											<?php } ?>
										</label>

										<div class="o-control-input">
											<?php echo $this->loadTemplate('admin/fields/config/' . $field->type, array('name' => $name, 'field' => $field, 'value' => $values->get($name))); ?>

											<?php if (isset($field->info)) { ?>
											<div class="alert t-lg-mt--sm"><?php echo $field->info; ?></div>
											<?php } ?>
										</div>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>

						<div class="o-form-group">
							<label class="o-control-label">
								<strong><?php echo JText::_('COM_ES_CONDITIONAL_SELECT_RULE'); ?></strong>
								<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo JText::_('COM_ES_CONDITIONAL_SELECT_RULE_DESC'); ?>"></i>
							</label>
						</div>
					</div>

					<div class="es-wf-settings-form">
						<div class="o-form-group">
							<div class="o-control-input">
								<?php echo $this->loadTemplate('admin/fields/config/conditional', array('name' => 'conditions', 'conditions' => $conditions, 'availableFields' => $availableFields)); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="es-dialog-footer">
			<div class>
				<div class="es-dialog-footer-content">
					<button data-field-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
					<button data-field-save-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_DONE_BUTTON'); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>