<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<?php if ($group->isOpen()) { ?>
<span data-original-title="<?php echo JText::_('COM_ES_GROUPS_PUBLIC_GROUP_TOOLTIP', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
    <i class="fa fa-globe"></i>&nbsp; <?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?>
</span>
<?php } ?>

<?php if ($group->isClosed()) { ?>
<span data-original-title="<?php echo JText::_('COM_ES_GROUPS_PRIVATE_GROUP_TOOLTIP', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
    <i class="fa fa-user"></i>&nbsp; <?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?>
</span>
<?php } ?>

<?php if ($group->isInviteOnly()) { ?>
<span data-original-title="<?php echo JText::_('COM_EASYSOCIAL_GROUPS_INVITE_GROUP_TOOLTIP', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
    <i class="fa fa-lock"></i>&nbsp; <?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?>
</span>
<?php } ?>