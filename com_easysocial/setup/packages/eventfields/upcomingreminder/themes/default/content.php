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
<div data-field-reminder>
    <div class="o-row">
        <div class="o-col--3">
            <div class="o-input-group">
                <input type="text" name="event_reminder" id="event_reminder" class="o-form-control text-center" autocomplete="off" value="<?php echo $value; ?>" 
                    placeholder="<?php echo JText::_('FIELDS_EVENT_UPCOMINGREMINDER_PLACEHOLDER'); ?>" data-input
                    <?php echo $params->get('readonly') ? ' disabled="disabled"' : '';?>
                />
                <span class="o-input-group__addon"><?php echo JText::_('COM_EASYSOCIAL_DAYS');?></span>
            </div>
        </div>
        <div class="o-col--9"></div>
    </div>
</div>
