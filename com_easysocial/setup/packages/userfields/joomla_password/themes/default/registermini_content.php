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
<div data-field-joomla_password
		data-error-empty="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_PASSWORD', true);?>"
		data-error-emptyconfirm="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_RECONFIRM_PASSWORD', true);?>"
		data-error-mismatch="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_NOT_MATCHING', true);?>"
		data-error-emptyoriginal="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_ORIGINAL_PASSWORD', true);?>"
		data-error-min="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_CHAR', $params->get('min', 4));?>"
		data-error-max="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MAXIMUM_CHAR', $params->get('max', 0));?>"
		data-error-mininteger="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_INTEGER', $params->get('min_integer', 0));?>"
		data-error-minsymbols="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_SYMBOLS', $params->get('min_symbols', 0));?>"
		data-error-minupper="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_UPPERCASE', $params->get('min_uppercase', 0));?>"
>
	<?php echo $this->html('form.floatinglabel', 'PLG_FIELDS_JOOMLA_PASSWORD_ENTER_PASSWORD', $inputName . '-input', 'password', '', $inputName . '-input', true, 'data-password'); ?>

	<?php if ($params->get('mini_reconfirm_password', true)) { ?>
	<br />
	<?php echo $this->html('form.floatinglabel', 'PLG_FIELDS_JOOMLA_PASSWORD_RECONFIRM_PASSWORD', $inputName . '-reconfirm', 'password', !empty($input) ? $this->html('string.escape', $input) : '', $inputName . '-reconfirm', true, 'data-field-password-confirm'); ?>
	<?php } ?>

	<div class="es-fields-error-note" data-field-error></div>
</div>
