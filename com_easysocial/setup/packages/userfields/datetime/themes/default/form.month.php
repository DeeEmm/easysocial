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
<div class="o-select-group es-field-datetime-group">
    <label for="month" class="t-hidden">Month</label>
    <select id="month" class="o-form-control" data-field-datetime-month>

        <option value="">
            <?php echo JText::_('PLG_FIELDS_DATETIME_MONTH'); ?>
        </option>

        <option value="01" <?php echo $month == 1 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('January'); ?>
        </option>
        <option value="02" <?php echo $month == 2 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('February'); ?>
        </option>
        <option value="03" <?php echo $month == 3 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('March'); ?>
        </option>
        <option value="04" <?php echo $month == 4 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('April'); ?>
        </option>
        <option value="05" <?php echo $month == 5 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('May'); ?>
        </option>
        <option value="06" <?php echo $month == 6 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('June'); ?>
        </option>
        <option value="07" <?php echo $month == 7 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('July'); ?>
        </option>
        <option value="08" <?php echo $month == 8 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('August'); ?>
        </option>
        <option value="09" <?php echo $month == 9 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('September'); ?>
        </option>
        <option value="10" <?php echo $month == 10 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('October'); ?>
        </option>
        <option value="11" <?php echo $month == 11 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('November'); ?>
        </option>
        <option value="12" <?php echo $month == 12 ? 'selected="selected"' : '';?>>
            <?php echo JText::_('December'); ?>
        </option>
    </select>
    <label class="o-select-group__drop" for=""></label>
</div>
