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

<?php if ($guest->isParticipant() || $event->isClusterMember()) { ?>
    <?php if ($event->isOver()) { ?>
        <nav class="media-meta pull-right">
        <?php if ($guest->isGoing()) { ?>
            <a class="btn btn-es-default btn-sm btn-es-success" href="javascript:void(0);"><i class="fa fa-user-plus"></i> <?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_GOING'); ?></a>
            <?php } ?>

            <?php if ($guest->isMaybe()) { ?>
            <a class="btn btn-es-default btn-sm btn-es-info" href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_MAYBE'); ?></a>
            <?php } ?>

            <?php if ($guest->isNotGoing()) { ?>
            <a class="btn btn-es-default btn-sm btn-es-danger" href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_NOTGOING'); ?></a>
            <?php } ?>
        </nav>
    <?php } else { ?>
        <?php if (empty($hideText) && !$guest->isPending()) { ?>
        <div class="es-rsvp-text pull-left">
            <?php echo JText::_('COM_EASYSOCIAL_EVENTS_RSVP_TO_THIS_EVENT'); ?>:
        </div>
        <?php } ?>
        <nav class="media-meta pull-right">
            <?php if ($guest->isPending()) { ?>
            <a class="btn btn-es-default btn-sm" href="javascript:void(0);" data-guest-action="withdraw" data-guest-withdraw>
                <?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_PENDING'); ?>
            </a>
            <?php } else { ?>
            <a class="btn btn-es-default btn-sm <?php if ($guest->isGoing()) { ?>btn-es-success<?php } ?>" href="javascript:void(0);" data-guest-action="state" data-guest-state="going"><i class="fa fa-user-plus"></i> <?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_GOING'); ?></a>

            <?php if ($event->getParams()->get('allowmaybe')) { ?>
            <a class="btn btn-es-default btn-sm <?php if ($guest->isMaybe()) { ?>btn-es-info<?php } ?>" href="javascript:void(0);" data-guest-action="state" data-guest-state="maybe"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_MAYBE'); ?></a>
            <?php } ?>

            <a class="btn btn-es-default btn-sm <?php if ($guest->isNotGoing()) { ?>btn-es-danger<?php } ?>" href="javascript:void(0);" data-guest-action="state" data-guest-state="notgoing"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_NOTGOING'); ?></a>
            <?php } ?>
        </nav>
    <?php } ?>
<?php } else { ?>
    <?php if (!$event->isOver()) { ?>
        <nav class="media-meta pull-right">
            <?php if ($event->seatsLeft() === 0) { ?>
                <?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_SEATS_LEFT'); ?>
            <?php } else { ?>

                <?php if (!$this->my->getAccess()->get('events.allow.join') && $this->my->getAccess()->exceeded('events.join', $this->my->getTotalEvents())) { ?>
                    <?php echo JText::_('COM_EASYSOCIAL_EVENTS_EXCEEDED_JOIN_LIMIT'); ?>
                <?php } ?>

            <?php } ?>

            <?php if ($this->my->getAccess()->get('events.allow.join') && !$this->my->getAccess()->exceeded('events.join', $this->my->getTotalEvents()) && $event->seatsLeft() !== 0) { ?>
                <?php if ($event->isOpen()) { ?>
                <a class="btn btn-es-default btn-sm" href="javascript:void(0);" data-guest-action="attend" data-guest-state="going"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_ATTEND_THIS_EVENT'); ?></a>
                <?php } ?>

                <?php if ($event->isClosed()) { ?>
                <a class="btn btn-es-default btn-sm" href="javascript:void(0);" data-guest-action="request" data-guest-request><?php echo JText::_('COM_EASYSOCIAL_EVENTS_REQUEST_TO_ATTEND_THIS_EVENT'); ?></a>
                <?php } ?>
            <?php } ?>
        </nav>
    <?php } ?>
<?php } ?>
