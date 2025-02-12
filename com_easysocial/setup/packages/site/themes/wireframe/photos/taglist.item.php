<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div data-photo-tag-list-item
	 data-photo-tag-id="<?php echo $tag->id; ?>"
	 <?php if (!empty($tag->uid)) { ?>
	 data-photo-tag-uid="<?php echo $tag->uid; ?>"
	 <?php } ?>
	 data-photo-tag-type="<?php echo $tag->type; ?>"
	 class="es-photo-tag-list-item es-photo-tag-<?php echo $tag->type; ?>">
	<i class="fa fa-eye"></i>
	<a href="javascript: void(0);"><span><?php echo $tag->label; ?></span></a>
	<?php if ($tag->deleteable()) { ?>
	<b data-photo-tag-remove-button data-photo-tag-id="<?php echo $tag->id; ?>"><i class="fa fa-times"></i> <span><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_TAG_REMOVE_TAG'); ?></span></b>
	<?php } ?>
</div>
