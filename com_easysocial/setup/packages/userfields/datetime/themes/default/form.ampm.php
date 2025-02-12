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
<?php echo JText::_('FIELDS_USER_DATETIME_AMPM'); ?>
<select
    data-field-datetime-ampm
    class="o-form-control input-sm"

>
    <option value="am" <?php if ($value == 'am') { ?>selected="selected"<?php } ?>>AM</option>
    <option value="pm" <?php if ($value == 'pm') { ?>selected="selected"<?php } ?>>PM</option>
</select>
</div>
