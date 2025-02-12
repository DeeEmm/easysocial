<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-joomla_timezone data-error="<?php echo JText::_('PLG_FIELDS_JOOMLA_TIMEZONE_VALIDATION_SELECT_TIMEZONE', true);?>">
	<select name="<?php echo $inputName;?>" id="<?php echo $inputName;?>" class="o-form-control searchable"
			data-field-joomla_timezone-input
			data-placeholder="<?php echo JText::_('PLG_FIELDS_JOOMLA_TIMEZONE_SELECT_TIMEZONE'); ?>"
			class="<?php echo JFactory::getDocument()->getDirection() == 'rtl' ? 'chzn-rtl' : '';?>"
	>
		<option value="UTC" <?php if ($value === 'UTC' || !$value) { ?>selected="selected"<?php } ?>><?php echo JText::_('PLG_FIELDS_JOOMLA_TIMEZONE_USE_DEFAULT'); ?></option>
		<?php foreach( $timezones as $group => $countries ){ ?>
		<optgroup label="<?php echo $group;?>">
			<?php foreach( $countries as $country ){ ?>
			<option value="<?php echo $country; ?>" <?php echo $value === $country ? 'selected="selected"' : ''; ?>><?php echo $country;?></option>
			<?php } ?>
		</optgroup>
		<?php } ?>
	</select>
	<div class="es-fields-error-note" data-field-error></div>
</div>
