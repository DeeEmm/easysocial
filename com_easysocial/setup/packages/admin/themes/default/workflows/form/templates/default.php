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
<div class="t-hidden" data-workflow-templates>
	<div data-template="dialog-loader">
		<div id="es" class="es-dialog has-footer es-dialog--workflow type-html active">
			<div class="es-dialog-modal" style="width: 810px; height: 560px;">
				<div class="es-dialog-header">
				</div>
				<div class="es-dialog-body">
					<div class="es-dialog-container is-loading">
						<div class="o-loader"></div>
					</div>
				</div>
				<div class="es-dialog-footer">
				</div>
			</div>
		</div>
	</div>

	<div data-template="dialog-delete-step">
		<div id="es" class="es-dialog has-footer type-html active no-tabs" data-dialog>
			<div class="es-dialog-modal" style="width: 400px; height: 230px;">
				<div class="es-dialog-header">
					<div class="es-dialog-header__grid">
						<div class="es-dialog-header__cell">
							<div class="es-wf-dialog-title"><?php echo JText::_('COM_ES_DIALOG_DELETE_STEP_TITLE'); ?></div>
						</div>
						<div class="es-dialog-close-button" data-field-cancel-button><i class="fa fa-close"></i></div>
					</div>
				</div>
				<div class="es-dialog-body">
					<div class="es-dialog-container">
						<div class="es-dialog-content"><?php echo JText::_('COM_ES_DIALOG_DELETE_STEP_CONTENT'); ?></div>
					</div>
				</div>
				<div class="es-dialog-footer">
					<div class="">
						<div class="es-dialog-footer-content">
							<button data-field-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
							<button data-dialog-confirm type="button" class="btn btn-es-danger btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_DELETE_BUTTON'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div data-template="dialog-delete-field">
		<div id="es" class="es-dialog has-footer type-html active no-tabs" data-dialog>
			<div class="es-dialog-modal" style="width: 400px; height: 230px;">
				<div class="es-dialog-header">
					<div class="es-dialog-header__grid">
						<div class="es-dialog-header__cell">
							<div class="es-wf-dialog-title"><?php echo JText::_('Delete Selected Fields'); ?></div>
						</div>
						<div class="es-dialog-close-button" data-field-cancel-button><i class="fa fa-close"></i></div>
					</div>
				</div>
				<div class="es-dialog-body">
					<div class="es-dialog-container">
						<div class="es-dialog-content"><?php echo JText::_('Are you sure you want to remove the selected custom fields from this workflow?'); ?></div>
					</div>
				</div>
				<div class="es-dialog-footer">
					<div class="">
						<div class="es-dialog-footer-content">
							<button data-field-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
							<button data-dialog-deletefield type="button" class="btn btn-es-danger btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_DELETE_BUTTON'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div data-template="dialog-move-field">
		<div id="es" class="es-dialog has-footer type-html active no-tabs" data-dialog-move-field>
			<div class="es-dialog-modal" style="width: 400px; height: 260px;">
				<div class="es-dialog-header">
					<div class="es-dialog-header__grid">
						<div class="es-dialog-header__cell">
							<div class="es-wf-dialog-title"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_TITLE'); ?></div>
						</div>
						<div class="es-dialog-close-button" data-field-cancel-button><i class="fa fa-close"></i></div>
					</div>
				</div>
				<div class="es-dialog-body">
					<div class="es-dialog-container" data-move-field-content>
						<div class="es-dialog-content t-hidden" data-field-available>
							<p><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_SELECT_PAGE'); ?></p>
							<div>
								<select class="o-form-control" data-move-selection>
								</select>
							</div>
						</div>
						<div class="es-dialog-content t-hidden" data-field-unavailable>
							<p><?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_MOVE_FIELD_DIALOG_NO_PAGE'); ?></p>
						</div>
					</div>
				</div>
				<div class="es-dialog-footer">
					<div class="">
						<div class="es-dialog-footer-content">
							<button data-field-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
							<button data-dialog-move type="button" class="btn btn-es-primary btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_MOVE_FIELD_BUTTON'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div data-template="dialog-error">
		<div id="es" class="es-dialog has-footer type-html active no-tabs" data-dialog-error>
			<div class="es-dialog-modal" style="width: 400px; height: 230px;">
				<div class="es-dialog-header">
					<div class="es-dialog-header__grid">
						<div class="es-dialog-header__cell">
							<div class="es-wf-dialog-title"><?php echo JText::_('COM_EASYSOCIAL_FIELDS_SAVE_ERROR'); ?></div>
						</div>
						<div class="es-dialog-close-button" data-dialog-error-close><i class="fa fa-close"></i></div>
					</div>
				</div>
				<div class="es-dialog-body">
					<div class="es-dialog-container">
						<div class="es-dialog-content">
							<p class="t-hidden" data-error-type="default"><?php echo JText::_('COM_ES_ERROR_SAVING_WORKFLOW'); ?></p>
							<p class="t-hidden" data-error-type="mandatory"><?php echo JText::_('COM_EASYSOCIAL_FIELDS_REQUIRE_MANDATORY_FIELDS'); ?></p>
							<p class="t-hidden" data-error-type="empty-step"><?php echo JText::_('COM_ES_FIELDS_EMPTY_STEPS'); ?></p>
							<p class="t-hidden" data-error-type="has-duplicate-unique"><?php echo JText::_('COM_ES_FIELDS_DUPLICATE_UNIQUE_FIELD_ERROR_MESSAGE'); ?></p>
						</div>
					</div>
				</div>
				<div class="es-dialog-footer">
					<div class="">
						<div class="es-dialog-footer-content">
							<button data-dialog-error-close type="button" class="btn btn-es-default btn-sm"><?php echo JTexT::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div data-template="workflow-config">
		<div id="es" class="es-dialog has-footer es-dialog--workflow type-html active no-tabs" data-config-dialog>
			<div class="es-dialog-modal" style="width: 810px; height: 560px;">
				<div class="es-dialog-header">
					<div class="es-dialog-header__grid">
						<div class="es-dialog-header__cell">
							<div class="es-wf-dialog-title"><?php echo JText::_('COM_ES_WORKFLOW_CONFIGURATION'); ?></div>
						</div>
					</div>
				</div>
				<div class="es-dialog-body">
					<div class="es-dialog-container">
						<div class="es-dialog-content">
							<div class="es-wf-settings-form o-form-horizontal">
								<div class="o-form-group">
									<label class="o-control-label">
										<strong><?php echo JText::_('COM_EASYSOCIAL_SETTINGS_TITLE'); ?></strong>
										<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo JText::_('COM_ES_WORKFLOW_TITLE_TOOLTIP'); ?>"></i>
									</label>

									<div class="o-control-input">
										<input type="text" id="title" name="title" value="<?php echo $workflow->getTitle(); ?>" class="o-form-control" data-workflow-config-title>
									</div>
								</div>
								<div class="o-form-group">
									<label class="o-control-label">
										<strong><?php echo JText::_('COM_ES_DESCRIPTION'); ?></strong>
										<i class="fa fa-question-circle t-lg-pull-right t-lg-ml--md" data-es-provide="tooltip" data-placement="right" data-original-title="<?php echo JText::_('COM_ES_WORKFLOW_DESCRIPTION_TOOLTIP'); ?>"></i>
									</label>

									<div class="o-control-input">
										<textarea class="o-form-control" rows="8" id="description" name="description" data-workflow-config-description><?php echo $workflow->getDescription(); ?></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="es-dialog-footer">
					<div class="">
						<div class="es-dialog-footer-content">
							<button data-field-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
							<button data-workflow-config-save-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_DONE_BUTTON'); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div data-template="step">
		<div class="es-wf-stepbar__item" data-step-item data-id data-isnew="true">
			<a href="javascript:void(0);" class="es-wf-stepbar__drag">
				<i class="fa fa-bars"></i>
			</a>
			<a href="javascript:void(0);" class="es-wf-stepbar__link" data-step-title><?php echo JText::_('COM_ES_WORKFLOW_STEP_DEFAULT_TITLE') ?></a>
		</div>
	</div>

	<div data-template="field">
		<div class="es-wf-field" data-field-item data-id data-appid data-ordering data-isNew="true" data-element>
			<span class="es-wf-field__item-checkbox">
				<input type="checkbox" data-field-item-checkbox />
			</span>

			<div class="">
				<a href="javascript:void(0);" class="es-wf-field__drag-icon">
					<i class="fa fa-bars"></i>
				</a>
				<span class="t-hidden" data-field-item-required>*</span>
				<span data-field-item-title data-field-item-edit></span>
				<span class="es-wf-field__link-label t-hidden" data-field-item-conditional><i class="fa fa-link"></i></span>
			</div>
			<div class="es-wf-field__action">
				<div class="es-wf-action">
					<span class="o-label o-label--primary t-lg-mr--md" data-field-item-element></span>
					<a href="javascript:void(0);" data-field-item-edit data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_EDIT'); ?>">
						<i class="far fa-edit"></i>
					</a>
					<a href="javascript:void(0);" data-field-item-move data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_MOVE'); ?>">
						<i class="fa fa-exchange-alt"></i>
					</a>
					<a href="javascript:void(0);" data-field-item-delete>
						<i class="fa fa-times"></i>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div data-template="editor">
		<div class="es-wf-content t-hidden" data-content data-id>
			<div class="es-wf-content__hd">
				<div class="">
					<div class="es-wf-content-step-title" data-step-title><?php echo JText::_('COM_ES_WORKFLOW_STEP_DEFAULT_TITLE'); ?></div>
					<div class="es-wf-content-step-desc" data-step-description><?php echo JText::_('COM_ES_WORKFLOW_STEP_DEFAULT_TITLE_DESC'); ?></div>
				</div>
				<div class="es-wf-content__hd-action">
					<div class="es-wf-action">
						<a href="javascript:void(0);" data-step-edit>
							<?php echo JText::_('COM_ES_EDIT'); ?>
						</a>
						<a href="javascript:void(0);" data-step-delete>
							<i class="fa fa-times"></i>
						</a>
					</div>
				</div>
			</div>
			<div class="es-wf-content__bd is-empty" data-fields-wrapper>
				<div data-fields></div>
				<div class="es-wf-content__empty">
					<div class="o-empty">
						<div class="o-empty__content">
							<?php echo $this->includeTemplate('admin/workflows/form/steps/placeholder', ['action' => 'prepend']); ?>

							<div class="o-empty__text"><?php echo JText::_('COM_ES_WORKFLOW_EMPTY'); ?></div>
							<div class="o-empty__text t-text--muted"><?php echo JText::_('COM_ES_WORKFLOW_EMPTY_DESC'); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div data-template="alert">
		<div class="o-alert o-alert--dismissible">
			<button type="button" class="o-alert__close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
			<div data-message></div>
		</div>
	</div>

	<div data-template="placeholder-before">
		<?php echo $this->includeTemplate('admin/workflows/form/steps/placeholder', ['action' => 'prepend']); ?>
	</div>

	<div data-template="placeholder-after">
		<?php echo $this->includeTemplate('admin/workflows/form/steps/placeholder', ['action' => 'append']); ?>
	</div>

</div>
