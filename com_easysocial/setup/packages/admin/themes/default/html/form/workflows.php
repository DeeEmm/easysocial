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
	<select name="<?php echo $name;?>" id="<?php echo $id;?>" class="o-form-control">
		<option value=""><?php echo JText::_('COM_ES_SELECT_A_WORKFLOW');?></option>
		<?php foreach ($workflows as $workflow) { ?>
			<option value="<?php echo $workflow->id;?>"<?php echo $workflow->id == $selected ? ' selected="selected"' : '';?>><?php echo JText::_($workflow->title); ?></option>
		<?php } ?>
	</select>

	<label for="<?php echo $id; ?>" class="o-select-group__drop"></label>
</div>
