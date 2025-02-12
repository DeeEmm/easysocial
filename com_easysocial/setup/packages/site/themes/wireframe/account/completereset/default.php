<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-social-signon" data-reset-password>
	<div class="es-social-signon__hd">
		<h1><?php echo JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_COMPLETE_RESET');?></h1>
		<p><?php echo JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_COMPLETE_RESET_DESC');?></p>
	</div>

	<form class="es-social-signon__form" action="<?php echo JRoute::_('index.php');?>" method="post">
		<div class="es-social-signon__form-inner" data-field-joomla_password
			data-error-empty="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_PASSWORD', true);?>"
			data-error-emptyconfirm="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_RECONFIRM_PASSWORD', true);?>"
			data-error-mismatch="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_NOT_MATCHING', true);?>"
			data-error-emptyoriginal="<?php echo JText::_('PLG_FIELDS_JOOMLA_PASSWORD_EMPTY_ORIGINAL_PASSWORD', true);?>"
			data-error-min="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_CHAR', $params->get('min', 4));?>"
			data-error-max="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MAXIMUM_CHAR', $params->get('max', 0));?>"
			data-error-mininteger="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_INTEGER', $params->get('min_integer', 0));?>"
			data-error-minsymbols="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_SYMBOLS', $params->get('min_symbols', 0));?>"
			data-error-minupper="<?php echo JText::sprintf('PLG_FIELDS_JOOMLA_PASSWORD_MINIMUM_UPPERCASE', $params->get('min_uppercase', 0));?>">
			<div class="o-form-group t-text--center">
				<label for="es-password"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_PASSWORD'); ?></label>
				<input type="password" name="es-password" id="es-password" class="o-form-control" data-es-password data-field-password-input />
			</div>

			<div class="o-form-group t-text--center">
				<label for="es-password2"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_RECONFIRM_PASSWORD'); ?></label>
				<input type="password" name="es-password2" id="es-password2" class="o-form-control" data-field-password-confirm />
			</div>

			<div class="es-fields-error-note" data-field-password-warning></div>

			<div>
				<button class="btn btn-es-primary btn-block" data-password-reset-submit><?php echo JText::_('COM_EASYSOCIAL_COMPLETE_RESET_PASSWORD_BUTTON');?></button>
			</div>
		</div>
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="account" />
		<input type="hidden" name="task" value="completeResetPassword" />
		<?php echo $this->html('form.token'); ?>
	</form>
</div>