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
<div data-field-gender data-error-required="<?php echo JText::_('PLG_FIELDS_GENDER_VALIDATION_GENDER_REQUIRED', true);?>">
	
	<?php if (false) { ?>
	<select id="<?php echo $inputName;?>" name="<?php echo $inputName;?>" data-field-gender-input class="o-form-control input-sm">
		<option value=""<?php echo $value == 0 ? ' selected="selected"' : '';?>><?php echo JText::_( 'PLG_FIELDS_GENDER_SELECT_A_GENDER' , true ); ?></option>
		<option value="1"<?php echo $value == 1 ? ' selected="selected"' : '';?>><?php echo JText::_( 'PLG_FIELDS_GENDER_SELECT_MALE' , true );?></option>
		<option value="2"<?php echo $value == 2 ? ' selected="selected"' : '';?>><?php echo JText::_( 'PLG_FIELDS_GENDER_SELECT_FEMALE' , true ); ?></option>
	</select>
	<?php } ?>

	<?php // when custom is disabled, we will show the radion buttons horizontally ?>
	<?php // when custom is enabled, we will show the radion buttons vertically ?>
	
	<?php if ($options) { ?>
		<?php foreach ($options as $key => $option) { ?>
		<div class="o-radio<?php echo !$params->get('custom') ? ' o-radio--inline' : ''; ?>">
			<input type="radio" 
					value="<?php echo $option->value; ?>" 
					name="<?php echo $inputName; ?>" id="<?php echo $option->id; ?>" <?php echo $value == $option->value ? 'checked="checked"' : '';?> 
					data-field-gender-select
					<?php if ($option->custom) { ?>
						data-field-gender-select-custom
					<?php } ?>
			/>
			<label for="<?php echo $option->id; ?>"><?php echo $option->title; ?></label>
		</div>
		<?php } ?>
	<?php } ?>
	
	
	<div class="es-fields-error-note" data-field-error></div>
</div>
