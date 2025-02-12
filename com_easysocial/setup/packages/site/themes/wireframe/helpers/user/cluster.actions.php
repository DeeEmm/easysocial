<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-btn-group">
	<button data-es-toggle="dropdown" class="btn btn-es-default-o btn-sm dropdown-toggle_" type="button">
		 <i class="fa fa-ellipsis-h"></i>
	</button>

	<ul class="dropdown-menu dropdown-menu-right">
		<?php if ($cluster->isPendingInvitationApproval($user->id)) { ?>
		<li data-cancel>
			<a href="javascript:void(0);"><?php echo JText::_('APP_CLUSTER_MEMBERS_CANCEL_INVITATION'); ?></a>
		</li>
		<?php } ?>

		<?php if ($cluster->isPendingMember($user->id)) { ?>
		<li data-approve>
			<a href="javascript:void(0);"><?php echo JText::_('APP_CLUSTER_MEMBERS_APPROVE'); ?></a>
		</li>
		<li data-reject>
			<a href="javascript:void(0);"><?php echo JText::_('APP_CLUSTER_MEMBERS_REJECT'); ?></a>
		</li>
		<?php } ?>

		<?php if ($cluster->isAdmin($user->id) && ($cluster->isOwner() || $this->my->isSiteAdmin())) { ?>
		<li data-revoke>
			<a href="javascript:void(0);"><?php echo JText::_('APP_CLUSTER_MEMBERS_REVOKE_ADMIN');?></a>
		</li>
		<?php } ?>

		<?php if (!$cluster->isAdmin($user->id) && $cluster->isMember($user->id)) { ?>
		<li data-promote>
			<a href="javascript:void(0);"><?php echo JText::_('APP_CLUSTER_MEMBERS_MAKE_ADMIN');?></a>
		</li>
		<?php } ?>

		<?php if ($cluster->isMember($user->id) && !$cluster->isOwner($user->id)) { ?>
		<li data-remove>
			<a href="javascript:void(0);"><?php echo JText::_('APP_CLUSTER_MEMBERS_REMOVE_FROM_' . strtoupper($cluster->cluster_type));?></a>
		</li>
		<?php } ?>
	</ul>
 </div>
