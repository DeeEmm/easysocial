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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>500</width>
	<height>500</height>
	<selectors type="json">
	{
		"{cancelButton}" : "[data-cancel-button]",
		"{saveButton}" : "[data-save-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo $title; ?></title>
	<content>

	<div class="es-privacy-option">
		<div class="es-privacy-option__insert">
			<a href="javascript:void(0);" class="btn btn-es-default-o btn-block" data-field-add><?php echo JText::_('COM_ES_PRIVACY_ADD_FIELD'); ?></a>
		</div>

		<div class="es-privacy-option-list" data-field-wrapper>

			<?php if ($current) { ?>
			<?php foreach ($current as $item) { ?>
				<?php echo $this->output('admin/privacy/dialogs/fields.item', array('fields' => $fields, 'selected' => $item)); ?>
			<?php } ?>
			<?php } else { ?>
			<?php echo $this->output('admin/privacy/dialogs/fields.item', array('fields' => $fields, 'selected' => '')); ?>
			<?php } ?>

			<div class="es-privacy-option-list__item t-hidden" data-field-template>
				<div class="o-grid">
					<div class="o-grid__cell">
						<div class="">
							<div class="o-select-group">
								<select name="fields[]" class="o-form-control" data-field-select>
									<option value=""><?php echo JText::_('COM_ES_PRIVACY_SELECT_FIELD'); ?></option>
									<?php foreach ($fields as $field) { ?>
									<option value="<?php echo $field->unique_key . '|' . $field->element; ?>"><?php echo JText::_($field->title); ?></option>
									<?php } ?>
								</select>
								<label for="" class="o-select-group__drop"></label>
							</div>
							<div class="o-alert o-alert--warning t-hidden" data-field-notice><?php echo JText::_('COM_ES_PRIVACY_WARNING_FIELD_EXISTS'); ?></div>
						</div>
					</div>
					<div class="o-grid__cell o-grid__cell--auto-size">
						<div class="es-privacy-option-list__remove">
							<a href="javascript:void(0);" data-field-remove><i class="fa fa-minus-circle"></i></a>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-save-button type="button" class="btn btn-es-primary"><?php echo JText::_('COM_ES_PRIVACY_UPDATE'); ?></button>
	</buttons>
</dialog>
