<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($this->config->get('photos.import.exif') && $exif) { ?>
<div class="es-photo-exif t-lg-mt--lg t-lg-mb--lg">
	<div class="es-photo-exif__title"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_EXIF_INFO'); ?></div>
	<div class="es-photo-exif__data">
		<?php foreach ($exif as $item) { ?>
		<div class="es-photo-exif__item">
			<i class="es-photo-exif__icon es-exif-icon es-exif es-exif-icon es-exif-<?php echo $item->class;?>"></i>
			<?php echo $item->value; ?>
		</div>
		<?php } ?> 
	</div>
</div>
<?php } ?>