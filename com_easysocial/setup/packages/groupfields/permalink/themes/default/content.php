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
<div data-field-permalink data-max="<?php echo $params->get('max'); ?>"
	data-error-max="<?php echo JText::_('PLG_FIELDS_GROUP_PERMALINK_EXCEEDED_MAX_LENGTH', true);?>"
	data-error-required="<?php echo JText::_('PLG_FIELDS_GROUP_PERMALINK_REQUIRED', true);?>"
>
	<div class="o-input-group">
		<input type="text" name="<?php echo $inputName;?>" id="<?php echo $element;?>" class="o-form-control validation keyup length-4 required" value="<?php echo $value; ?>"
			autocomplete="off"
			data-permalink-input
			placeholder="<?php echo JText::_('PLG_FIELDS_GROUP_PERMALINK_PLACEHOLDER'); ?>" />

		<?php if ($params->get('check_permalink', true)) { ?>
		<span class="o-input-group__btn">
			<button type="button" class="btn btn-sm btn-es-default" data-permalink-check><?php echo JText::_('PLG_FIELDS_GROUP_PERMALINK_CHECK_BUTTON');?></button>
		</span>
		<?php } ?>
	</div>

	<div class="controls" style="margin:0;">
		<span class="help-block" data-permalink-available style="display: none;">
			<span class="t-text--success"><?php echo JText::_( 'PLG_FIELDS_GROUP_PERMALINK_AVAILABLE' );?></span>
		</span>
	</div>
	<div class="es-fields-error-note" data-field-error></div>
</div>
