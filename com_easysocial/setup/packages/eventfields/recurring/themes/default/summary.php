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
<?php if ($showWarningMessages && $changed && $type != 'none') { ?>
<p class="mb-10">
    <i class="fa fa-notification-2"></i>&nbsp; <?php echo JText::_('FIELD_EVENT_RECURRING_EVENTS_WILL_BE_RECREATED'); ?>
</p>
<?php } ?>

<?php if ($showWarningMessages && $hasChildren && $type == 'none') { ?>
<p>
    <i class="fa fa-notification-2"></i>&nbsp; <?php echo JText::_('FIELD_EVENT_RECURRING_EVENTS_WILL_BE_DELETED'); ?>
</p>
<?php } ?>

<?php if ($type != 'none') { ?>
<div class="t-lg-ml--xl">
    <div class="o-btn-group">
        <a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-show-schedules>
            <i class="fa fa-calendar"></i>&nbsp; <?php echo JText::_('FIELD_EVENT_RECURRING_EVENTS_SHOW_SCHEDULE'); ?>
        </a>

        <?php if ($hasChildren) { ?>
        <a href="javascript:void(0);" class="btn btn-es-danger-o btn-sm" data-recurring-delete>
            <i class="fa fa-times"></i>&nbsp; <?php echo JText::_('FIELD_EVENT_RECURRING_EVENTS_CLEAR_EVENTS'); ?>
        </a>
        <?php } ?>
    </div>
</div>
<?php } ?>
