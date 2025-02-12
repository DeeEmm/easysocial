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
<input type="checkbox" id="cb<?php echo $number;?>" name="<?php echo $name;?>[]" value="<?php echo $id;?>"
	<?php if( $allowed ){ ?>
	onclick="Joomla.isChecked( this.checked );" title="<?php echo JText::sprintf( 'COM_EASYSOCIAL_GRID_CHECKBOX_ROW' , ( $number + 1 ) );?>"
	data-table-grid-id
	<?php } else { ?>
	disabled="disabled"
	data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_NOT_ALLOWED_TO_SELECT_RECORD' );?>"
	data-es-provide="tooltip"
	<?php } ?>
/>
