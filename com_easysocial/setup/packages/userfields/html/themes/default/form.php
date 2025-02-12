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
<div class="o-form-group">
	<label class="o-control-label">
		<?php echo JText::_('COM_ES_CUSTOM_HTML');?>:
	</label>
	<div class="o-control-input data" data-content>
		<div class="data-field-textarea" data-field-textarea>
			<textarea name="<?php echo $inputName; ?>" id="<?php echo $inputName; ?>" class="o-form-control" data-field-textarea-input value="<?php echo $value; ?>"><?php echo $value; ?></textarea>
		</div>
	</div>
</div>
