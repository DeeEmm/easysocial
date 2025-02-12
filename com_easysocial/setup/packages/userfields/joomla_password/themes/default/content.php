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
	<ul class="input-vertical g-list-unstyled">
		<?php if ($showOriginalPassword) { ?>
		<li>
			<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_ENTER_ORIGINAL_PASSWORD_TO_CHANGE_PASSWORD'); ?>
		</li>
		<li>
			<label for="<?php echo $inputName; ?>-orig" name="<?php echo $inputName; ?>-orig" class="t-hidden"><?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_ENTER_PASSWORD');?></label>
			<input type="password" size="50" class="o-form-control" autocomplete="new-password" id="<?php echo $inputName; ?>-orig" name="<?php echo $inputName; ?>-orig" data-field-password-orig
				placeholder="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_ENTER_PASSWORD');?>" />
		</li>
		<li>
			<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_ENTER_NEW_PASSWORD'); ?>
		</li>
		<?php } ?>

		<li>
			<label for="<?php echo $inputName; ?>-input" class="t-hidden"><?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_ENTER_PASSWORD', true);?></label>
			<input type="password" name="<?php echo $inputName; ?>-input" id="<?php echo $inputName; ?>-input" class="o-form-control" autocomplete="new-password"
					value="<?php echo !empty($input) ? $this->html('string.escape', $input ) : ''; ?>"
					data-field-password-input placeholder="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_ENTER_PASSWORD', true);?>" />

			<span class="help-inline t-fs--sm" data-password-strength
				data-message-1="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_STRENGTH_VERY_WEAK', true);?>"
				data-message-2="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_STRENGTH_WEAK', true);?>"
				data-message-3="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_STRENGTH_NORMAL', true);?>"
				data-message-4="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_STRENGTH_STRONG', true);?>"
				data-message-5="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_STRENGTH_VERY_STRONG', true);?>"
			></span>
		</li>

		<?php if ($params->get('reconfirm_password', true)) { ?>
		<li>
			<label for="<?php echo $inputName; ?>-reconfirm" class="t-hidden"><?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_RECONFIRM_PASSWORD', true);?></label>
			<input type="password" class="o-form-control" name="<?php echo $inputName; ?>-reconfirm" id="<?php echo $inputName; ?>-reconfirm" class="form-control" autocomplete="new-password"
					value="<?php echo !empty($input) ? $this->html('string.escape', $input) : ''; ?>"
					data-field-password-confirm placeholder="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_RECONFIRM_PASSWORD', true);?>" />
		</li>
		<?php } ?>
	</ul>
	<div class="es-fields-error-note" data-field-error></div>
</div>
