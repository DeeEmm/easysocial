<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<div class="es-profile userProfile" data-id="<?php echo $user->id;?>" data-es-profile>

	<?php echo $this->render('widgets', 'user', 'profile', 'aboveHeader', array($user)); ?>

	<?php echo $this->render('module', 'es-profile-about-before-header'); ?>

	<?php if ($this->my->isSiteAdmin() && $user->isBlock()) { ?>
	<div class="es-user-banned alert alert-danger">
		<?php echo JText::_('COM_EASYSOCIAL_PROFILE_USER_IS_BANNED');?>
	</div>
	<?php } ?>

	<?php echo $this->html('cover.user', $user, 'about'); ?>

	<?php echo $this->render('module', 'es-profile-about-after-header'); ?>

	<div class="es-container <?php echo $this->config->get('users.profile.sidebar') == 'right' ? 'es-sidebar-right' : '';?>" data-es-container>

		<?php if ($this->isMobile() && $this->config->get('users.profile.sidebar') != 'hidden') { ?>
			<?php echo $this->output('site/profile/about/mobile'); ?>
		<?php } ?>

		<?php echo $this->html('html.sidebar'); ?>

		<div class="es-content">
			<div class="es-profile-info">
				<?php echo $this->output('site/fields/about/default', array('steps' => $steps, 'canEdit' => $canEdit, 'routerType' => 'profile', 'objectId' => 1, 'item' => $user)); ?>
			</div>

			<?php echo $this->render('module', 'es-profile-about-after-contents'); ?>
		</div>
	</div>
</div>
