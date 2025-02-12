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
<select name="<?php echo $name;?>" default="<?php echo $default;?>">
	<option value=""><?php echo JText::_('COM_ES_MENU_NO_PRESELECT_FILTER');?></option>
	<?php if ($filters) { ?>
		<?php foreach ($filters as $filter) { ?>
			<option value="<?php echo $filter->id; ?>" <?php echo $value == $filter->id ? 'selected="selected"' : '';?>><?php echo $filter->title;?></option>
		<?php } ?>
	<?php } ?>
</select>
