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
<?php if( count( $countries ) === 1 ) {
    echo (isset($advancedsearchlinks) && isset($advancedsearchlinks[0]) && $advancedsearchlinks[0]) ? '<a href="' . $advancedsearchlinks[0] . '">' : '';
	echo $countries[0];
    echo (isset($advancedsearchlinks) && isset($advancedsearchlinks[0]) && $advancedsearchlinks[0]) ? '</a>' : '';
} ?>

<?php if( count( $countries ) > 1 ) { ?>
	<ul>
		<?php for($i = 0; $i < count($countries); $i++ ){
            $country = $countries[$i];
        ?>
		<li>
            <?php echo (isset($advancedsearchlinks) && isset($advancedsearchlinks[$i]) && $advancedsearchlinks[$i]) ? '<a href="' . $advancedsearchlinks[$i] . '">' : ''; ?>
            <?php echo $country; ?>
            <?php echo (isset($advancedsearchlinks) && isset($advancedsearchlinks[$i]) && $advancedsearchlinks[$i]) ? '</a>' : ''; ?>
        </li>
		<?php } ?>
	</ul>
<?php } ?>
