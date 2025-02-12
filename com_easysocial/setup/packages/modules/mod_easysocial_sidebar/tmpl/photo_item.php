<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

if (!$lib->adapter) {
	return;
}
?>
<div id="es" class="mod-es mod-es-sidebar-photos <?php echo $this->lib->getSuffix();?>">
	<div data-photo-browser-sidebar class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('module', 'es-photos-sidebar-top'); ?>

		<div class="es-side-widget">
			<div class="es-side-widget__hd">
				<a href="<?php echo $lib->getAlbumLink(); ?>" class="btn btn-es-default-o btn-block" data-photo-back-button>
					<i class="fa fa-caret-left"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_PHOTOS_BACK_TO_ALBUM'); ?>
				</a>
			</div>

			<hr class="es-hr" />

			<div class="es-side-widget__bd">
				<div class="es-photo-browser">
					<ul class="es-nav-thumbs" data-photo-list-item-group>
						<li class="es-thumb grid-sizer">
							<a></a>
						</li>

						<?php foreach ($photos as $photo) { ?>
						<li class="es-thumb<?php echo $photo->id == $id ? ' active' : '';?><?php echo $photo->isFeatured() ? ' featured' : '';?>" data-photo-list-item data-photo-id="<?php echo $photo->id; ?>">
							<a href="<?php echo $photo->getPermalink();?>" title="<?php echo $this->lib->html('string.escape', $photo->title); ?>">
								<i data-photo-list-item-image style="background-image: url(<?php echo $photo->getSource('square'); ?>);"></i>
								<img data-photo-list-item-cover src="<?php echo $photo->getSource('square'); ?>" />
							</a>
						</li>
						<?php } ?>
					</ul>

					<?php if ($total > $limit) { ?>
					<div class="t-lg-mt--md">
						<a href="javascript:void(0);" class="btn btn-es-primary btn-block btn-sm" data-es-photos-loadmore data-total="<?php echo $total; ?>" data-current="<?php echo $current; ?>" data-id="<?php echo $album->id;?>">
							<?php echo JText::_('COM_EASYSOCIAL_LOAD_MORE');?>
						</a>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php echo $this->lib->render('module', 'es-photos-sidebar-bottom'); ?>
	</div>
</div>
