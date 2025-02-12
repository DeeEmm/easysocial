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
<div class="panel" data-notifications-widget>
	<div class="panel-head t-my--no t-pb--no t-pl--no">
		<div class="t-d--flex">
			<div class="t-flex-grow--1">
				<b class="panel-head-title">Information</b>
			</div>
		</div>
	</div>

	<?php echo $this->output('admin/easysocial/widgets/version', [
		'installedVersion' => $installedVersion
	]); ?>

	<?php if ($appUpdates) { ?>
	<div class="panel-body">
		<div class="l-stack t-text--center">
			<div class="">
				<div>
					<i class="fa fa-download" style="display: block; font-size: 24px;margin-bottom: 10px;"></i>
					<b><?php echo JText::sprintf('COM_EASYSOCIAL_APPS_REQUIRING_UPDATES', $appUpdates); ?></b>
				</div>
			</div>

			<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=apps&filter=outdated');?>" class="btn btn-es-default-o">
				<?php echo JText::_('COM_EASYSOCIAL_VIEW_APPS_BUTTON');?>
			</a>
		</div>
	</div>
	<?php } ?>
</div>


<div class="panel">
	<div class="panel-head t-my--no t-pb--no t-pl--no">
		<div class="t-d--flex">
			<div class="t-flex-grow--1">
				<b class="panel-head-title"><?php echo JText::_('Statistics');?></b>
			</div>
		</div>
	</div>
	<div class="panel-body t-bg--100 t-px--md t-m--no">
		<div class="l-stack l-spaces--xs">
			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=users'),
				'icon' => 'fa fa-user-friends',
				'label' => 'COM_EASYSOCIAL_USERS',
				'count' => $totalUsers
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=groups'),
				'icon' => 'fas fa-users',
				'label' => 'COM_EASYSOCIAL_GROUPS',
				'count' => $totalGroups
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=events'),
				'icon' => 'fa fa-calendar-check',
				'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_EVENTS',
				'count' => $totalEvents
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => 'javascript:void(0);',
				'icon' => 'fa fa-signal',
				'label' => 'COM_EASYSOCIAL_ONLINE',
				'count' => $totalOnline
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=albums'),
				'icon' => 'fa fa-images',
				'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_ALBUMS',
				'count' => $totalAlbums
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=audios'),
				'icon' => 'fa fa-music',
				'label' => 'COM_ES_WIDGETS_STATS_TOTAL_AUDIO',
				'count' => $totalAudios
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=videos'),
				'icon' => 'fa fa-play-circle',
				'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_VIDEOS',
				'count' => $totalVideos
			]); ?>

			<?php echo $this->output('admin/easysocial/widgets/stats.item', [
				'link' => JRoute::_('index.php?option=com_easysocial&view=reports'),
				'icon' => 'fa fa-flag',
				'label' => 'COM_EASYSOCIAL_WIDGETS_STATS_TOTAL_REPORTS',
				'count' => $totalReports
			]); ?>
		</div>
	</div>
</div>
