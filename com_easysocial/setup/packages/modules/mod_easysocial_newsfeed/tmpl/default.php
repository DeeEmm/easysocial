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
<div id="es" data-es-module-newsfeed data-has-stream-filter="<?php echo $streamFilterEnabled ? 1 : 0;?>">
	<div class="es-side-widget" data-type="feeds">
		<div class="es-side-widget__bd">
			<ul class="o-tabs o-tabs--stacked feed-items" data-dashboard-feeds>

				<?php if ($lib->config->get('users.dashboard.everyone') && $params->get('display_everyone', true)) { ?>
				<li class="o-tabs__item <?php echo $filter == 'everyone' ? ' active' : '';?>" data-filter-item data-type="everyone">
					<a href="<?php echo ESR::dashboard(array('type' => 'everyone'));?>" class="o-tabs__link">
						<?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_SIDEBAR_NEWSFEEDS_EVERYONE');?>
						<div class="o-tabs__bubble" data-counter>0</div>
					</a>
				</li>
				<?php } ?>

				<?php if ($params->get('display_friends', true)) { ?>
				<li class="o-tabs__item <?php echo (empty($filter) || $filter == 'me') ? 'active' : '';?>" data-filter-item data-type="me">
					<a href="<?php echo ESR::dashboard(array('type' => 'me'));?>" class="o-tabs__link">
						<?php if ($lib->config->get('friends.enabled')) { ?>
							<?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_SIDEBAR_ME_AND_FRIENDS');?>
						<?php } else {  ?>
							<?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_SIDEBAR_MY_UPDATES');?>
						<?php } ?>
						<div class="o-tabs__bubble" data-counter>0</div>
					</a>
				</li>
				<?php } ?>

				<?php if ($params->get('display_following', true) && $lib->config->get('followers.enabled')) { ?>
				<li class="o-tabs__item <?php echo $filter == 'following' ? ' active' : '';?>" data-filter-item data-type="following">
					<a href="<?php echo ESR::dashboard(array('type' => 'following'));?>" class="o-tabs__link">
						<?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_FEEDS_FOLLOWING');?>
						<div class="o-tabs__bubble" data-counter>0</div>
					</a>
				</li>
				<?php } ?>

				<?php if ($params->get('display_favourites', true) && $lib->config->get('stream.bookmarks.enabled')) { ?>
					<li class="o-tabs__item <?php echo $filter == 'bookmarks' ? ' active' : '';?>" data-filter-item data-type="bookmarks">
						<a href="<?php echo ESR::dashboard(array('type' => 'bookmarks'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_FEEDS_BOOKMARKS'); ?></a>
					</li>
				<?php } ?>

				<?php if ($params->get('display_pinned', true) && $lib->config->get('stream.pin.enabled')) { ?>
					<li class="o-tabs__item <?php echo $filter == 'sticky' ? ' active' : '';?>" data-filter-item data-type="sticky">
						<a href="<?php echo ESR::dashboard(array('type' => 'sticky'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_DASHBOARD_FEEDS_STICKY'); ?></a>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>

				<?php if (ES::user()->canPostScheduledStory(SOCIAL_TYPE_USER)) { ?>
				<li class="o-tabs__item <?php echo $filter == 'sticky' ? ' active' : '';?>" data-filter-item data-type="scheduled">
					<a href="<?php echo ESR::dashboard(array('type' => 'scheduled'));?>" class="o-tabs__link"><?php echo JText::_('COM_ES_STORY_SCHEDULED'); ?></a>
					<div class="o-loader o-loader--sm"></div>
				</li>
				<?php } ?>

				<?php if ($showCustomFilters && $lib->config->get('users.dashboard.customfilters')) { ?>
					<?php foreach ($filterList as $filter) { ?>
					<li class="o-tabs__item <?php echo $filterId == $filter->id ? ' active' : '';?>" class="o-tabs__item" data-filter-item data-type="custom" data-id="<?php echo $filter->id; ?>">
						<a href="<?php echo ESR::dashboard(array('type' => 'filter', 'filterid' => $filter->getAlias()));?>" class="o-tabs__link">
							<?php echo $filter->_('title'); ?>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				<?php } ?>
			</ul>
		</div>
	</div>

	<?php if ($params->get('display_custom', true) && $lib->config->get('users.dashboard.customfilters')) { ?>
		<div class="es-side-widget__ft">
			<a href="javascript:void(0);" data-mod-filter-create class="es-side-widget-btn-showmore"><?php echo JText::_('COM_ES_CREATE_NEW_FILTER'); ?></a>
		</div>
	<?php } ?>
</div>
