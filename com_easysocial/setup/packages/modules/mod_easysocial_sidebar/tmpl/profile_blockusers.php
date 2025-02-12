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
		<?php echo $this->lib->render('module' , 'es-profile-edit-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_BLOCKED_USERS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item active" data-es-privacy-item>
						<a href="<?php echo ESR::profile(array('layout' => 'manageBlockedUsers'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_PRIVACY_MANAGE_BLOCKED_USERS');?></a>
					</li>
				</ul>
			</div>
		</div>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_OTHER_LINKS');?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE');?></a>
					</li>

					<?php if ($this->lib->my->canConfigureMFA() && $this->lib->my->hasCommunityAccess()) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'mfa'));?>" class="o-tabs__link"><?php echo JText::_('COM_ES_PROFILE_SIDEBAR_MFA_MANAGE');?></a>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('privacy.enabled') && $this->lib->my->hasCommunityAccess()) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'editPrivacy'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY');?></a>
					</li>
					<?php } ?>

					<?php if ($this->lib->my->hasCommunityAccess()) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'editNotifications'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_MANAGE_ALERTS');?></a>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('activity.logs.enabled')) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::activities(); ?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES'); ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php echo $this->lib->render('module', 'es-profile-editprivacy-sidebar-bottom'); ?>
	</div>
</div>
