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
<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_GROUPS_RANDOM_GROUPS'); ?>

	<div class="es-side-widget__bd">
		<?php if ($groups) { ?>
		<div class="o-flag-list">
			<?php $i = 1; ?>
			<?php foreach ($groups as $group) { ?>
			<div class="o-flag <?php echo $i > SOCIAL_CLUSTER_CATEGORY_MEMBERS_LIMIT ? 't-hidden' : '';?>" data-group-item>
				<div class="o-flag__image">
					<?php echo $this->html('avatar.mini', $group->getName(), $group->getPermalink(), $group->getAvatar()); ?>
				</div>
				<div class="o-flag__body">
					<a href="<?php echo $group->getPermalink();?>" class="ed-user-name t-mb--sm"><?php echo $group->getName();?></a>
					<div class="t-text--muted">
						<i class="fa fa-users"></i> <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_MEMBERS_MINI', $group->getTotalMembers()), $group->getTotalMembers());?>
					</div>
				</div>
			</div>
			<?php $i++;?>
			<?php } ?>

			<?php if ($i > SOCIAL_CLUSTER_CATEGORY_MEMBERS_LIMIT) { ?>
			<div>
				<a href="javascript:void();" class="es-side-widget-btn-showmore" data-view-all><?php echo JText::_('COM_ES_VIEW_ALL');?></a>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<div class="t-text--muted">
			<?php echo JText::_('COM_EASYSOCIAL_GROUPS_NO_GROUPS_YET'); ?>
		</div>
		<?php } ?>
	</div>
</div>

<div class="es-side-widget">

	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_GROUPS_RANDOM_MEMBERS'); ?>

	<div class="es-side-widget__bd">
		<?php if ($randomMembers) { ?>
			<?php echo $this->html('widget.users', $randomMembers); ?>
		<?php } else { ?>
			<div class="t-text--muted">
				<?php echo JText::_('COM_EASYSOCIAL_GROUPS_NO_MEMBERS_HERE'); ?>
			</div>
		<?php } ?>
	</div>
</div>

<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_GROUPS_RANDOM_ALBUMS'); ?>

	<div class="es-side-widget__bd">
		<?php echo $this->html('widget.albums', $randomAlbums, 'COM_EASYSOCIAL_GROUPS_NO_ALBUMS_HERE'); ?>
	</div>
</div>