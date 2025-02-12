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
<div data-field-email 
	data-error-required="<?php echo JText::_('PLG_FIELDS_EMAIL_VALIDATION_REQUIRED', true);?>"
	data-error-invalid="<?php echo JText::_('PLG_FIELDS_EMAIL_VALIDATION_INVALID_FORMAT', true);?>"
>
	<input type="text" class="o-form-control" id="<?php echo $inputName;?>" name="<?php echo $inputName;?>"
		data-field-email-input
		value="<?php echo $value; ?>"
		autocomplete="off"
		placeholder="<?php echo JText::_( 'PLG_FIELDS_EMAIL_SAMPLE_EMAIL_ADDRESS' ); ?>" />

	<div class="es-fields-error-note" data-field-error></div>
</div>