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
<div class="o-select-group">
	<select name="<?php echo $name;?>" id="<?php echo $name;?>" class="o-form-control" <?php echo $attributes;?>>
		<?php foreach ($items as $key => $value) { ?>
		<option value="<?php echo $key;?>" <?php echo $key == $selected || (is_array($selected) && in_array($key, $selected)) ? ' selected="selected"' : '';?>><?php echo JText::_($value); ?></option>
		<?php } ?>
	</select>

	<label for="<?php echo $name;?>" class="o-select-group__drop"></label>
</div>
