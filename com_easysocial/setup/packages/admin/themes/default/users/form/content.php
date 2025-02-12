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
<div class="wrapper accordion">
	<div class="tab-box tab-box-alt">
		<div class="tabbable">

			<?php if (isset($user)) { ?>
			<ul id="userForm" class="nav nav-tabs nav-tabs-icons" data-es-form-tabs>
				<li class="tabItem <?php echo $active == 'profile' ? 'active' : '';?>" data-item="profile">
					<a href="#profile" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_PROFILE');?></a>
				</li>
				<li class="tabItem <?php echo $active == 'usergroup' ? 'active' : '';?>" data-item="usergroup">
					<a href="#usergroup" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_USERGROUP');?></a>
				</li>
				<li class="tabItem <?php echo $active == 'badges' ? 'active' : '';?>" data-item="badges">
					<a href="#badges" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_ACHIEVEMENTS');?></a>
				</li>
				<li class="tabItem <?php echo $active == 'points' ? 'active' : '';?>" data-item="points">
					<a href="#points" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_POINTS');?></a>
				</li>
				<li class="tabItem <?php echo $active == 'notifications' ? 'active' : '';?>" data-item="notifications">
					<a href="#notifications" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_NOTIFICATIONS');?></a>
				</li>
				<li class="tabItem <?php echo $active == 'privacy' ? 'active' : '';?>" data-item="privacy">
					<a href="#privacy" data-es-toggle="tab"><?php echo JText::_('COM_EASYSOCIAL_USERS_PRIVACY');?></a>
				</li>
			</ul>
			<?php } ?>

			<div class="tab-content tab-content-side">

				<div id="profile" class="tab-pane <?php echo $active == 'profile' ? 'active' : '';?>" data-tabcontent data-for="profile">
					<?php echo $this->includeTemplate('admin/users/form/profile'); ?>
				</div>

				<?php if (isset($user)) { ?>
				<div id="badges" class="tab-pane <?php echo $active == 'badges' ? 'active' : '';?>" data-tabcontent data-for="badges">
					<?php echo $this->includeTemplate('admin/users/form/badges'); ?>
				</div>

				<div id="points" class="tab-pane <?php echo $active == 'points' ? 'active' : '';?>" data-tabcontent data-for="points">
					<?php echo $this->includeTemplate('admin/users/form/points'); ?>
				</div>

				<div id="notifications" class="tab-pane <?php echo $active == 'notifications' ? 'active' : '';?>" data-tabcontent data-for="notifications">
					<?php echo $this->includeTemplate('admin/users/form/notifications'); ?>
				</div>

				<div id="privacy" class="tab-pane <?php echo $active == 'privacy' ? 'active' : '';?>" data-tabcontent data-for="privacy">
					<?php echo $this->includeTemplate('admin/users/form/privacy'); ?>
				</div>

				<div id="usergroup" class="tab-pane <?php echo $active == 'usergroup' ? 'active' : '';?>" data-tabcontent data-for="usergroup">
					<?php echo $this->includeTemplate('admin/users/form/usergroups'); ?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>

</div>
