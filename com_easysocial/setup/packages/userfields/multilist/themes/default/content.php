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
<div data-field-multilist data-error-empty="<?php echo JText::_('PLG_FIELDS_MULTILIST_VALIDATION_PLEASE_SELECT_A_VALUE', true);?>">
	<select class="o-form-control"
		name="<?php echo $inputName; ?>[]"
		id="<?php echo $inputName; ?>"
		multiple="multiple"
		data-field-multilist-item
		data-id="<?php echo $field->id;?>"
	>
		<?php foreach( $options as $id => $option ){ ?>
			<option value="<?php echo $option->value;?>"<?php echo in_array( $option->value, $selected ) ? ' selected="selected"' : '';?>><?php echo JText::_($option->title); ?></option>
		<?php } ?>
	</select>
</div>
