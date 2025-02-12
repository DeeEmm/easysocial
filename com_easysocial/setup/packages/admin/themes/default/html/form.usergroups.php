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
<select name="<?php echo $name;?>" class="o-form-control input-sm">
<?php for( $i = 0, $n = count( $groups); $i < $n; $i++ ){ ?>
	<option value="<?php echo $groups[ $i ]->id;?>"><?php echo str_repeat( ' - ' , $groups[ $i ]->level );?> <b><?php echo $groups[ $i ]->title;?></b></option>
<?php } ?>
</select>
