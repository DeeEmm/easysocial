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
	<select name="<?php echo $name;?>" id="<?php echo empty($id) ? $name : $id; ?>" class="o-form-control" <?php echo $attributes; ?> autocomplete="off">
		<?php if ($inherit) { ?>
			<option value="inherit" <?php echo $value == 'inherit' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_EVENT_DESCRIPTIONS_EDITOR_INHERIT'); ?></option>
		<?php } ?>
		<?php foreach( $editors as $editor ){ ?>
			<option value="<?php echo $editor->value; ?>"<?php echo $editor->value == $value ? ' selected="selected"' : '';?>><?php echo $editor->text; ?></option>
		<?php } ?>
	</select>

	<label for="<?php echo $id;?>" class="o-select-group__drop"></label>
</div>
