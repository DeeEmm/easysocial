<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-select-group es-field-datetime-group">
	<span class="t-hidden" data-datetime-day-label><?php echo JText::_('PLG_FIELDS_DATETIME_DAY'); ?></span>
	<label for="day" class="t-hidden"><?php echo JText::_('PLG_FIELDS_DATETIME_DAY'); ?></label>
	<select id="day" class="o-form-control" data-field-datetime-day>
	    <option value=""><?php echo JText::_('PLG_FIELDS_DATETIME_DAY'); ?></option>
	    <?php for ($i = 1; $i <= $maxDay; $i++) { ?>
	        <option value="<?php echo $i; ?>" <?php if ($day == $i) { ?>selected="selected"<?php } ?>><?php echo $i; ?></option>
	    <?php } ?>
	</select>
    <label class="o-select-group__drop" for=""></label>
</div>
