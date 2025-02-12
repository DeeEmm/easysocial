

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
<div id="es" class="mod-es mod-es-sidebar-marketplaces <?php echo $this->lib->getSuffix();?>" data-es-marketplace-filters>
	<div class="es-sidebar" data-sidebar>

		<?php echo $this->lib->render('module', 'es-marketplaces-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ((!$cluster && $user->isViewer() && $this->lib->my->canCreateListing()) || ($cluster && $cluster->canCreateListing())) { ?>
		<a href="<?php echo ESR::marketplaces($createUrl); ?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl">
			<?php echo JText::_('COM_ES_MARKETPLACES_ADD_LISTING'); ?>
		</a>
		<?php } ?>

		<div class="es-side-widget">

			<?php echo $this->lib->html('widget.title', 'COM_ES_MARKETPLACE'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo $filter == 'all' && !$activeCategory ? 'active' : ''; ?>" data-filter-item data-type="all">
						<a href="<?php echo $filtersLink->all; ?>"
						title="<?php echo JText::_('COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_ALL', true); ?>"
						class="o-tabs__link">
							<?php echo JText::_('COM_ES_MARKETPLACES_FILTERS_ALL_LISTINGS'); ?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->all; ?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item has-notice <?php echo $filter == 'featured' ? 'active' : ''; ?>" data-filter-item data-type="featured">
						<a href="<?php echo $filtersLink->featured; ?>"
						title="<?php echo JText::_('COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_FEATURED', true); ?>"
						class="o-tabs__link">
							<?php echo JText::_('COM_ES_MARKETPLACES_FILTERS_FEATURED_LISTINGS'); ?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->featured; ?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<?php if ((!$cluster && $browseView && $user->canCreateListing())) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'created' ? 'active' : ''; ?>" data-filter-item data-type="created">
						<a href="<?php echo $filtersLink->created; ?>"
						title="<?php echo JText::_('COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_CREATED', true); ?>"
						class="o-tabs__link">
							<?php echo JText::_('COM_ES_MARKETPLACES_FILTERS_MY_LISTINGS'); ?>
						</a>

						<span class="o-tabs__bubble" data-counter><?php echo $counters->created; ?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if ($browseView) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'nearby' ? 'active' : ''; ?>" data-filter-item data-type="nearby">
							<a href="<?php echo ESR::marketplaces(array('filter' => 'nearby')); ?>" title="<?php echo JText::_('COM_ES_NEARBY_LISTINGS', true); ?>" class="o-tabs__link">
								<?php echo JText::_('COM_ES_NEARBY_LISTINGS'); ?>
							</a>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php if ($browseView || $isCluster || $isUserProfileView) { ?>
			<hr class="es-hr" />
			<div class="es-side-widget">
				<?php echo $this->lib->html('widget.title', 'COM_ES_MARKETPLACES_CATEGORIES'); ?>

				<div class="es-side-widget__bd">
					<?php echo $this->lib->html('categories.sidebar', SOCIAL_TYPE_MARKETPLACE, $activeCategory) ?>
				</div>
			</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-marketplaces-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
