<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-textbox data-min="<?php echo $params->get( 'min' ); ?>" data-max="<?php echo $params->get( 'max' ); ?>"
	data-error-required="<?php echo JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_REQUIRED', true);?>"
	data-error-short="<?php echo JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_TOO_SHORT', true);?>"
	data-error-long="<?php echo JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_TOO_LONG', true);?>"
	data-error-invalid="<?php echo JText::_('PLG_FIELDS_TEXTBOX_VALIDATION_INPUT_INVALID_FORMAT', true);?>"
>
	<input type="text" id="<?php echo $inputName;?>"
		value="<?php echo $value; ?>"
		name="<?php echo $inputName;?>"
		class="o-form-control"
		placeholder="<?php echo JText::_( $params->get( 'placeholder' ), true ); ?>"
		data-field-textbox-input
		<?php if( $params->get( 'readonly' ) ) { ?>disabled="disabled"<?php } ?>
		<?php if( $params->get( 'required' ) ) { ?>data-check-required<?php } ?>
		<?php if( $params->get( 'regex_validate' ) ) { ?>
		data-check-validate
		data-check-format="<?php echo $params->get( 'regex_format' ); ?>"
		data-check-modifier="<?php echo $params->get( 'regex_modifier' ); ?>"
		<?php } ?>
	/>

	<?php if (isset($isDisabled) && $isDisabled) { ?>
	<div class="o-alert o-alert--info t-lg-mt--sm"><?php echo $disabledInfo; ?></div>
	<?php } ?>
	<div class="es-fields-error-note" data-field-error></div>
</div>
