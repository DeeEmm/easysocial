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
<span class="es-avatar es-avatar-md es-avatar-rounded">
	<img src="<?php echo $this->my->getAvatar( SOCIAL_AVATAR_MEDIUM );?>" alt="<?php echo $this->html( 'string.escape' , $this->my->getName() );?>" />
</span>
<div class="h4">
	<?php echo JText::_( 'COM_EASYSOCIAL_REGISTRATIONS_LOGIN_TO_YOUR_ACCOUNT' );?>
</div>

<form class="es-login-form mt-10" action="<?php echo JRoute::_( 'index.php' );?>" method="post" id="loginbox" name="loginbox">

	<fieldset class="">

		<?php if ($this->config->get('registrations.emailasusername')) { ?>
			<input type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_LOGIN_EMAIL_PLACEHOLDER');?>" name="username" id="userIdentity" class="full-width">
		<?php } else { ?>
			<input type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_PLACEHOLDER_YOUR_USERNAME');?>" name="username" id="userIdentity" class="full-width">
		<?php } ?>

		<input type="password" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_PLACEHOLDER_YOUR_PASSWORD' );?>" name="password" id="userPassword" class="full-width">

		<label class="checkbox">
			<input type="checkbox">
			<span name="remember"><?php echo JText::_( 'COM_EASYSOCIAL_LOGIN_REMEMBER_YOU' );?></span>
		</label>

		<button class="mt-20 btn btn-es-success btn-login btn-large btn-block" type="submit"><?php echo JText::_( 'COM_EASYSOCIAL_LOG_ME_IN_BUTTON' );?></button>
	</fieldset>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo base64_encode( FRoute::dashboard( array() , false ) ); ?>" />
	<input type="hidden" name="<?php echo ES::token();?>" value="1" />
</form>
