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
<div data-photo-item
	 data-photo-id="<?php echo $photo->id; ?>"
	 class="es-photo-item es-media-item">
	<div class="es-photo">
		<a href="javascript: void(0);">
			<u>
				<b data-mode="cover" data-threshold="<?php echo $this->config->get('photos.layout.threshold'); ?>">
					<img data-photo-image
						 src="<?php echo $photo->getSource('thumbnail'); ?>"
						 data-width="<?php echo $photo->getWidth(); ?>"
						 data-height="<?php echo $photo->getHeight(); ?>"
						 onload="window.ESImage ? ESImage(this) : (window.ESImageList || (window.ESImageList=[])).push(this);" />
				</b>
			</u>
		</a>
	</div>
	<div data-photo-remove-button
		 class="es-photo-remove-button"><i class="fa fa-times"></i> <?php echo JText::_("COM_EASYSOCIAL_STORY_PHOTO_REMOVE"); ?></div>
</div>
