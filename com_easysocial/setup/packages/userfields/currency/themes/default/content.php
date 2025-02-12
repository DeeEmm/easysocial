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
<div data-field-currency data-error-required="<?php echo JText::_('PLG_FIELDS_CURRENCY_VALIDATION_INPUT_REQUIRED', true);?>">
	<div class="o-grid">
		<div class="o-grid__cell t-lg-pr--md">
			<div class="o-input-group">
				<label for="<?php echo $inputName; ?>[dollar]" class="t-hidden">Dollars</label>
				<input id="<?php echo $inputName; ?>[dollar]" data-currency-dollar type="text" class="o-form-control" name="<?php echo $inputName; ?>[dollar]" value="<?php echo $dollar; ?>" />
				<span class="o-input-group__addon"><?php echo $dollarsLabel;?></span>
			</div>
		</div>

		<div class="o-grid__cell">
			<div class="o-input-group">
				<label for="<?php echo $inputName; ?>[cent]" class="t-hidden">Cents</label>
				<input id="<?php echo $inputName; ?>[cent]" data-currency-cent type="text" class="o-form-control" name="<?php echo $inputName; ?>[cent]" value="<?php echo $cent; ?>" />
				<span class="o-input-group__addon"><?php echo $centsLabel; ?></span>
			</div>
		</div>
	</div>

	<div class="es-fields-error-note" data-field-error></div>
</div>
