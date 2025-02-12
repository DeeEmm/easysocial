<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<li>
    <a href="<?php echo $event->getPermalink(false, false, 'edit');?>">
        <?php echo ($event->isDraft()) ? JText::_('COM_EASYSOCIAL_EVENTS_REVIEW_EVENT') : JText::_('COM_EASYSOCIAL_EVENTS_EDIT_EVENT'); ?>
    </a>
</li>

<?php if ($this->my->isSiteAdmin() && !$event->isDraft()) { ?>
    <?php if ($event->isFeatured()) { ?>
    <li>
        <a href="javascript:void(0);" data-es-events-unfeature data-id="<?php echo $event->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_REMOVE_FEATURED');?></a>
    </li>
    <?php } else { ?>
    <li>
        <a href="javascript:void(0);" data-es-events-feature data-id="<?php echo $event->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_SET_FEATURED');?></a>
    </li>
    <?php } ?>
<?php } ?>

<?php if ($showAdminAction) { ?>
    <li class="divider"></li>
    <?php echo $eventAdminStart; ?>
    <?php echo $eventAdminEnd; ?>
<?php } ?>

<li class="divider"></li>
<li>
    <?php if ($event->isPublished() && $event->canUnpublish()) { ?>
        <a href="javascript:void(0);" data-es-events-unpublish data-id="<?php echo $event->id;?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_UNPUBLISH_EVENT');?></a>
    <?php } else if ($event->canPublish()){ ?>
        <a href="javascript:void(0);" data-es-events-publish data-id="<?php echo $event->id;?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_PUBLISH_EVENT');?></a>
    <?php } ?>
</li>

<?php if ($event->canDelete()) { ?>
    <li>
        <a href="javascript:void(0);" data-es-events-delete data-id="<?php echo $event->id;?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_DELETE_EVENT');?></a>
    </li>
<?php } ?>
