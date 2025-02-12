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
<form method="post" action="<?php echo JRoute::_('index.php');?>" data-oauth-profile>
	<div class="es-registration">
		<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_REGISTRATIONS_SELECT_PROFILE_TYPE_TITLE', 'h1'); ?>

		<?php if ($profiles) { ?>
		<ul class="list-profiles g-list-unstyled">
			<?php foreach ($profiles as $profile) { ?>
				<?php echo $this->loadTemplate('site/oauth/profile/items', array('profile' => $profile, 'oauth' => true)); ?>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<div>
			<?php echo JText::_('COM_EASYSOCIAL_REGISTRATIONS_NO_PROFILES_CREATED_YET'); ?>
		</div>
		<?php } ?>
	</div>

	<?php echo $this->html('form.token'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="registration" />
	<input type="hidden" name="profile" value="" data-oauth-profile-id />
	<input type="hidden" name="layout" value="oauthPreferences" />
	<input type="hidden" name="client" value="<?php echo $clientType;?>" />
	<input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>" />
</form>
