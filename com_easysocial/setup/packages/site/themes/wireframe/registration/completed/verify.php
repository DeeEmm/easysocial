<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<h1 class="t-text--center"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_COMPLETED_ACTIVATE_ACCOUNT');?></h1>

<div class="es-complete-wrap">

	<?php if ($user) { ?>
	<?php echo $this->html('avatar.mini', $user->getName(), '', $user->getAvatar(SOCIAL_AVATAR_MEDIUM), 'lg', '', '', false); ?>
	<?php } ?>

	<p class="t-lg-mt--xl">
	<?php if ($user) { ?>
		<?php echo JText::_('COM_ES_REGISTRATION_COMPLETED_SENT_ACTIVATION_CODE');?>
	<?php } else { ?>
		<?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_ACTIVATION_PLEASE_PROVIDE_ACTIVATION_CODE'); ?>
	<?php } ?>
	</p>

	<form class="es-login-form es-verify-form" action="<?php echo JRoute::_('index.php');?>" method="post">
		<div class="o-form-group">
			<input type="text" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_REGISTRATION_ACTIVATION_CODE_PLACEHOLDER' );?>" name="token" class="o-form-control" />
		</div>

		<button class="btn btn-es-primary btn-login btn-large btn-block mt-10" type="submit">
			<?php echo JText::_('COM_EASYSOCIAL_ACTIVATE_ACCOUNT_BUTTON');?>
		</button>

		<input type="hidden" name="userid" value="<?php echo $user ? $user->id : '';?>" />
		<input type="hidden" name="option" value="com_easysocial" />
		<input type="hidden" name="controller" value="registration" />
		<input type="hidden" name="task" value="activate" />
	</form>
</div>
