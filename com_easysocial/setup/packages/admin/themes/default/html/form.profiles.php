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
<select class="o-form-control input-sm" autocomplete="off" name="<?php echo $name;?>" id="<?php echo empty( $id ) ? $name : $id; ?>" <?php echo $multiple ? ' multiple="multiple"' : '';?> <?php echo $attributes; ?>>
	<?php foreach( $profiles as $profile ){ ?>
		<option value="<?php echo $profile->id; ?>"<?php echo $profile->id == $selected || (is_array($selected) && in_array($profile->id, $selected)) ? ' selected="selected"' : '';?>><?php echo $profile->get( 'title' ); ?></option>
	<?php } ?>
</select>
