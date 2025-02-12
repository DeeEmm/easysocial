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
<div id="es" class="mod-es mod-es-sidebar-audios <?php echo $this->lib->getSuffix();?>"
	data-es-audio-filters
	data-uid="<?php echo $cluster ? $cluster->id : '' ?>"
	data-type="<?php echo $cluster ? $cluster->getType() : '' ?>"
	data-active="<?php echo !$filter ? 'all' : $filter;?>"
>
	<div data-sidebar class="es-sidebar"
		<?php if ($this->config->get('audio.counters')) { ?>
		data-es-sidebar-wrapper
		data-type="<?php echo SOCIAL_TYPE_AUDIO; ?>"
		data-uid="<?php echo $uid; ?>"
		data-utype="<?php echo $type; ?>"
		<?php } ?>
	>
		<?php echo $this->lib->render('module', 'es-audios-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($allowCreation) { ?>
			<a class="btn btn-es-primary btn-block t-lg-mb--xl" href="<?php echo $createLink;?>">
				<?php echo JText::_('COM_ES_AUDIO_ADD_AUDIO');?>
			</a>
		<?php } ?>

		<div class="es-side-widget" data-section data-load="1" data-type="<?php echo SOCIAL_TYPE_AUDIOS; ?>" data-active="<?php echo $filter; ?>">
			<div class="es-side-widget__hd">
				<div class="es-side-widget__title"><?php echo JText::_('COM_ES_AUDIO');?></div>
			</div>

			<div class="es-side-widget__bd" data-section-content>
				<?php if ($this->config->get('audio.counters')) { ?>
					<?php echo $this->lib->html('widget.placeholder'); ?>
				<?php } else {  ?>
					<?php echo ES::themes()->includeTemplate('site/audios/default/sidebar.filters', ['filter' => $filter , 'adapter' => $adapter, 'titles' => $titles, 'filtersAcl' => $filtersAcl, 'total' => $total]); ?>
				<?php } ?>
			</div>
		</div>

		<?php if ($canCreateFilter && $browseView) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget" data-section data-type="custom-filters">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_CUSTOM_FILTERS'); ?>

			<div class="es-side-widget__bd">
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
				data-audio-create-filter
				data-type="audios"
				data-uid="<?php echo $uid;?>"
				data-cluster-type="<?php echo $type;?>"
			>
				<?php echo JText::_('COM_ES_NEW_FILTER'); ?>
			</a>
		</div>
		<?php } ?>

		<?php if ($browseView || $isCluster || $isUserProfileView) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget" data-section data-load="1" data-type="genres" data-active="<?php echo $currentGenre ? $currentGenre->id : ''; ?>">
			<div class="es-side-widget__hd">
				<div class="es-side-widget__title"><?php echo JText::_('COM_ES_AUDIO_GENRES');?></div>
			</div>

			<div class="es-side-widget__bd" data-section-content>
				<?php if ($this->config->get('audio.counters')) { ?>
					<?php echo $this->lib->html('widget.placeholder'); ?>
				<?php } else { ?>
					<?php echo $this->lib->html('categories.sidebar', SOCIAL_TYPE_AUDIO, $currentGenre) ?>
				<?php } ?>
			</div>
		</div>
		<?php } ?>

		<?php if ($canCreatePlaylist) { ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_ES_AUDIO_PLAYLISTS'); ?>

			<div class="es-side-widget__bd" data-audios-list>
				<?php if ($playlists) { ?>
				<ul class="o-tabs o-tabs--stacked" data-audios-listItems>
					<?php foreach ($playlists as $list) { ?>
						<li class="o-tabs__item has-notice item-<?php echo $list->id;?> <?php echo $activePlaylist && $activePlaylist->id == $list->id ? ' active' : '';?>" data-id="<?php echo $list->id;?>" data-filter-item data-type="list">
							<a href="<?php echo $adapter->getPlaylistLink($list->id);?>"
								title="<?php echo $this->lib->html('string.escape' , $list->get('title'));?>"
								class="o-tabs__link">
								<?php echo $this->lib->html('string.escape', $list->get('title')); ?>
							</a>
							<?php if ($this->config->get('audio.counters')) { ?>
								<span class="o-tabs__bubble" data-counter><?php echo $list->getCount();?></span>
							<?php } ?>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>
				</ul>
				<?php } else { ?>
				<div class="t-text--muted">
					<?php echo JText::_('COM_ES_AUDIO_NO_PLAYLIST_CREATED_YET'); ?>
				</div>
				<?php } ?>
			</div>
		</div>


		<a href="<?php echo ESR::audios(array('layout' => 'playlistform'));?>" class="btn btn-es-primary-o btn-block t-lg-mt--xl">
			<?php echo JText::_('COM_ES_AUDIO_NEW_PLAYLIST'); ?>
		</a>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-audios-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>

	</div>
</div>

<script>
EasySocial
.require()
.script('site/audios/filter', 'site/sidebars/section')
.done(function($) {

	var wrapper = $('[data-es-audio-filters]'),
	uid = wrapper.data('uid'),
	type = wrapper.data('type')
	active = wrapper.data('active');

	$('body').addController(EasySocial.Controller.Audios.Filter, {
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
