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
<div class="o-select-group">
	<select name="<?php echo $name; echo ($ismultiple) ? '[]' : ''; ?>" id="<?php echo empty($id) ? $name : $id; ?>" <?php echo $attributes; ?> <?php echo ( $ismultiple ) ? 'multiple' : ''; ?> class="<?php echo $class;?>" autocomplete="off">
		<?php foreach( $options as $value => $text ) { ?>
		<?php $isSelected = ''; ?>

		<?php if( is_array( $selected ) ) { ?>
			<?php
				foreach( $selected as $item ) {
					if( $item == $value )
					{
						$isSelected = ' selected="selected"';
						break;
					}
				}
			?>
		<?php } else { ?>
			<?php $isSelected = ( $selected == $value ) ? ' selected="selected"' : ''; ?>
		<?php } ?>


		<option value="<?php echo $value; ?>" <?php echo $isSelected; ?> ><?php echo $text; ?></option>
		<?php } ?>
	</select>

	<label for="<?php echo empty($id) ? $name : $id; ?>" class="o-select-group__drop"></label>
</div>
