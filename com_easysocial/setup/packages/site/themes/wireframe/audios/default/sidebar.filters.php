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
<ul data-es-audios-filters class="o-tabs o-tabs--stacked">
	<li class="o-tabs__item has-notice <?php echo ($filter == '' || $filter == 'all') ? 'active' : '';?>" data-filter-item data-type="all">
		<a href="<?php echo $adapter->getAllAudiosLink();?>"
			data-type="all"
			title="<?php echo $titles->all;?>"
			class="o-tabs__link">
			<span><?php echo JText::_('COM_ES_AUDIO_FILTERS_ALL_AUDIOS');?></span>
		</a>
		<?php if ($total) { ?>
			<span class="o-tabs__bubble" data-counter><?php echo $total->audios;?></span>
		<?php } ?>
		<div class="o-loader o-loader--sm"></div>
	</li>

	<li class="o-tabs__item has-notice <?php echo $filter == 'featured' ? 'active' : '';?>" data-filter-item data-type="featured">
		<a href="<?php echo $adapter->getAllAudiosLink('featured');?>"
			title="<?php echo $titles->featured;?>"
			class="o-tabs__link">
			<span><?php echo JText::_('COM_ES_AUDIO_FILTERS_FEATURED_AUDIOS');?></span>
		</a>
		<?php if ($total) { ?>
			<span class="o-tabs__bubble" data-counter data-total-featured><?php echo $total->featured;?></span>
		<?php } ?>

		<div class="o-loader o-loader--sm"></div>
	</li>

	<?php if ($filtersAcl->mine) { ?>
		<li class="o-tabs__item has-notice <?php echo $filter == 'mine' ? 'active' : '';?>" data-filter-item data-type="mine">
			<a href="<?php echo FRoute::audios(array('filter' => 'mine'));?>"
				title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_MINE');?>"
				class="o-tabs__link">
				<span><?php echo JText::_('COM_ES_AUDIO_FILTERS_MY_AUDIOS');?></span>
			</a>
			<?php if ($total) { ?>
				<span class="o-tabs__bubble" data-counter data-total-created><?php echo $total->user;?></span>
			<?php } ?>

			<div class="o-loader o-loader--sm"></div>
		</li>
	<?php } ?>

	<?php if ($filtersAcl->pending) { ?>
		<li class="o-tabs__item has-notice <?php echo $filter == 'pending' ? 'active' : '';?>" data-filter-item data-type="pending">
			<a href="<?php echo $adapter->getAllAudiosLink('pending');?>"
				title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_PENDING');?>"
				class="o-tabs__link">
				<span><?php echo JText::_('COM_ES_AUDIO_FILTERS_PENDING_AUDIOS');?></span>
			</a>
			<?php if ($total) { ?>
				<span class="o-tabs__bubble" data-counter data-total-pending><?php echo $total->pending;?></span>
			<?php } ?>
			<div class="o-loader o-loader--sm"></div>
		</li>
	<?php } ?>
</ul>