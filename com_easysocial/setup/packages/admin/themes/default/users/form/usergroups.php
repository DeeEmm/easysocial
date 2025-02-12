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
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-head">
                <b><?php echo JText::_( 'COM_EASYSOCIAL_USERS_USER_GROUPS' ); ?></b>
                <p>
                	<?php echo JText::_( 'COM_EASYSOCIAL_USERS_USER_GROUPS_DESC' ); ?>
                </p>
            </div>

            <div class="panel-body">
                <div class="form-group">
                	<label for="theme" class="col-md-4"><?php echo JText::_( 'COM_EASYSOCIAL_PROFILES_FORM_GROUPS_DEFAULT_USER_GROUP' );?></label>
                	<div class="col-md-8">
                		<?php echo $this->html( 'tree.groups' , 'gid' , $userGroups , $guestGroup ); ?>
                	</div>
                </div>
            </div>
        </div>
    </div>
</div>
