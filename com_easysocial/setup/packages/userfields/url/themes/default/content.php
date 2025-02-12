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
<div data-field-url data-error-empty="<?php echo JText::_('PLG_FIELDS_URL_VALIDATION_EMPTY_URL', true);?>">
	<input type="text" name="<?php echo $inputName;?>" id="<?php echo $inputName;?>" value="<?php echo $value; ?>"
		placeholder="<?php echo JText::_($params->get('placeholder'), true); ?>"
		class="o-form-control"
		data-field-url-input
	/>
	<div class="es-fields-error-note" data-field-error></div>
</div>
