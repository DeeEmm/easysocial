<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<li class="data-field-multitextbox-item" data-polls-option data-id="<?php echo $item ? $item->id : '';?>">
	<div class="o-input-group">
		<input type="text" class="o-form-control"
			data-polls-option-input
			name="pollItems[]"
			value="<?php echo $item ? ES::string()->escape($item->value) : '';?>"
			placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_ENTER_POLL_ITEM');?>"
		/>
		<span class="o-input-group__btn" data-polls-delete-btn>
			<button class="btn btn-es-default-o" type="button" data-polls-item-delete="">×</button>
		</span>
	</div>
</li>
