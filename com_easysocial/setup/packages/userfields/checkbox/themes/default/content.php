<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="es-checkbox-group-<?php echo $inputName; ?>"
	data-field-checkbox
	data-error-empty="<?php echo JText::_('PLG_FIELDS_CHECKBOX_CHECK_AT_LEAST_ONE_ITEM', true);?>"
>
	<?php foreach( $options as $key => $option ){ ?>
	<div class="o-checkbox">
		<input type="checkbox"
			id="<?php echo $inputName; ?>-<?php echo $option->id;?>"
			name="<?php echo $inputName; ?>[]"
			value="<?php echo !empty( $option->value ) ? $option->value : $option->title; ?>"
			data-field-checkbox-item
			<?php echo ( !empty( $selected ) && in_array( $option->value , $selected ) ) || ( empty( $selected ) && $option->default == 1 ) ? ' checked="checked"' : '';?>
		/>
		<label for="<?php echo $inputName; ?>-<?php echo $option->id;?>" class="option"><?php echo $option->get( 'title' ); ?></label>
	</div>
	<?php } ?>
</div>
