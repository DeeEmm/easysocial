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
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>

		<div data-dashboardSidebar-menu data-type="profile" data-id="<?php echo $profile->id;?>" class="active"></div>

		<?php echo $this->lib->render('module', 'es-profiles-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php echo ES::themes()->includeTemplate('site/profiles/default/about', array('profile' => $profile, 'randomMembers' => $randomMembers, 'albums' => $albums)); ?>

		<?php echo $this->lib->render('module', 'es-profiles-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>

	</div>
</div>
