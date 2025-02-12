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
<div id="es" class="mod-es mod-es-sidebar-pages <?php echo $this->lib->getSuffix();?>" data-es-page-filters data-user-id="<?php echo $user ? $user->id : '';?>">
	<div class="es-sidebar" data-sidebar>

		<?php echo $this->lib->render('module', 'es-pages-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ((!$user || ($user && $user->isViewer())) && $this->lib->my->canCreatePages()) { ?>
		<a href="<?php echo ESR::pages(array('layout' => 'create'));?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl">
			<?php echo JText::_('COM_EASYSOCIAL_PAGES_START_YOUR_PAGE');?>
		</a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PAGES'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php if ($browseView) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'all' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="all">
							<a href="<?php echo $filters->all;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES' , true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_ALL_PAGES');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->total;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'featured' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="featured">
							<a href="<?php echo $filters->featured;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_FEATURED', true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_FEATURED_PAGES');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->featured;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } else { ?>

						<?php if ((!$user || ($user && $user->canCreatePages()))) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'created' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="created">
							<a href="<?php echo $filters->created;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_CREATED_PAGES' , true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_CREATED_PAGES');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->created;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<?php } ?>

						<li class="o-tabs__item has-notice <?php echo $filter == 'participated' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="participated">
							<a href="<?php echo $filters->participated;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_PARTICIPATED_PAGES' , true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_PARTICIPATED_PAGES');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->participated;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

					<?php if ($showMyPages) { ?>
						<?php if ((!$user || ($user && $user->isViewer())) && $this->lib->my->canCreatePages()) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'mine' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="mine">
							<a href="<?php echo ESR::pages(array('filter' => 'mine'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_MY_PAGES', true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_MY_PAGES');?>
							</a>
						<?php } ?>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->created;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'liked' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="liked">
							<a href="<?php echo ESR::pages(array('filter' => 'liked'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_LIKED', true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_MY_LIKED_PAGES');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->participated;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'unpublished' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="unpublished">
							<a href="<?php echo ESR::pages(array('filter' => 'unpublished'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_UNPUBLISHED', true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_UNPUBLISHED_PAGES');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->unpublished;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

					<?php if ($showPendingPages) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'pending' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="pending">
							<a href="<?php echo $filters->pending;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_PENDING', true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_FILTER_PENDING');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->pending;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

					<?php if ($showInvites) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'invited' && !$activeCategory ? ' active' : '';?>" data-filter-item data-type="invited">
							<a href="<?php echo $filters->invited;?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_PAGES_FILTER_INVITED', true);?>"  class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_PAGES_INVITED');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->invites;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

				</ul>
			</div>
		</div>

		<?php if ($browseView) { ?>
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PAGES_CATEGORIES_SIDEBAR_TITLE'); ?>

			<div class="es-side-widget__bd">
				<?php echo $this->lib->html('categories.sidebar', SOCIAL_TYPE_PAGE, $activeCategory) ?>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-pages-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>

<script>
EasySocial
.require()
.script('site/pages/filter')
.done(function($) {

	var wrapper = $('[data-es-page-filters]'),
	userId = wrapper.data('user-id');

	$('body').addController(EasySocial.Controller.Pages.Filter, {
		"userId": userId
	});
});
</script>
