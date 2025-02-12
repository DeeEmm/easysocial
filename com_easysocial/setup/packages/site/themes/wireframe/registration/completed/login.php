<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<h1 class="t-text--center"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATIONS_LOGIN_TO_YOUR_ACCOUNT');?></h1>
<div class="t-text--center"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATIONS_ACCOUNT_CREATED_LOGIN_TO_ACCOUNT');?></div>

<div class="es-complete-wrap">
	<?php echo $this->html('avatar.mini', $this->my->getName(), '', $this->my->getAvatar(SOCIAL_AVATAR_MEDIUM), 'lg', '', '', false); ?>

	<form class="es-login-form es-verify-form" action="<?php echo JRoute::_('index.php');?>" method="post" id="loginbox" name="loginbox">

		<div class="o-form-group">
			<input type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_PLACEHOLDER_YOUR_USERNAME', true);?>" name="username" id="userIdentity" class="o-form-control" />
		</div>

		<div class="o-form-group">
			<input type="password" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_PLACEHOLDER_YOUR_PASSWORD' );?>" name="password" id="userPassword" class="o-form-control" />
		</div>

		<div class="o-checkbox">
			<input type="checkbox" name="remember" id="login-remember">
			<label for="login-remember"><?php echo JText::_( 'COM_EASYSOCIAL_LOGIN_REMEMBER_YOU' );?></label>
		</div>

		<button class="btn btn-es-primary btn-login btn-large btn-block" type="submit"><?php echo JText::_('COM_EASYSOCIAL_LOG_ME_IN_BUTTON');?></button>

		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="account" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $returnUrl; ?>" />
		<input type="hidden" name="<?php echo ES::token();?>" value="1" />
	</form>
</div>
