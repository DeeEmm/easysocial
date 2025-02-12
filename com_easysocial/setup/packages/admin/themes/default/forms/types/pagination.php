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
<div class="row">
	<div class="col-sm-5">
		<select name="<?php echo $field->inputName;?>" class="o-form-control input-sm<?php echo isset( $field->class ) ? $field->class : '';?>">
			<?php for( $i = $field->start; $i <= $field->max; $i = ( $i + $field->increment ) ){ ?>
				<option value="<?php echo $i;?>"<?php echo $params->get( $field->name , $field->default ) == $i ? ' selected="selected"' : '';?>><?php echo $i;?></option>
			<?php } ?>
			<option value="0"<?php echo $params->get( $field->name , $field->default ) == 0 ? ' selected="selected"' : '';?>><?php echo JText::_( 'COM_EASYSOCIAL_FORM_ALL' ); ?></option>
		</select>
	</div>
</div>
