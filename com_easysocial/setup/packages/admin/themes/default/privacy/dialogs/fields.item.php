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
<div class="es-privacy-option-list__item" data-field-item>
	<div class="o-grid">
		<div class="o-grid__cell">
			<div class="">
				<div class="o-select-group">
					<select name="fields[]" class="o-form-control" data-field-select>
						<option value=""><?php echo JText::_('COM_ES_PRIVACY_SELECT_FIELD'); ?></option>
						<?php foreach ($fields as $field) { ?>
						<?php $selectStr = ($selected && $selected == $field->unique_key . '|' . $field->element) ? ' selected="selected"' : ''; ?>
						<option value="<?php echo $field->unique_key . '|' . $field->element; ?>"<?php echo $selectStr; ?>><?php echo JText::_($field->title); ?></option>
						<?php } ?>
					</select>
					<label for="" class="o-select-group__drop"></label>
				</div>
			</div>
		</div>
		<div class="o-grid__cell o-grid__cell--auto-size">
			<div class="es-privacy-option-list__remove">
				<a href="javascript:void(0);" data-field-remove><i class="fa fa-minus-circle"></i></a>
			</div>
		</div>
	</div>
</div>
