<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-media-browser-dialog sidebar-open" data-layout="dialog" data-album-browser="<?php echo $uuid; ?>">
	<div class="es-media-browser-dialog__sidebar <?php echo empty($albums) ? 'is-empty' : ''; ?>" data-album-browser-sidebar>
		<div class="es-side-widget">
			<div class="es-side-widget__bd">
				<ul class="g-list-unstyled o-tabs o-tabs--stacked" data-album-list-item-group>
					<?php foreach($albums as $album) { ?>
					<li class="o-tabs__item has-notice <?php echo $album->id == $id ? 'active' : '';?>" data-album-list-item data-album-id="<?php echo $album->id; ?>">
						<a href="<?php echo $album->getPermalink(); ?>" title="<?php echo $this->html('string.escape', $album->get('title')); ?>" class="o-tabs__link">
							<span class="o-flag">
								<span class="o-flag__image">
									<span data-album-list-item-cover class="es-side-widget-cover"
									style="background-image: url('<?php echo $album->getCover(); ?>');" 
									>
									</span>
								</span>
								<span class="o-flag__body">
									<span data-album-list-item-title><?php echo $album->get('title'); ?></span>
									<div class="o-tabs__bubble" data-album-list-item-count><?php echo $album->getTotalPhotos(); ?></div>		
								</span>
							</span>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="o-empty">
			<div class="o-empty__content">
				<i class="o-empty__icon far fa-images"></i>
				<div class="o-empty__text">
					<?php echo JText::_('COM_EASYSOCIAL_NO_ALBUM_AVAILABLE'); ?>
				</div>

				<?php if ($lib->canCreateAlbums()) { ?>
				<div class="o-empty__action">
					<a href="<?php echo $lib->getCreateLink();?>" class="btn btn-primary btn-lg"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_CREATE_ALBUM'); ?></a>
				</div>
				<?php } ?>

			</div>
		</div>
	</div>
	<div class="es-media-browser-dialog__content" data-album-browser-content><?php echo $content; ?></div>
</div>