<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-input-group mb-10" data-copy-wrapper>
	<input type="text" data-copy="<?php echo $field->name;?>"
		class="<?php echo isset($field->class) ? $field->class : '';?> o-form-control"
		value="<?php echo isset($field->prefix) && $field->prefix == 'baseUrl' ? rtrim(JURI::root(), '/') . '/' : '';?><?php echo $field->default;?>"
	/>

	<span class="o-input-group__btn"
		data-copy
		data-original-title="<?php echo JText::_('COM_ES_COPY_TOOLTIP')?>"
		data-placement="left"
		data-es-provide="tooltip"
	>
		<a href="javascript:void(0);" class="btn btn-es-default-o">
			<i class="fa fa-copy"></i>
		</a>
	</span>
</div>
