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
<?php echo (isset($advancedsearchlink) && $advancedsearchlink) ? '<a href="' . $advancedsearchlink . '">' : ''; ?>
<?php if( $value === '1' ){ ?>
	<span class="success"><i class="fa fa-check"></i> <?php echo JText::_( 'PLG_FIELDS_BOOLEAN_TRUE' ); ?></span>
<?php } else { ?>
	<span class="error"><i class="fa fa-times"></i> <?php echo JText::_( 'PLG_FIELDS_BOOLEAN_FALSE' ); ?></span>
<?php } ?>
<?php echo (isset($advancedsearchlink) && $advancedsearchlink) ? '</a>' : ''; ?>
