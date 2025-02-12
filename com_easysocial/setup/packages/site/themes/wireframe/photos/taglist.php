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
<div class="es-photo-tag-list" data-photo-tag-list>
	<label><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_IN_THIS_PHOTO'); ?></label>

	<div class="es-photo-tag-list-item-group<?php echo (empty($tags)) ? ' empty-tags' : ''; ?>" data-photo-tag-list-item-group>
		<?php if ($tags) { ?>
			<?php foreach ($tags as $tag) { ?>
				<?php echo $this->includeTemplate('site/photos/taglist.item', array('tag' => $tag)); ?>
			<?php } ?>
		<?php } else { ?>
			<span class="empty-tags-hint">
				<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_TAGS_EMPTY_HINT'); ?>
			</span>
		<?php } ?>
	</div>

	<?php if ($lib->taggable()) { ?>
	<div class="btn btn-es-default-o btn-media es-photo-tag-button" data-photo-tag-button="enable">
		<a href="javascript: void(0);"><?php echo JText::_("COM_EASYSOCIAL_TAG_PHOTO"); ?></a>
	</div>
	<?php } ?>
</div>
