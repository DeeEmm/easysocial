<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<ul id="userForm" class="nav nav-tabs nav-tabs-icons">
    <li class="tabItem<?php echo $activeTab == 'settings' ? ' active' : '';?>">
        <a data-es-toggle="tab" href="#settings" data-form-tabs data-item="settings">
            <?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PROFILE_GENERAL');?>
        </a>
    </li>
</ul>
