<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-sidebar" data-sidebar>
	<?php echo $this->render('module', 'es-groups-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

	<?php if ($user->isViewer() && $this->my->canCreateGroups()) { ?>
	<a href="<?php echo ESR::groups(array('layout' => 'create'));?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl">
		<?php echo JText::_('COM_EASYSOCIAL_GROUPS_START_YOUR_GROUP');?>
	</a>
	<?php } ?>

	<div class="es-side-widget">
		<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_GROUPS'); ?>

		<div class="es-side-widget__bd">
			<ul class="o-tabs o-tabs--stacked">

				<?php if ($browseView) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'all' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="all">
						<a href="<?php echo $filtersLink->all;?>" title="<?php echo JText::_( 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS' , true );?>" class="o-tabs__link">
							<?php echo JText::_( 'COM_EASYSOCIAL_GROUPS_FILTER_ALL_GROUPS' );?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalGroups;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item has-notice <?php echo $filter == 'featured' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="featured">
						<a href="<?php echo $filtersLink->featured;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_FEATURED', true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_EASYSOCIAL_GROUPS_FILTER_FEATURED_GROUPS');?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalFeaturedGroups;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } else { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'created' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="created">
						<a href="<?php echo $filtersLink->created;?>" title="<?php echo JText::_('COM_ES_GROUPS_FILTER_CREATED_GROUPS' , true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_ES_GROUPS_FILTER_CREATED_GROUPS');?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalCreatedGroups;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item has-notice <?php echo $filter == 'participated' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="participated">
						<a href="<?php echo $filtersLink->participated;?>" title="<?php echo JText::_('COM_ES_GROUPS_FILTER_PARTICIPATED_GROUPS' , true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_ES_GROUPS_FILTER_PARTICIPATED_GROUPS');?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalParticipatedGroups;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>



				<?php if ($showMyGroups) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'mine' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="mine">
						<a href="<?php echo ESR::groups(array('filter' => 'mine'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_MY_GROUPS', true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_EASYSOCIAL_GROUPS_FILTER_MY_GROUPS');?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalParticipatedGroups;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>

				<?php if ($showPendingGroups) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'pending' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="pending">
						<a href="<?php echo $filtersLink->pending;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_PENDING', true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_EASYSOCIAL_GROUPS_FILTER_PENDING');?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalPendingGroups;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>

				<?php if ($showTotalInvites) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'invited' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="invited">
						<a href="<?php echo $filtersLink->invited;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_INVITED', true);?>"  class="o-tabs__link">
							<?php echo JText::_('COM_EASYSOCIAL_GROUPS_INVITED');?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->totalInvites;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>

				<?php if ($browseView) { ?>
				<li class="o-tabs__item has-notice <?php echo $filter == 'nearby' ? 'active' : ''; ?>" data-filter-item data-type="nearby">
					<a href="<?php echo ESR::groups(array('filter' => 'nearby')); ?>" title="<?php echo JText::_('COM_ES_NEARBY_GROUPS', true); ?>" class="o-tabs__link">
						<?php echo JText::_('COM_ES_NEARBY_GROUPS'); ?>
					</a>
					<div class="o-loader o-loader--sm"></div>
				</li>

				<li class="o-tabs__item has-notice <?php echo $filter == 'unpublished' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="all">
					<a href="<?php echo ESR::groups(array('filter' => 'unpublished')); ?>" title="<?php echo JText::_('COM_ES_UNPUBLISHED_GROUPS', true); ?>" class="o-tabs__link">
						<?php echo JText::_('COM_ES_UNPUBLISHED_GROUPS'); ?>
					</a>

					<span class="o-tabs__bubble" data-counter><?php echo $counters->totalUnpublished;?></span>
					<div class="o-loader o-loader--sm"></div>
				</li>
			</ul>
		</div>
	</div>

	<?php if ($browseView) { ?>
	<hr class="es-hr" />
	<div class="es-side-widget">
		<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_GROUPS_CATEGORIES_SIDEBAR_TITLE'); ?>

		<div class="es-side-widget__bd">
			<?php echo $this->html('cluster.categoriesSidebar', SOCIAL_TYPE_GROUP, $activeCategory) ?>
		</div>
	</div>
	<?php } ?>

	<?php echo $this->render('module', 'es-groups-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
</div>
