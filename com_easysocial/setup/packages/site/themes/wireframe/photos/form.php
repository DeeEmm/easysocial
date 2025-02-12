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
<div class="es-photo-form" data-photo-form>

	<div class="es-photo-form-fields">
		<input class="es-photo-title-field" type="text" placeholder="<?php echo JText::_("COM_EASYSOCIAL_ENTER_PHOTO_TITLE"); ?>" value="<?php echo $this->html( 'string.escape' , $photo->title ); ?>" data-photo-title-field />

		<textarea class="es-photo-caption-field" data-photo-caption-field <?php echo $this->isMobile() ? 'data-photo-description-field' : ''; ?> placeholder="<?php echo JText::_("COM_EASYSOCIAL_ENTER_PHOTO_CAPTION"); ?>"><?php echo $photo->caption; ?></textarea>

		<div data-photo-meta-field class="es-photo-meta-field sentence">
			<?php if ($this->config->get('photos.location')) { ?>
			<div data-photo-location class="es-photo-location words <?php echo $photo->getLocation() ? 'has-data' : '';?>">
				<i class="fa fa-map-marker-alt"></i>
				<span data-photo-location-caption data-es-toggle="dropdown" class="with-data">
					<?php if ($photo->getLocation()) { ?>
						<?php echo $photo->getLocation()->getAddress(); ?>
					<?php } ?>

					<i class="fa fa-times es-photo-location-remove" data-photo-location-remove></i>
				</span>

				<span data-photo-addLocation-button data-es-toggle="dropdown" class="without-data">
					<?php echo JText::_("COM_EASYSOCIAL_ADD_LOCATION"); ?>
				</span>

				<div class="es-photo-location-form dropdown-menu dropdown-static dropdown-arrow-topleft" data-photo-location-form>
					<?php echo $this->html('form.location', $photo->getLocation(), '', 'album'); ?>
				</div>
			</div>
			<?php } ?>

			<div data-photo-date class="es-photo-date words has-data">
				<i class="far fa-clock"></i>
				<span data-photo-date-caption
					  data-es-toggle="dropdown"
					  class="with-data">
					<?php echo $this->html( 'string.date', $photo->getAssignedDate() , "COM_EASYSOCIAL_PHOTOS_DATE_FORMAT", $photo->hasAssignedDate() ? false : true); ?>
				</span>
				<span data-photo-addDate-button
					  data-es-toggle="dropdown"
					  class="without-data">
					<?php echo JText::_("COM_EASYSOCIAL_ADD_DATE"); ?>
				</span>
				<div class="es-photo-date-form dropdown-menu dropdown-static dropdown-arrow-topright">
					<?php echo $this->html('grid.dateform', 'date-form', $photo->getAssignedDate(), '', '', $photo->hasAssignedDate() ? false : true); ?>
				</div>
			</div>
		</div>

		<?php if ($lib->hasPrivacy()) { ?>
		<div data-photo-privacy class="es-photo-privacy">
			<?php echo $privacy->form($photo->id, SOCIAL_TYPE_PHOTO, $photo->uid, 'photos.view', false, null, array(), array('iconOnly' => true)); ?>
		</div>
		<?php } ?>

	</div>
</div>
