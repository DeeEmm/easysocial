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
<div class="o-input-group">
	<span class="o-input-group__addon">
		<i class="fab fa-whatsapp"></i>
	</span>
	<input type="text" id="<?php echo $inputName;?>"
		value="<?php echo $value; ?>"
		name="<?php echo $inputName;?>"
		class="o-form-control"
		placeholder="<?php echo JText::_($params->get('placeholder'), true); ?>"
		<?php echo $params->get('readonly') ? 'disabled="disabled"' : '';?>
		<?php echo $params->get('required') ? 'data-check-required' : '';?>
	/>

	<div class="es-fields-error-note" data-field-error></div>
</div>
