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
<div data-field-country data-max="<?php echo $params->get('max'); ?>" data-min="<?php echo $params->get('min'); ?>" data-select-type="<?php echo $params->get('select_type'); ?>"
	data-error-required="<?php echo JText::_('PLG_FIELDS_COUNTRY_VALIDATION_REQUIRED', true);?>"
	data-error-min="<?php echo JText::_('PLG_FIELDS_COUNTRY_VALIDATION_MINIMUM_ERROR', true);?>"
	data-error-max="<?php echo JText::_('PLG_FIELDS_COUNTRY_VALIDATION_MAXIMUM_ERROR', true);?>"
>
	<?php if ($params->get('select_type') === 'textboxlist') { ?>
	<div data-country-select-textboxlist class="textboxlist o-form-control">
		<?php if (!empty($selected)) { ?>
			<?php foreach($selected as $item) { ?>
			<div class="textboxlist-item" data-id="<?php echo $item->id; ?>" data-title="<?php echo $item->title; ?>" data-textboxlist-item>
				<span class="textboxlist-itemContent" data-textboxlist-itemContent><?php echo $item->title; ?><input type="hidden" name="<?php echo $inputName; ?>[]" value="<?php echo $item->id; ?>" /></span>
				<div class="textboxlist-itemRemoveButton" data-textboxlist-itemRemoveButton><i class="fa fa-times"></i></div>
			</div>
			<?php } ?>
		<?php } ?>

		<label for="<?php echo $inputName;?>-datetime" class="t-hidden">Datetime</label>
		<input id="<?php echo $inputName;?>-datetime" class="textboxlist-textField input-large" type="text" data-textboxlist-textfield data-country-textboxlist-input />
	</div>
	<?php } ?>

	<?php if ($params->get('select_type') === 'multilist') { ?>
	<select class="o-form-control" data-country-select-multilist multiple="multiple" size="<?php echo $params->get('multilist_size'); ?>" name="<?php echo $inputName; ?>[]">
		<?php foreach($countries as $country) { ?>
		<option value="<?php echo $country->id; ?>" <?php if (!empty($selected) && in_array($country->id, $selected)) { ?>selected="selected"<?php } ?>><?php echo $country->title; ?></option>
		<?php } ?>
	</select>
	<?php } ?>

	<?php if ($params->get('select_type') === 'checkbox') { ?>
	<div class="o-form-control" data-country-select-checkbox style="height: 200px; overflow-y: scroll;">
		<?php foreach($countries as $country) { ?>
		<label><input type="checkbox" value="<?php echo $country->id; ?>" name="<?php echo $inputName; ?>[]" <?php if (!empty($selected) && in_array($country->id, $selected)) { ?>checked="checked"<?php } ?> /> <?php echo $country->title; ?></label>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if ($params->get('select_type') === 'dropdown') { ?>
	<div class="o-select-group">
		<select class="o-form-control" data-country-select-dropdown name="<?php echo $inputName;?>[]" id="<?php echo $inputName;?>">
			<option value=""><?php echo JText::_('PLG_FIELDS_COUNTRY_SELECT_A_COUNTRY'); ?></option>
			<?php foreach ($countries as $country) { ?>
				<option value="<?php echo $country->id;?>" <?php echo !empty($selected) && in_array($country->id, $selected) ? ' selected="selected"' : '';?>><?php echo $country->title;?></option>
			<?php } ?>
		</select>
		<label for="<?php echo $inputName; ?>" class="o-select-group__drop"></label>
	</div>
	<?php } ?>

	<?php if ($params->get('show_max_message')) { ?>
	<div><?php echo JText::_('PLG_FIELDS_COUNTRY_LIMIT_MAX_SELECTION'); ?>: <?php echo $params->get('max'); ?></div>
	<?php } ?>

	<div class="es-fields-error-note" data-field-error></div>
</div>
