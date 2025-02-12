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
<?php if ($this->lib->config->get('users.profile.sidebar') != 'hidden') { ?>
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('widgets', 'user', 'profile', 'sidebarAboutTop', array($user), 'site/widgets/sidebar.wrapper'); ?>

		<?php echo $this->lib->render('module', 'es-profile-about-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php echo $this->lib->output('site/profile/about/stats', array('user' => $user)); ?>

		<?php echo $this->lib->render('module', 'es-profile-about-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php echo $this->lib->render('widgets', 'user', 'profile', 'sidebarAboutBottom', array($user), 'site/widgets/sidebar.wrapper'); ?>
	</div>
</div>
<?php } ?>
