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
<ul class="g-list-unstyled">
	<?php foreach( $field->options as $option ){ ?>
	<li>
		<div class="form-inline">
			<input type="checkbox" name="<?php echo $field->inputName;?>[]"
				value="<?php echo $option->value;?>" id="<?php echo $option->title; ?>"
				<?php echo in_array( $option->value , $params->get( $field->name , $field->default ) ) ? ' checked="checked"' : '';?>
			/>
			<label for="<?php echo $option->title;?>"><?php echo $option->title; ?></label>
		</div>
	</li>
	<?php } ?>
</ul>
