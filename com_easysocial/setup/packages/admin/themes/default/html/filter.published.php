<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<select class="o-form-control" name="<?php echo $name;?>" class="select" data-table-grid-filter>
	<option value="all"<?php echo !$selected ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_FILTER_SELECT_STATE');?></option>
	<option value="1"<?php echo $selected == '1' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_GRID_FILTER_STATE_PUBLISHED');?></option>
	<option value="0"<?php echo $selected == '0' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_GRID_FILTER_STATE_UNPUBLISHED');?></option>

	<?php if ($options) { ?>
		<?php foreach ($options as $option) { ?>
		<option value="<?php echo $option->value;?>" <?php echo $selected == $option->value ? ' selected="selected"' : '';?>><?php echo JText::_($option->title);?></option>
		<?php } ?>
	<?php } ?>
</select>
