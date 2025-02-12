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
?>
<div>
	<?php if (!empty($albums)) { ?>
	<div class="es-cards es-cards--2">
		<?php foreach ($albums as $album) { ?>
			<?php $albumPhotos = isset($photos[$album->id]) ? $photos[$album->id] : array(); ?>
			<?php echo $this->includeTemplate('site/albums/items/item', array('album' => $album, 'photos' => $albumPhotos, 'verifyPassword' => ES::albums($album->uid, $album->type, $album->id)->verifyPassword(), 'hasPassword' => $album->hasPassword(), 'isMine' => $album->isMine())); ?>
		<?php } ?>
	</div>
	<?php } ?>

	<?php echo $pagination->getListFooter('site');?>

	<?php if (!$albums) { ?>
		<div class="is-empty">
			<div class="o-empty es-island">
				<div class="o-empty__content">
					<i class="o-empty__icon far fa-images"></i>
					<div class="o-empty__text"><?php echo JText::_('COM_EASYSOCIAL_NO_ALBUM_AVAILABLE_' . strtoupper($filter)); ?></div>

					<?php if ($lib->canCreateAlbums()) { ?>
					<div class="o-empty__action">
						<a class="btn btn-es-primary btn-large" href="<?php echo $lib->getCreateLink();?>"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_CREATE_ALBUM'); ?></a>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
