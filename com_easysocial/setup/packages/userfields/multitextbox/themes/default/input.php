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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<li data-field-multitextbox-item class="data-field-multitextbox-item">
	<div class="o-media">
		<div class="o-media__image">
			<span class="item-move" data-field-multitextbox-move><i class="fa fa-bars"></i></span>
		</div>
		<div class="o-media__body">
			<div class="o-input-group">
				<label for="<?php echo $inputName; ?>[]" class="t-hidden">Multitextbox</label>
				<input id="<?php echo $inputName; ?>[]" type="text" class="o-form-control" data-field-multitextbox-input name="<?php echo $inputName; ?>[]" value="<?php echo $value; ?>" placeholder="<?php echo JText::_($placeholder); ?>" />
				<span class="o-input-group__btn">
					<button class="btn btn-es-default-o" type="button" data-field-multitextbox-delete>×</button>
				</span>
			</div>
		</div>
	</div>
</li>
