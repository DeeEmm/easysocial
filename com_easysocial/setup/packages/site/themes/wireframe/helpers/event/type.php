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
<?php if ($cluster) { ?>
	<span>
		<a href="<?php echo $cluster->getPermalink(); ?>" data-original-title="<?php echo $cluster->getTitle(); ?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
			<?php if ($showIcon) { ?>
				<i class="fa fa-users"></i>&nbsp;&nbsp;
			<?php } ?>

			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_' . $cluster->getType() . '_EVENT'); ?>
		</a>
	</span>
<?php } else { ?>
	<?php if ($event->isOpen()) { ?>
	<span data-original-title="<?php echo JText::_('COM_ES_EVENTS_PUBLIC_EVENT_TOOLTIP', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
		<?php if ($showIcon) { ?>
			<i class="fa fa-globe-americas"></i>&nbsp;&nbsp;
		<?php } ?>

		<?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?>
	</span>
	<?php } ?>

	<?php if ($event->isPrivate()) { ?>
	<span data-original-title="<?php echo JText::_('COM_EASYSOCIAL_EVENTS_PRIVATE_EVENT_TOOLTIP', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
		<?php if ($showIcon) { ?>
			<i class="fa fa-lock"></i>&nbsp;&nbsp;
		<?php } ?>

		<?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?>
	</span>
	<?php } ?>

	<?php if ($event->isInviteOnly()) { ?>
	<span data-original-title="<?php echo JText::_('COM_EASYSOCIAL_EVENTS_INVITE_EVENT_TOOLTIP', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
		<?php if ($showIcon) {?>
			<i class="fa fa-envelope"></i>&nbsp;&nbsp;
		<?php } ?>

		<?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?>
	</span>
	<?php } ?>
<?php } ?>