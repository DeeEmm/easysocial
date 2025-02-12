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
 data-es-provide="popover"
<?php if( $content ){ ?>
 data-content="<?php echo $content;?>"
<?php } ?>
<?php if( $title ){ ?>
 data-title="<?php echo $title;?>"
<?php } ?>
<?php if( $placement ){ ?>
 data-placement="<?php echo $placement;?>"
<?php } ?>
<?php if( $placeholder ){ ?>
 placeholder="<?php echo $placeholder;?>"
<?php } ?>
<?php if( $html ){ ?>
 data-html="true"
<?php } ?>
