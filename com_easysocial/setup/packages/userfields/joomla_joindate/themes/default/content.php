<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-joomla_joindate>
    <ul class="input-vertical g-list-unstyled">
        <li>
            <input type="text" size="30" class="o-form-control" readonly="readonly" id="joindate" name="joindate" value="<?php echo $joindate; ?>"
            data-check-required
            autocompleted="off"
            placeholder="<?php echo JText::_('PLG_FIELDS_JOOMLA_JOINDATE_SAMPLE_YOUR_DATE'); ?>" />
        </li>
    </ul>
</div>
