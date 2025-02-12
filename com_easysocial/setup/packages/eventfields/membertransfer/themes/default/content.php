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
<select class="o-form-control" name="member_transfer">
<?php foreach ($allowed as $a) { ?>
	<option value="<?php echo $a; ?>" <?php if ($value == $a) { ?>selected="selected"<?php } ?>><?php echo JText::_('FIELDS_EVENT_MEMBERTRANSFER_OPTION_' . strtoupper($a)); ?></option>
<?php } ?>
</select>
