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
<div class="o-input-group o-input-group--inline"
	data-field-currency
	data-error-required="<?php echo JText::_('PLG_FIELDS_PRICE_VALIDATION_INPUT_REQUIRED', true);?>"
	data-error-numeric="<?php echo JText::_('PLG_FIELDS_PRICE_VALIDATION_INPUT_NUMERIC', true);?>">

	<?php if ($this->config->get('marketplaces.multicurrency')) { ?>
		<div class="o-input-group__select">
			<div class="o-select-group">
				<select name="<?php echo $inputName; ?>[currency]" id="<?php echo $inputName; ?>[currency]" class="o-form-control">
					<?php foreach ($currencyLabel as $currencyOption) { ?>
						<option value="<?php echo $currencyOption['value']; ?>" <?php echo $currencyDefault == $currencyOption['value'] ? 'selected' : ''; ?>><?php echo Jtext::_($currencyOption['text']); ?></option>
					<?php } ?>
				</select>
				<label class="o-select-group__drop"></label>
			</div>
		</div>
	<?php } else { ?>
		<span class="o-input-group__addon"><?php echo $defaultCurrency->symbol; ?></span>
	<?php } ?>
	<input id="<?php echo $inputName; ?>[price]" type="text" class="o-form-control t-text--center" name="<?php echo $inputName; ?>[price]" value="<?php echo $price; ?>" data-price/>
</div>

<div class="es-fields-error-note" data-field-error></div>
