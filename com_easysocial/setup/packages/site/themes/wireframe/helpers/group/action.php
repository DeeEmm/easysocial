<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($group->isInvited() && !$group->isMember()) { ?>
<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-es-groups-respond-invitation data-id=<?php echo $group->id;?> data-page-reload="<?php echo $forceReload; ?>">
	<?php echo JText::_('COM_ES_RESPOND');?>
</a>
<?php } ?>

<?php if ($this->my->getAccess()->get('groups.allow.join') && !$group->isInviteOnly() && !$group->isMember() && !$group->isPendingMember() && !$group->isInvited()) { ?>
<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-es-groups-join data-id="<?php echo $group->id;?>" data-page-reload="<?php echo $forceReload; ?>">
	<?php echo JText::_('COM_ES_JOIN');?>
</a>
<?php } ?>

<?php if ($group->isMember()) { ?>
<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-es-groups-leave data-id="<?php echo $group->id;?>">
	<?php echo JText::_('COM_ES_LEAVE');?>
</a>
<?php } ?>

<?php if (!$group->isMember() && $group->isPendingMember()) { ?>
<div data-request-sent class="o-btn-group">
	<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
		<?php echo JText::_('COM_EASYSOCIAL_REQUEST_SENT');?> &nbsp;<i class="fa fa-caret-down"></i>
	</button>

	<ul class="dropdown-menu">
		<li data-es-groups-withdraw data-id="<?php echo $group->id;?>">
			<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_WITHDRAW_REQUEST'); ?></a>
		</li>
	</ul>
</div>
<?php } ?>
