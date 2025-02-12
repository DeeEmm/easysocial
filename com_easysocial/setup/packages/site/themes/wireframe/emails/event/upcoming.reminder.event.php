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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<?php if ($events) { ?>
<p style="text-align:left;">
    <?php foreach($events as $event) { ?>
    <div style="padding-top: 10px">
        <div>
            <h2 style="margin-bottom:2px;"><?php echo $event->title; ?></h2>
        </div>
        <div>
            <span>
            <?php if ($event->all_day) { ?>
                <?php $startDate = ES::date($event->start_gmt)->toFormat('d-m-Y'); ?>
                <?php $endDate = ES::date($event->end_gmt)->toFormat('d-m-Y'); ?>
            <?php } else { ?>
                <?php $startDate = ES::date($event->start_gmt)->toFormat('d-m-Y H:i'); ?>
                <?php $endDate = ES::date($event->end_gmt)->toFormat('d-m-Y H:i'); ?>
            <?php } ?>
            <?php if ($event->start == $event->end || $event->end == '0000-00-00 00:00:00') { ?>
                <?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_UPCOMING_REMINDER_ON', $startDate); ?>
            <?php } else { ?>
                <?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_UPCOMING_REMINDER_FROM_TO', $startDate, $endDate); ?>
            <?php } ?>            
            </span>
            <span><?php echo ($event->all_day) ? ' ( ' . JText::_('COM_EASYSOCIAL_EMAILS_UPCOMING_REMINDER_ALLDAY') . ' )' : ''; ?></span>
        </div>
        <?php if ($event->address) { ?>
            <div>
                <?php echo JText::sprintf('COM_EASYSOCIAL_EMAILS_UPCOMING_REMINDER_VENUE', $event->address); ?>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
</p>
<?php } ?>
