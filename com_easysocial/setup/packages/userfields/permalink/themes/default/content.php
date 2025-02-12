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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div data-field-permalink data-max="<?php echo $params->get( 'max' ); ?>" data-error-length="<?php echo JText::_('PLG_FIELDS_PERMALINK_EXCEEDED_MAX_LENGTH');?>" data-error-required="<?php echo JText::_('PLG_FIELDS_PERMALINK_REQUIRED', true);?>">
	<div class="o-input-group">
		<label for="<?php echo $element;?>" class="t-hidden">Permalink</label>
		<input type="text" class="o-form-control validation keyup length-4 required"
			name="<?php echo $inputName;?>"
			id="<?php echo $element;?>"
			value="<?php echo $value; ?>"
			autocomplete="off"
			data-permalink-input
			placeholder="<?php echo JText::_( 'PLG_FIELDS_PERMALINK_PLACEHOLDER' ); ?>" />

		<?php if( $params->get( 'check_permalink' , true ) ){ ?>
		<span class="o-input-group__btn">
			<button type="button" class="btn btn-es-default-o" data-permalink-check><?php echo JText::_( 'PLG_FIELDS_PERMALINK_CHECK_BUTTON' );?></button>
		</span>
		<?php } ?>
	</div>

	<div class="">
		<span class="help-block" data-permalink-available style="display: none;">
			<span class="t-text--success"><?php echo JText::_( 'PLG_FIELDS_PERMALINK_AVAILABLE' );?></span>
		</span>
	</div>
	<div class="es-fields-error-note" data-field-error></div>
</div>
