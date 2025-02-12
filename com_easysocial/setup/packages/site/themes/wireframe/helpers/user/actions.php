<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($this->my->canBanUser($user) || $user->isViewer() || ES::reports()->canReport() || $this->my->canDeleteUser($user)) { ?>
<div class="o-btn-group o-btn-group--actions">
	<button type="button" class="dropdown-toggle_ btn btn-es-default-o btn-sm" data-es-toggle="dropdown">
		<i class="fa fa-ellipsis-h"></i>
	</button>

	<ul class="dropdown-menu dropdown-menu-right">
		<?php if ($canEdit) { ?>
			<li>
				<a href="<?php echo $editLink;?>">
					<?php echo JText::_('COM_EASYSOCIAL_PROFILE_UPDATE_PROFILE');?>
				</a>
			</li>
		<?php } ?>

		<?php if ($this->my->id && !$user->isViewer() && !$user->isSiteAdmin() && $this->config->get('users.blocking.enabled') ){ ?>
			<li>
				<?php echo ES::blocks()->getForm($user->id); ?>
			</li>
		<?php } ?>

		<?php if (ES::reports()->canReport() && !$user->isViewer()) { ?>
			<li>
				<?php echo ES::reports()->getForm('com_easysocial', SOCIAL_TYPE_USER, $user->id, $user->getName(), JText::_('COM_EASYSOCIAL_PROFILE_REPORT_USER'), '', JText::_('COM_EASYSOCIAL_PROFILE_REPORT_USER_DESC'), $user->getPermalink(true, true), false, $user->id); ?>
			</li>
		<?php } ?>

		<?php if ($this->my->canBanUser($user) && $this->my->canBanUser($user)) { ?>
			<li class="divider"></li>

			<?php if (!$user->isBanned()) { ?>
			<li>
				<a href="javascript:void(0);" data-es-user-ban data-id="<?php echo $user->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_BAN_THIS_USER');?></a>
			</li>
			<?php } else { ?>
			<li>
				<a href="javascript:void(0);" data-es-user-unban data-id="<?php echo $user->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_UNBAN_USER');?></a>
			</li>
			<?php } ?>
		<?php } ?>

		<?php if ($this->my->canDeleteUser($user)) { ?>
			<li class="divider"></li>
			<li>
				<a href="javascript:void(0);" data-es-user-delete data-id="<?php echo $user->id;?>">
					<?php echo JText::_('COM_EASYSOCIAL_PROFILE_DELETE_THIS_USER');?>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>
<?php } ?>
