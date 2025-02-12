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
<div data-field-password data-min="<?php echo $params->get('min'); ?>" data-max="<?php echo $params->get('max'); ?>"
	data-error-required="<?php echo JText::_('PLG_FIELDS_PASSWORD_VALIDATION_INPUT_REQUIRED', true);?>"
	data-error-short="<?php echo JText::_('PLG_FIELDS_PASSWORD_VALIDATION_INPUT_TOO_SHORT', true);?>"
	data-error-long="<?php echo JText::_('PLG_FIELDS_PASSWORD_VALIDATION_INPUT_TOO_LONG', true);?>"
>
	<input type="password" id="<?php echo $inputName;?>"
		value="<?php echo $value; ?>"
		name="<?php echo $inputName;?>"
		class="o-form-control input-sm"
		autocomplete="new-password"
		placeholder="<?php echo JText::_($params->get('placeholder'), true); ?>"
		data-field-password-input
		<?php if ($params->get('required')) { ?>data-check-required<?php } ?>
	/>
</div>
