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
<div data-photo-item
	 data-photo-edit="<?php echo isset($isEdit) ? $isEdit : '0'; ?>"
	 class="es-embed-container">
	<div class="es-embed-container__action" data-photo-remove-button>
		<a href="javascript:void(0);" class="es-embed-container__remove" title="Remove">x</a>
	</div>
	<div class="embed-responsive embed-responsive-16by9">
		<img data-photo-image src="<?php echo $uri; ?>" alt="" class="embed-responsive-item">
	</div>

	<input type="hidden" name="<?php echo $inputName; ?>[source][]" data-field-photo-source value="<?php echo $uri; ?>" />
	<input type="hidden" name="<?php echo $inputName; ?>[path][]" data-field-photo-path value="<?php echo $path; ?>" />
</div>
