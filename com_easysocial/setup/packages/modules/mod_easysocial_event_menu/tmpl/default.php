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
<div id="es" class="mod-es mod-es-menu mod-es-event-menu <?php echo $lib->getSuffix();?>">

	<?php if ($params->get('show_edit', true) && $event->isAdmin()) { ?>
	<div class="mod-es-menu-bar">
		<nav class="o-nav">
			<div class="o-nav__item pull-right">
				<a href="<?php echo $event->getPermalink(true, false, 'edit');?>" class="o-nav__link mod-es-menu-bar__icon-link has-new">
					<i class="fa fa-edit"></i>
				</a>
			</div>
		</nav>
	</div>
	<?php } ?>
	<div class="mod-es-pf-hd">
		<div class="mod-es-pf-hd__cover-wrap">
			<div style="background-image : url(<?php echo $cover->getSource();?>);background-position: <?php echo $cover->getPosition();?>;" class="mod-es-pf-hd__cover">
			</div>
		</div>
		<div class="mod-es-pf-hd__content">
			<?php if ($params->get('show_avatar', true)) { ?>
			<div class="mod-es-pf-hd__avatar">
				<?php echo ES::template()->html('avatar.mini', $event->getName(), $event->getPermalink(), $event->getAvatar(SOCIAL_AVATAR_MEDIUM), 'lg'); ?>
			</div>
			<?php } ?>

			<?php if ($params->get('show_name', true)) { ?>
			<a href="<?php echo $event->getPermalink();?>" class="mod-es-title"><?php echo $event->getName();?></a>
			<?php } ?>

			<?php if ($params->get('show_members', true)) { ?>
			<div class="mod-es-meta">
				<?php echo JText::sprintf('MOD_EASYSOCIAL_EVENT_MENU_TOTAL_GUEST', $event->getTotalGoing()); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="mod-es-menu-list">

		<a href="<?php echo $event->getPermalink();?>" class="mod-es-menu-list__item">
			<?php echo JText::_('COM_ES_TIMELINE');?>
		</a>
		<a href="<?php echo ESR::events(array('id' => $event->getAlias(), 'page' => 'info', 'layout' => 'item'));?>" class="mod-es-menu-list__item">
			<?php echo JText::_('COM_ES_EVENTS_ABOUT');?>
		</a>
		<a href="<?php echo $event->getAppPermalink('guests');?>" class="mod-es-menu-list__item">
			<?php echo JText::_('COM_ES_ATTENDEES');?>
		</a>
		<?php if ($event->allowPhotos()) { ?>
		<a href="<?php echo ESR::albums(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));?>" class="mod-es-menu-list__item">
			<?php echo JText::_('COM_ES_ALBUMS');?>
		</a>
		<?php } ?>

		<?php if ($event->allowVideos()) { ?>
		<a href="<?php echo ESR::videos(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));?>" class="mod-es-menu-list__item">
			<?php echo JText::_('COM_EASYSOCIAL_VIDEOS');?>
		</a>
		<?php } ?>

		<?php if ($event->allowAudios()) { ?>
		<a href="<?php echo ESR::audios(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));?>" class="mod-es-menu-list__item">
			<?php echo JText::_('COM_ES_AUDIOS');?>
		</a>
		<?php } ?>

		<?php foreach ($apps as $item) { ?>
		<a href="<?php echo $event->getAppPermalink($item->element);?>" class="mod-es-menu-list__item">
			<?php echo $item->getAppTitle(); ?>
		</a>
		<?php } ?>
	</div>
</div>
