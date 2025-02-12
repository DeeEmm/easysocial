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
<div class="es-field-datetime-group">
<?php echo JText::_('FIELDS_USER_DATETIME_HOUR'); ?>
<select
    data-field-datetime-hour
    class="o-form-control input-sm"
>
    <option value=""><?php echo JText::_('FIELDS_USER_DATETIME_HOUR'); ?></option>

    <?php $start = $params->get('time_format') == 1 ? 1 : 0; ?>
    <?php $end = $params->get('time_format') == 1 ? 12 : 23; ?>

    <?php for ($i = $start; $i <= $end; $i++) { ?>
    <option value="<?php echo $i; ?>" <?php if ($hour == $i) { ?>selected="selected"<?php } ?>><?php echo $i; ?></option>
    <?php } ?>
</select>
</div>
