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
<h1 class="t-text--center"><?php echo JText::_('COM_ES_REGISTRATION_COMPLETED_CONFIRMATION_EMAIL_ACCOUNT_HEADING');?></h1>

<div class="es-complete-wrap">
	<?php echo $this->html('avatar.mini', $user->getName(), '', $user->getAvatar(SOCIAL_AVATAR_MEDIUM), 'lg', '', '', false); ?>

	<form class="es-login-form es-verify-form" action="<?php echo JRoute::_('index.php');?>" method="post">
		<p class="t-text--center">
			<?php echo JText::_('COM_ES_REGISTRATION_COMPLETED_CONFIRMATION_EMAIL_ACCOUNT_DESCRIPTION'); ?>
		</p>
	</form>
</div>
