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
<div class="es-mobile-filter-slider__item swiper-slide<?php echo $active ? ' is-active' : '';?>"
	data-es-sly-filter-group
	data-es-sly-item
	data-es-swiper-filter-group
	data-es-swiper-item
	data-type="<?php echo $target;?>"
	<?php if ($dialog) { ?>
	data-dialog
	<?php } ?>
	<?php if ($attributes) { ?>
	<?php echo $attributes; ?>
	<?php } ?>
>
	<a href="<?php echo $link; ?>" class="<?php echo $class; ?>">
		<?php echo $icon ? '<i class="' . $icon . '"></i>&nbsp;' : '';?> <?php echo JText::_($title);?>
	</a>
</div>
