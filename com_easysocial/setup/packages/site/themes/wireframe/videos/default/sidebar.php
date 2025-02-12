<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<div data-sidebar class="es-sidebar">
	<?php echo $this->render('module', 'es-videos-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

	<?php if ($allowCreation) { ?>
	<a class="btn btn-es-primary btn-block t-lg-mb--xl" href="<?php echo $createLink;?>">
		<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_ADD_VIDEO');?>
	</a>
	<?php } ?>

	<div class="es-side-widget">

		<div class="es-side-widget__hd">
			<div class="es-side-widget__title"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS');?></div>
		</div>

		<div class="es-side-widget__bd">
			<ul data-es-videos-filters="" class="o-tabs o-tabs--stacked">
				<li class="o-tabs__item has-notice <?php echo ($filter == '' || $filter == 'all') ? 'active' : '';?>">
					<a href="<?php echo $adapter->getAllVideosLink();?>"
						data-videos-filter
						data-type="all"
						title="<?php echo $this->html('string.escape', $allVideosPageTitle);?>"
						class="o-tabs__link">
						<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_ALL_VIDEOS');?></span>
					</a>
					<span class="o-tabs__bubble" data-counter><?php echo $total;?></span>
					<div class="o-loader o-loader--sm"></div>
				</li>

				<li class="o-tabs__item has-notice <?php echo $filter == 'featured' ? 'active' : '';?>">
					<a href="<?php echo $adapter->getAllVideosLink('featured');?>"
						data-videos-filter
						data-type="featured"
						title="<?php echo $this->html('string.escape', $featuredVideosPageTitle);?>"
						class="o-tabs__link">
						<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_FEATURED_VIDEOS');?></span>
					</a>
					<span class="o-tabs__bubble" data-counter data-total-featured><?php echo $totalFeatured;?></span>
					<div class="o-loader o-loader--sm"></div>
				</li>

				<?php if ($showMyVideos) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'mine' ? 'active' : '';?>">
						<a href="<?php echo FRoute::videos(array('filter' => 'mine'));?>"
							data-videos-filter
							data-type="mine"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_MINE');?>"
							class="o-tabs__link">
							<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_MY_VIDEOS');?></span>
						</a>
						<span class="o-tabs__bubble" data-counter data-total-created><?php echo $totalUserVideos;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>
				<?php if ($showPendingVideos) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'pending' ? 'active' : '';?>">
						<a href="<?php echo FRoute::videos(array('filter' => 'pending'));?>"
							data-videos-filter
							data-type="pending"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_PENDING');?>"
							class="o-tabs__link">
							<span><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FILTERS_PENDING_VIDEOS');?></span>
						</a>
						<span class="o-tabs__bubble" data-counter data-total-pending><?php echo $totalPending;?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
				<?php } ?>

			</ul>
		</div>
	</div>

	<?php if ($canCreateFilter && $browseView) { ?>
	<hr class="es-hr" />
	<div class="es-side-widget" data-section data-type="custom-filters">
		<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_CUSTOM_FILTERS'); ?>

		<div class="es-side-widget__bd">
			<div class="es-side-widget__filter">
				<ul class="o-tabs o-tabs--stacked" data-section-lists>
					<?php if (isset($hashtagFilter) && $hashtagFilter) { ?>
						<?php foreach ($hashtagFilter as $hashtag) { ?>
						<li class="o-tabs__item">
						<a
						data-videos-filter
						data-type="hashtag"
						title="<?php echo JText::_($hashtag->title); ?>"
						data-tag-id="<?php echo $hashtag->id ?>"
						href="<?php echo $hashtag->permalink; ?>" class="o-tabs__link"><?php echo '#' . $hashtag->title; ?></a>
						</li>
						<?php } ?>
					<?php } ?>
				</ul>
				<?php if (!$hashtagFilter) { ?>
				<div class="t-text--muted">
					<?php echo JText::_('COM_EASYSOCIAL_NO_CUSTOM_FILTERS_AVAILABLE'); ?>
				</div>
				<?php } ?>
			</div>
		</div>

		<a href="<?php echo $customFilterLink;?>" class="btn btn-es-primary-o btn-block t-lg-mt--xl" data-video-create-filter data-type="videos" data-uid="<?php echo $uid;?>" data-cluster-type="<?php echo $type;?>">
			<?php echo JText::_('COM_ES_NEW_FILTER'); ?>
		</a>
	</div>
	<?php } ?>

	<?php if ($browseView) { ?>
	<hr class="es-hr" />
	<div class="es-side-widget">
		<div class="es-side-widget__hd">
			<div class="es-side-widget__title"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_CATEGORIES');?></div>
		</div>
		<div class="es-side-widget__bd">
			<?php if ($categories) { ?>
				<ul data-es-videos-categories class="o-tabs o-tabs--stacked">
					<?php foreach ($categories as $category) { ?>
					<li class="o-tabs__item has-notice<?php echo $currentCategory == $category->id ? ' active' : '';?>">
						<a href="<?php echo $category->getPermalink(true, $uid, $type);?>"
							data-videos-filter
							data-type="category"
							data-id="<?php echo $category->id;?>"
							title="<?php echo JText::_($category->pageTitle, true);?>"
							class="o-tabs__link">
							<span><?php echo JText::_($category->title);?></span>
						</a>

						<?php $totalCategoryVideos = $category->getTotalVideos($cluster, $uid, $type); ?>
						<span class="o-tabs__bubble" data-counter data-total-videos="<?php echo $totalCategoryVideos; ?>"><?php echo $totalCategoryVideos; ?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<?php echo $this->render('module', 'es-videos-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
</div>
