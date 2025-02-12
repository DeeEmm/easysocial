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
<div id="es" class="mod-es mod-es-sidebar-videos <?php echo $this->lib->getSuffix();?>"
	data-es-video-filters
	data-uid="<?php echo $cluster ? $cluster->id : '' ?>"
	data-type="<?php echo $cluster ? $cluster->getType() : '' ?>"
	data-active="<?php echo !$filter ? 'all' : $filter;?>"
>

	<div data-sidebar class="es-sidebar"
		<?php if ($this->config->get('video.counters')) { ?>
		data-es-sidebar-wrapper
		data-type="<?php echo SOCIAL_TYPE_VIDEO; ?>"
		data-uid="<?php echo $uid; ?>"
		data-utype="<?php echo $type; ?>"
		<?php } ?>
	>
		<?php echo $this->lib->render('module', 'es-videos-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($allowCreation) { ?>
		<a class="btn btn-es-primary btn-block t-lg-mb--xl" href="<?php echo $createLink;?>">
			<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_ADD_VIDEO');?>
		</a>
		<?php } ?>

		<div class="es-side-widget" data-section data-load="1" data-type="<?php echo SOCIAL_TYPE_VIDEOS; ?>" data-active="<?php echo $filter; ?>">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_VIDEOS'); ?>

			<div class="es-side-widget__bd" data-section-content>

			<?php if ($this->config->get('video.counters')) { ?>
				<?php echo $this->lib->html('widget.placeholder'); ?>
			<?php } else {  ?>

				<?php echo ES::themes()->includeTemplate('site/videos/default/sidebar.filters', ['filter' => $filter , 'adapter' => $adapter, 'titles' => $titles, 'filtersAcl' => $filtersAcl, 'counters' => $counters]); ?>

			<?php } ?>
			</div>
		</div>

		<?php if ($canCreateFilter && $browseView) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget" data-section data-type="custom-filters">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_CUSTOM_FILTERS'); ?>

			<div class="es-side-widget__bd" data-section-content>
				<div class="es-side-widget__filter">
					<ul class="o-tabs o-tabs--stacked" data-section-lists>
						<?php if ($customFilters) { ?>
							<?php foreach ($customFilters as $customFilter) { ?>
							<li class="o-tabs__item <?php echo $filter == 'customFilter' && $activeCustomFilter && $activeCustomFilter->id == $customFilter->id ? 'active' : '';?>" data-filter-item data-type="hashtag" data-tag-id="<?php echo $customFilter->id ?>">
								<a href="<?php echo $customFilter->permalink; ?>"
									title="<?php echo JText::_($customFilter->title); ?>"
									class="o-tabs__link"
								>
									<?php echo '#' . $customFilter->title; ?>
								</a>
							</li>
							<?php } ?>
						<?php } ?>
					</ul>

					<?php if (!$customFilters) { ?>
					<div class="t-text--muted">
						<?php echo JText::_('COM_EASYSOCIAL_NO_CUSTOM_FILTERS_AVAILABLE'); ?>
					</div>
					<?php } ?>
				</div>
			</div>

			<a href="<?php echo $createCustomFilterLink;?>" class="btn btn-es-primary-o btn-block t-lg-mt--xl"
				data-video-create-filter
				data-type="videos"
				data-uid="<?php echo $uid;?>"
				data-cluster-type="<?php echo $type;?>"
			>
				<?php echo JText::_('COM_ES_NEW_FILTER'); ?>
			</a>
		</div>
		<?php } ?>

		<?php if (($browseView || $isCluster || $isUserProfileView) && $categories) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget" data-section data-load="1" data-type="categories" data-active="<?php echo $activeCategory ? $activeCategory->id : ''; ?>">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_VIDEOS_CATEGORIES'); ?>

			<div class="es-side-widget__bd" data-section-content>
			<?php if ($this->config->get('video.counters')) { ?>
				<?php echo $this->lib->html('widget.placeholder'); ?>
			<?php } else { ?>
				<?php echo $this->lib->html('categories.sidebar', SOCIAL_TYPE_VIDEO, $activeCategory); ?>
			<?php } ?>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-videos-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>

<script>
EasySocial
.require()
.script('site/videos/filter', 'site/sidebars/section')
.done(function($) {

	var wrapper = $('[data-es-video-filters]'),
	uid = wrapper.data('uid'),
	type = wrapper.data('type')
	active = wrapper.data('active');

	$('body').addController(EasySocial.Controller.Videos.Filter, {
		"uid": uid,
		"type": type,
		"active": active
	});

	var sidebarWrapper = $('[data-es-sidebar-wrapper]'),
	uid = sidebarWrapper.data('uid'),
	utype = sidebarWrapper.data('utype'),
	type = sidebarWrapper.data('type');

	if (sidebarWrapper.length > 0) {
		$('body').addController(EasySocial.Controller.Sidebars.Section, {
			"uid": uid,
			"utype": utype,
			"type": type
		});
	}

});
</script>
