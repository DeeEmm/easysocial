<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
    <width>400</width>
    <height>150</height>
    <selectors type="json">
    {
        "{closeButton}"  : "[data-close-button]"
    }
    </selectors>
    <bindings type="javascript">
    {
        "{closeButton} click": function()
        {
            this.parent.close();
        }
    }
    </bindings>
    <title><?php echo JText::_('COM_EASYSOCIAL_GROUPS_DIALOG_INVITED_JOIN_TITLE'); ?></title>
    <content>
        <p class="mt-5">
            <img src="<?php echo $group->getAvatar();?>" class="ml-5 es-avatar" align="right" />

            <?php echo JText::sprintf('COM_EASYSOCIAL_GROUPS_DIALOG_INVITED_JOIN_CONTENT' , '<a href="' . $group->getPermalink() . '">' . $group->getName() . '</a>');?>
        </p>
    </content>
    <buttons>
        <a href="<?php echo $group->getPermalink();?>" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON');?></a>
    </buttons>
</dialog>
