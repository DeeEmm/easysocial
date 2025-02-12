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
<div class="es-registration">
	<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_PROFILE_SWITCH_SELECT_PROFILE_TYPE_TITLE', 'div'); ?>

	<p><?php echo JText::_('COM_EASYSOCIAL_PROFILE_SWITCH_SELECT_PROFILE_TYPE_INTO'); ?></p>

	<?php if ($profiles) { ?>
	<ul class="list-profiles g-list-unstyled">
		<?php foreach ($profiles as $profile) { ?>
			<?php echo $this->loadTemplate('site/profile/switch/items', array('profile' => $profile)); ?>
		<?php } ?>
	</ul>
	<?php } ?>

	<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_REGISTRATIONS_NO_PROFILES_CREATED_YET', 'fa-users'); ?>
</div>
