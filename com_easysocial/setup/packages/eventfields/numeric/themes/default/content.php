<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-numeric
	data-error-required="<?php echo JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_REQUIRED', true);?>"
	data-error-short="<?php echo JText::sprintf('PLG_FIELDS_NUMERIC_TOO_SMALL', $params->get('min'));?>"
	data-error-long="<?php echo JText::sprintf('PLG_FIELDS_NUMERIC_TOO_BIG', $params->get('max'));?>"
	data-error-invalid="<?php echo JText::_('PLG_FIELDS_NUMERIC_INVALID_VALUE', true);?>"
>
	<div class="o-grid o-grid--1of4">
		<div class="o-grid__cell">
			<input type="number" id="<?php echo $inputName;?>"
				value="<?php echo $value; ?>"
				name="<?php echo $inputName;?>"
				class="o-form-control"
				placeholder="<?php echo JText::_($params->get('placeholder'), true); ?>"
				data-field-numeric-input
				data-min="<?php echo $params->get('min'); ?>" data-max="<?php echo $params->get('max'); ?>"
				<?php echo $params->get('readonly') ? 'disabled="disabled"' : '';?>
				<?php echo $params->get('required') ? 'data-check-required' : '';?>
			/>
		</div>
	</div>
	<div class="es-fields-error-note" data-field-error></div>
</div>