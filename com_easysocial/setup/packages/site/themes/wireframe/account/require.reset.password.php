<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="es-remind es-remind-username mt-20">
	<div class="es-title">
		<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_REQUIRE_PASSWORD_RESET' ); ?>
	</div>
	<div class="es-desp small">
		<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_REQUIRE_PASSWORD_RESET_DESC' );?>
	</div>

	<div class="es-remind-form-wrap">
		<form name="requireResetPassword" method="post" action="<?php echo JRoute::_( 'index.php' );?>">

			<div class="o-input-group">
				<span class="o-input-group__addon"><?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_NEW_PASSWORD' );?></span>
				<input type="password" class="o-form-control" name="es-password" value="" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_NEW_PASSWORD_PLACEHOLDER' , true );?>" />
			</div>

			<div class="o-input-group">
				<span class="o-input-group__addon"><?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_NEW_PASSWORD_CONFIRM' );?></span>
				<input type="password" class="o-form-control" name="es-password2" value="" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_NEW_PASSWORD_CONFIRM_PLACEHOLDER' , true );?>" />
			</div>

			<hr />
			<button class="btn btn-es-primary btn-submit"><?php echo JText::_( 'COM_EASYSOCIAL_RESET_PASSWORD_BUTTON' ); ?></button>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="account" />
			<input type="hidden" name="task" value="completeRequireResetPassword" />
			<?php echo $this->html( 'form.token' ); ?>
		</form>
	</div>
</div>
