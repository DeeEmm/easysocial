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
<div data-field-autocomplete data-error-required="<?php echo JText::_('PLG_FIELDS_USER_AUTOCOMPLETE_PLEASE_SELECT_SOME_VALUES', true);?>">

	<div class="textboxlist disabled" data-field-suggest>
		<?php if ($selected) { ?>
			<?php foreach ($selected as $item) { ?>
				<div class="textboxlist-item" data-textboxlist-item
					data-id="<?php echo $item->id;?>"
					data-title="<?php echo $item->title;?>"
					data-value="<?php echo $item->value; ?>"
				>
					<span class="textboxlist-itemContent" data-textboxlist-itemcontent>
						<?php echo $item->title;?>
						<input type="hidden" name="<?php echo $inputName; ?>[]" value="<?php echo $item->id;?>" />
					</span>
					<div class="textboxlist-itemRemoveButton" data-textboxlist-itemremovebutton>×</div>
				</div>
			<?php } ?>
		<?php } ?>

		<label for="<?php echo $inputName; ?>-autocomplete" class="t-hidden">Autocomplete</label>
		<input id="<?php echo $inputName; ?>-autocomplete" type="text" autocomplete="off" disabled class="textboxlist-textField o-form-control" data-textboxlist-textField placeholder="<?php echo JText::_($params->get('placeholder', ''));?>" />

	</div>
	<div class="es-fields-error-note" data-field-error></div>
</div>
