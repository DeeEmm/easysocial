<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modisfied pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-sidebar-albums <?php echo $this->lib->getSuffix();?>" data-layout="album" data-album-uuid="<?php echo $uuid; ?>" data-es-album-filters>
	<div class="es-sidebar" data-album-browser-sidebar data-sidebar>

		<?php echo $this->lib->render('module', 'es-albums-sidebar-top'); ?>

		<?php if ($lib->canCreateAlbums() && !$lib->exceededLimits()) { ?>
			<a href="<?php echo $lib->getCreateLink();?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_CREATE_ALBUM'); ?></a>
		<?php } ?>

		<?php if ($coreAlbums) { ?>
		<div class="es-side-widget">

			<?php echo $this->lib->html('widget.title', JText::_($lib->getCoreAlbumsTitle())); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked" data-album-list-item-group="core">

				<?php foreach ($coreAlbums as $album) { ?>
				<li class="o-tabs__item has-notice<?php echo $album->id == $id ? ' active' : ''; ?>" data-album-list-item data-album-id="<?php echo $album->id; ?>">
					<a href="<?php echo $album->getPermalink();?>" title="<?php echo $album->get('title'); ?>" class="o-tabs__link" custom-title="<?php echo $this->lib->html('string.escape', $album->getPageTitle()); ?>">
						<span data-album-list-item-title><?php echo $album->get('title'); ?></span>
						<div class="o-tabs__bubble" data-album-list-item-count><?php echo $album->getTotalPhotos(); ?></div>
					</a>
					<div class="o-loader o-loader--sm"></div>
				</li>
				<?php } ?>

				</ul>
			</div>
		</div>
		<?php } ?>

		<?php if ($lib->showMyAlbums() && ($myAlbums || ($layout == 'form' && empty($id)))) { ?>
		<div class="es-side-widget">

			<?php echo $this->lib->html('widget.title', JText::_($lib->getMyAlbumsTitle())); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked" data-album-list-item-group="regular" data-album-list-item-container-myclusteralbum>

					<?php if ($layout == "form" && empty($id)) { ?>
					<li class="o-tabs__item has-notice active new" data-album-list-item>
						<a href="javascript: void(0);" class="o-tabs__link">
							<i data-album-list-item-cover></i>
							<span data-album-list-item-title><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_NEW_ALBUM'); ?></span>
							<div class="o-tabs__bubble" data-album-list-item-count>0</div>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php foreach ($myAlbums as $album) { ?>
					<li class="o-tabs__item has-notice<?php echo $album->id == $id ? ' active' : ''; ?>"  data-album-list-item data-album-id="<?php echo $album->id; ?>">
						<a href="<?php echo $album->getPermalink(); ?>" title="<?php echo $this->lib->html('string.escape', $album->get('title')); ?>" class="o-tabs__link" custom-title="<?php echo $this->lib->html('string.escape', $album->getPageTitle()); ?>">
							<i data-album-list-item-cover style="background-image: url(<?php echo $album->getCover(); ?>);"></i>
							<span data-album-list-item-title><?php echo $album->get('title'); ?></span>
							<div class="o-tabs__bubble" data-album-list-item-count><?php echo $album->getTotalPhotos(); ?></div>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			</div>

			<?php if ($myTotalClusterAlbums > count($myAlbums)) { ?>
				<div class="es-side-widget__bd">
					<a href="javascript:void(0);" data-album-mycluster-showall>
						<?php echo JText::_('COM_ES_VIEW_MORE');?>
					</a>
				</div>
			<?php } ?>

		</div>
		<?php } ?>

		<?php if ($albums || ($layout == "form" && empty($id) && !$lib->showMyAlbums())) { ?>
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', JText::_('COM_EASYSOCIAL_OTHER_ALBUMS')); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked" data-album-list-item-group="regular" data-album-list-item-container-regular>

					<?php if ($layout == "form" && empty($id) && !$lib->showMyAlbums()) { ?>
					<li class="o-tabs__item has-notice active new" data-album-list-item>
						<a href="javascript: void(0);" class="o-tabs__link">
							<i data-album-list-item-cover></i>
							<span data-album-list-item-title><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_NEW_ALBUM'); ?></span>
							<div class="o-tabs__bubble" data-album-list-item-count>0</div>
						</a>
					<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>

					<?php if ($albums) { ?>
						<?php foreach ($albums as $album) { ?>
						<li class="o-tabs__item has-notice<?php echo $album->id == $id ? ' active' : ''; ?>" data-album-list-item data-album-id="<?php echo $album->id; ?>">
							<a href="<?php echo $album->getPermalink(); ?>" title="<?php echo $this->lib->html('string.escape', $album->get('title')); ?>" class="o-tabs__link" custom-title="<?php echo $this->lib->html('string.escape', $album->getPageTitle()); ?>">
								<i data-album-list-item-cover style="background-image: url(<?php echo $album->getCover(); ?>);"></i>
								<span data-album-list-item-title><?php echo $album->get('title'); ?></span>
								<div class="o-tabs__bubble" data-album-list-item-count><?php echo $album->getTotalPhotos(); ?></div>
							</a>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<?php } ?>
					<?php } ?>
				</ul>
			</div>

			<?php if ($totalAlbums > count($albums)) { ?>
			<div class="es-side-widget__bd">
				<a href="javascript:void(0);" data-album-showall>
					<?php echo JText::_('COM_ES_VIEW_MORE');?>
				</a>
			</div>
			<?php } ?>

		</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-albums-sidebar-bottom'); ?>
	</div>
</div>

<script>
EasySocial
.require()
.script('site/albums/filter')
.done(function($){
	$('body').addController(EasySocial.Controller.Albums.Filter);
});
</script>
