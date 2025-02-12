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
<li>
	<a href="<?php echo ESR::groups(array('layout' => 'edit', 'id' => $group->getAlias()));?>">
		<?php echo ($group->isDraft()) ? JText::_('COM_EASYSOCIAL_GROUPS_REVIEW_GROUP') : JText::_('COM_EASYSOCIAL_GROUPS_EDIT_GROUP');?>
	</a>
</li>

<?php if ($this->my->isSiteAdmin() && $group->isFeatured() && !$group->isDraft()) { ?>
<li>
	<a href="javascript:void(0);" data-es-groups-unfeature data-id="<?php echo $group->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_REMOVE_FEATURED');?></a>
</li>
<?php } ?>

<?php if ($this->my->isSiteAdmin() && !$group->isFeatured() && !$group->isDraft()) { ?>
<li>
	<a href="javascript:void(0);" data-es-groups-feature data-id="<?php echo $group->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_SET_FEATURED');?></a>
</li>
<?php } ?>

<?php if (($this->my->isSiteAdmin() || $group->isOwner() || $group->isAdmin()) && !$group->isDraft()) { ?>
	<li class="divider"></li>
	<?php echo $this->render('widgets', 'group', 'groups', 'groupAdminStart', array($group)); ?>
	<?php echo $this->render('widgets', 'group', 'groups', 'groupAdminEnd' , array($group)); ?>
<?php } ?>

<?php if (!$group->isDraft()) { ?>

	<li class="divider"></li>
	<li>
		<?php if($group->canUnpublish() && $group->isPublished()) { ?>
			<a href="javascript:void(0);" data-es-groups-unpublish data-id="<?php echo $group->id;?>"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_UNPUBLISH_GROUP');?></a>
		<?php } else if ($group->canPublish()){ ?>
			<a href="javascript:void(0);" data-es-groups-publish data-id="<?php echo $group->id;?>"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_PUBLISH_GROUP');?></a>
		<?php } ?>
	</li>

	<?php if ($group->canDelete()){ ?>
		<li>
			<a href="javascript:void(0);" data-es-groups-delete data-id="<?php echo $group->id;?>"><?php echo JText::_('COM_EASYSOCIAL_GROUPS_DELETE_GROUP');?></a>
		</li>
	<?php } ?>
<?php } ?>
