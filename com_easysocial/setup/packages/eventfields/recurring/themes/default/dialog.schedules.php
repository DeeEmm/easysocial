<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
    <width>450</width>
    <height>250</height>
    <selectors type="json">
    {
        "{cancelButton}": "[data-cancel-button]"
    }
    </selectors>
    <bindings type="javascript">
    {
        "{cancelButton} click": function() {
            this.parent.close();
        }
    }
    </bindings>
    <title><?php echo JText::_('PLG_FIELDS_EVENT_RECURRING_SCHEDULES_DIALOG_TITLE'); ?></title>
    <content>
        <p class="t-lg-mt--md"><?php echo JText::_('PLG_FIELDS_EVENT_RECURRING_SCHEDULES_CONTENT');?></p>

        <table class="table table-striped">
            <thead>
                <td width="1%">
                    <b><?php echo JText::_('#');?></b>
                </td>
                <td>
                    <b><?php echo JText::_('COM_ES_CREATION_DATE'); ?></b>
                </td>
            </thead>

            <tbody>
            <?php $i = 1; ?>
            <?php foreach ($schedules as $schedule) { ?>
                <tr>
                    <td><?php echo $i;?></td>
                    <td>
                        <?php echo $schedule; ?>
                    </td>
                </tr>
                <?php $i++; ?>
            <?php } ?>
            </tbody>
        </table>
    </content>
    <buttons>
        <button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON'); ?></button>
    </buttons>
</dialog>
