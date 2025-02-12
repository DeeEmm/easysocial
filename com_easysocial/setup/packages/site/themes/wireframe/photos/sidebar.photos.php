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
<?php foreach ($photos as $photo) { ?>
<li class="es-thumb<?php echo $photo->id == $id ? ' active' : '';?><?php echo $photo->isFeatured() ? ' featured' : '';?>" data-photo-list-item data-photo-id="<?php echo $photo->id; ?>">
	<a href="<?php echo $photo->getPermalink();?>" title="<?php echo $this->html('string.escape', $photo->title); ?>">
		<i data-photo-list-item-image style="background-image: url(<?php echo $photo->getSource('square'); ?>);"></i>
		<img data-photo-list-item-cover src="<?php echo $photo->getSource('square'); ?>" />
	</a>
</li>
<?php } ?>