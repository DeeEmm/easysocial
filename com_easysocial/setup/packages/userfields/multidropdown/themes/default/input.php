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
<li class="data-field-multidropdown-item" data-field-multidropdown-item>
	<div class="o-grid">
		<div class="o-grid__cell-auto-size">
			<span class="item-move" data-field-multidropdown-move><i class="fa fa-bars"></i></span>
		</div>

		<div class="o-grid__cell">
			<label for="<?php echo $inputName; ?>[]" class="t-hidden">Multidropdown</label>
			<select id="<?php echo $inputName; ?>[]" name="<?php echo $inputName; ?>[]" class="o-form-control" data-field-multidropdown-input>
				<?php foreach ($choices as $id => $choice) { ?>
				<option value="<?php echo $choice->value; ?>" <?php echo $value && $value === $choice->value || ($showDefault && !$value && $choice->default) ? 'selected="selected"' : '';?>>
					<?php echo JText::_($choice->title); ?>
				</option>
				<?php } ?>
			</select>

			<a href="javascript:void(0);" class="btn btn-es-danger-o" data-field-multidropdown-delete>
				<i class="fa fa-minus-circle"></i>
			</a>

			<a href="javascript:void(0);" class="btn btn-es-success-o" data-field-multidropdown-add>
				<i class="fa fa-plus-circle"></i>
			</a>
		</div>
	</div>
</li>
