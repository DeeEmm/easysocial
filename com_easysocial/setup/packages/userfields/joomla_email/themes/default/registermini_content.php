<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
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
			<?php echo $this->html('form.floatinglabel', 'COM_ES_EMAIL_ADDRESS', 'email', 'email', '', 'email', true, 'data-check-required data-field-email-input autocomplete="off"'); ?>
		</li>
		
		<li class="<?php echo !$showConfirmation ? 't-hidden' : ' t-lg-mt--xl';?>" data-field-email-reconfirm-frame>
			<?php echo $this->html('form.floatinglabel', 'COM_ES_EMAIL_ADDRESS_RECONFIRM', 'email-reconfirm', 'email', '', 'email-reconfirm', true, 'data-field-email-reconfirm-input autocomplete="off"'); ?>
		</li>
	</ul>
	<div class="es-fields-error-note" data-field-error></div>
</div>
