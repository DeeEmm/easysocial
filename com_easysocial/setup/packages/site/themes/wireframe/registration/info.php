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
<div id="ezs-page" class="mtl">
    <div id="ezs-main">
		<h3><?php echo JText::_( 'Edit Profile' );?></h3>
		<?php foreach( $this->fields as $group ) { ?>
		    <h4><?php echo JText::_( $group->title ); ?></h4>
		    <ul>
		        <?php foreach( $group->childs as $field ){ ?>
		        <li>
		            <label><?php echo JText::_( $field->title );?></label>
		            <?php echo $field->output; ?>
		        </li>
		        <?php } ?>
		    </ul>
		<?php } ?>
    </div>
</div>
