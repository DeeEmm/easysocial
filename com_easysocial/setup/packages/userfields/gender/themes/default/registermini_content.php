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
<div data-field-gender class="o-form-group t-text--center">
	<?php if ($options) { ?>
		<?php foreach($options as $key => $option) { ?>
			<label class="radio-inline t-lg-ml--lg">
				<input type="radio" value="<?php echo $option->value; ?>" id="<?php echo $inputName;?>" name="<?php echo $inputName;?>" data-field-gender-select />
				<?php echo $option->title; ?>

			</label>
		<?php } ?>
	<?php } ?>
	<div class="es-fields-error-note" data-field-error></div>
</div>
