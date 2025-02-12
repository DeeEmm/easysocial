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
<dialog>
	<width>500</width>
	<height>500</height>
	<selectors type="json">
	{
		"{recaptureButton}"  : "[data-recapture-button]",

		"{saveButton}"  : "[data-save-button]",
		"{preview}"  : "[data-photo-camera-preview]",

		"{capture}": "[data-capture-button]",
		"{canvas}": "[data-webcam-canvas]",
		"{video}": "[data-webcam-video]"
	}
	</selectors>
	<bindings type="javascript">
	{
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_TAKE_PHOTO'); ?></title>
	<content>
		<video autoplay style="width: 100%;" data-webcam-video></video>
		<canvas style="display:none;" data-webcam-canvas></canvas>
		<img data-photo-camera-preview class="es-photo-camera-preview t-hidden" />
		<input name="photo_filename" type="hidden" data-photo-filename />
		<input name="uid" type="hidden" value="<?php echo $uid; ?>" data-photo-uid />
	</content>
	<buttons>
		<button data-recapture-button type="button" class="btn btn-es-default btn-sm t-hidden"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_RECAPTURE'); ?></button>
		<button data-capture-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_CAPTURE'); ?></button>
		<button data-save-button type="button" class="btn btn-es-primary btn-sm t-hidden"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SAVE_PHOTO'); ?></button>
	</buttons>
</dialog>
