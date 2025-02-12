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
<div class="o-form-group <?php echo !empty( $error ) ? 'has-error' : ''; ?>" data-field data-field-<?php echo $field->id; ?> data-check data-error-required="<?php echo JText::_('PLG_FIELDS_TERMS_VALIDATION_REQUIRED', true);?>">

	<?php if ($params->get('dialog')) { ?>
		<div class="o-control-label"></div>
		<div class="o-control-input" data-content>
			<div class="o-checkbox">
				<input type="checkbox" id="terms-<?php echo $inputName;?>" name="<?php echo $inputName;?>" data-field-terms-checkbox <?php if ($value) { ?>checked="checked"<?php } ?> />
				<label for="terms-<?php echo $inputName;?>">
					<?php echo $required ? '<span>*</span>' : ''; ?>
					<a href="javascript:void(0);" data-field-terms-dialog><?php echo JText::_($params->get('labeltext', JText::_('PLG_FIELDS_TERMS_ACCEPT_TERMS')));?></a>
				</label>
			</div>
			<div class="es-fields-error-note" data-field-error></div>
		</div>
	<?php } else { ?>
		<div class="o-control-label"></div>

		<div class="o-control-input">
			<div class="o-form-group" data-field-terms data-content>
				<label for="terms" class="t-hidden" >Terms</label>
				<textarea id="terms" class="o-form-control" readonly="readonly" data-field-terms-textbox><?php echo JText::_($params->get('message', JText::_('PLG_FIELDS_TERMS_CONDITION_MESSAGE_TERMS')));?></textarea>
			</div>
			<div class="o-form-group">
				<div class="o-checkbox">
					<input type="checkbox" id="terms-<?php echo $inputName;?>" name="<?php echo $inputName;?>" data-field-terms-checkbox <?php echo $value ? 'checked="checked"' : '';?> />
					<label for="terms-<?php echo $inputName;?>">
					<?php echo $required ? '<span>*</span>' : ''; ?>
					<?php echo JText::_($params->get('labeltext', JText::_('PLG_FIELDS_TERMS_ACCEPT_TERMS')));?></label>
				</div>
				<div class="es-fields-error-note" data-field-error></div>
			</div>
		</div>
	<?php } ?>
</div>

