<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

?>
<div data-field-joomla_email
	data-error-required="<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_REQUIRED', true);?>"
	data-error-reconfirmrequired="<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_RECONFIRM_REQUIRED', true);?>"
	data-error-mismatch="<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_NOT_MATCHING', true);?>"
>
	<ul class="input-vertical g-list-unstyled">
		<li>
			<label for="email" class="t-hidden">Email</label>
			<input type="text" size="30" class="o-form-control" id="email" name="email" value="<?php echo $email; ?>"
			data-check-required
			data-field-email-input
			autocomplete="off"
			placeholder="<?php echo JText::_( 'PLG_FIELDS_JOOMLA_EMAIL_SAMPLE_EMAIL_ADDRESS' ); ?>" />
		</li>

		<li class="<?php echo !$showConfirmation ? 't-hidden' : '';?>" data-field-email-reconfirm-frame>
			<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_RECONFIRM_EMAIL'); ?>
		</li>

		<li class="<?php echo !$showConfirmation ? 't-hidden' : '';?>" data-field-email-reconfirm-frame>
			<label for="email-reconfirm" class="t-hidden">Email Reconfirm</label>
			<input type="text" name="email-reconfirm" id="email-reconfirm" class="o-form-control" size="30" value="<?php echo $email; ?>"
				data-field-email-reconfirm-input autocomplete="off" placeholder="<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_SAMPLE_EMAIL_ADDRESS'); ?>" />
		</li>
	</ul>
	<div class="es-fields-error-note" data-field-error></div>
</div>
