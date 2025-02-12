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
<span data-photo-tag-list>
	<?php if ($lib->taggable()) { ?>
	<div class="o-nav__item">
		<button class="btn btn-photo-popup-nav-item" data-photo-tag-button="enable">
			<i class="fa fa-user-plus"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_TAG_PHOTO'); ?>
		</button>
	</div>
	<?php } ?>

	<div class="o-nav__item">
		<div class="dropdown_" data-item-actions-menu>
			<button class="btn btn-photo-popup-nav-item dropdown-toggle_" data-es-toggle="dropdown">
				<i class="fa fa-users"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_PHOTOS_TAGS'); ?>
			</button>

			<div class="es-photo-tag-list-dropdown dropdown-menu dropdown-static <?php echo !$tags ? 'empty-tags' : '';?>" data-photo-tag-list-item-group>
				<?php if ($tags) { ?>
					<?php foreach ($tags as $tag) { ?>
					<div class="es-photo-tag-list-item es-photo-tag-<?php echo $tag->type; ?>"
						data-photo-tag-list-item
						data-photo-tag-id="<?php echo $tag->id; ?>"
						data-photo-tag-type="<?php echo $tag->type; ?>"
						<?php if (!empty($tag->uid)) { ?>
						data-photo-tag-uid="<?php echo $tag->uid; ?>"
						<?php } ?>
					>
						<i class="fa fa-eye"></i>
						<a href="javascript: void(0);"><span><?php echo $tag->label; ?></span></a>
						<?php if ($tag->deleteable()) { ?>
						<b data-photo-tag-remove-button data-photo-tag-id="<?php echo $tag->id; ?>"><i class="fa fa-times"></i> <span><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_TAG_REMOVE_TAG'); ?></span></b>
						<?php } ?>
					</div>
					<?php } ?>
				<?php } ?>
				<div class="empty-tags-hint t-lg-p--md">
					<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_TAGS_EMPTY_HINT'); ?>
				</div>
			</div>
		</div>
	</div>
</span>
