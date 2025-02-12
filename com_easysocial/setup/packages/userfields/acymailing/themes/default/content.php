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
<div data-field-acymailing>
	<?php if ($optionType == 'checkbox') { ?>
		<label class="checkbox" for="<?php echo $inputName;?>">
			<input type="checkbox" name="<?php echo $inputName;?>" id="<?php echo $inputName;?>" value="1" <?php echo $value ? ' checked="checked"' :'';?> />
			<?php echo JText::_('PLG_FIELDS_MAILCHIMP_SUBSCRIBE_TO_NEWSLETTER'); ?>
		</label>
	<?php } ?>

	<?php if ($optionType == 'toggler') { ?>
		<div class="o-radio o-radio--inline">
			<input type="radio" value="1" name="<?php echo $inputName; ?>" id="acymailing-yes" <?php echo $value === 1 ? 'checked="checked"' : '';?> />
			<label for="acymailing-yes"><?php echo JText::_('PLG_FIELDS_MAILCHIMP_SUBSCRIBE_TO_YES'); ?></label>
		</div>

		<div class="o-radio o-radio--inline">
			<input type="radio" value="0" name="<?php echo $inputName; ?>" id="acymailing-no" <?php echo $value === 0 ? 'checked="checked"' : '';?> />
			<label for="acymailing-no"><?php echo JText::_('PLG_FIELDS_MAILCHIMP_SUBSCRIBE_TO_NO'); ?></label>
		</div>
	<?php } ?>

	<div class="es-fields-error-note" data-field-error></div>
</div>