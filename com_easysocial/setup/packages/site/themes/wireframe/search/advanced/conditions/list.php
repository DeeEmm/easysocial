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
<select class="o-form-control" name="conditions[]" data-condition style="min-width:130px">
	<?php foreach($list as $item) { ?>
	<option value="<?php echo $item->value; ?>" <?php echo $selected == $item->value ? ' selected="selected"' : ''; ?>><?php echo JText::_($item->title); ?></option>
	<?php } ?>
</select>