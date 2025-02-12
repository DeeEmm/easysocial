<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-album-form">
	<div class="es-album-form-fields <?php echo ($album->core) ? 'core-album' : ''; ?>">

		<input class="es-album-title-field" type="text" value="<?php echo $this->html('string.escape', $album->_('title')); ?>" placeholder="<?php echo JText::_("COM_EASYSOCIAL_ALBUMS_ENTER_ALBUM_TITLE"); ?>" autocomplete="off"
			data-album-title-field
			<?php echo ($album->core) ? 'readonly' : ''; ?>
		/>

		<textarea class="es-album-caption-field" placeholder="<?php echo JText::_("COM_EASYSOCIAL_ALBUMS_ENTER_ALBUM_DESCRIPTION"); ?>" data-album-caption-field <?php echo $this->isMobile() ? 'data-album-description-field' : ''; ?> <?php echo ($album->core) ? 'readonly' : ''; ?>><?php echo $this->html('string.escape', $album->_('caption')); ?></textarea>

		<div class="es-album-cover-field <?php echo !$album->hasCover() ? ' no-cover ' : '';?>"
			<?php if ($album->hasCover()) { ?>
			style="background-image: url(<?php echo $album->getCover( 'thumbnail' ); ?>);"
			<?php } ?>
			data-album-cover-field>
			<i class="fa fa-image"></i>
		</div>

		<?php if ($lib->canSetPassword() || $album->hasPassword()) { ?>
			<div class="es-album-password-field" data-album-meta-field>
				<input type="text" class="es-album-password-field__input" name="es-album-password-field" value="<?php echo $album->password; ?>" placeholder="<?php echo JText::_("COM_ES_ALBUMS_SET_PASSWORD"); ?>" autocomplete="off" data-album-password-field/>
			</div>
		<?php } ?>

		<div class="es-album-meta-field sentence" data-album-meta-field>

			<?php if (!$album->core && $this->config->get('photos.location')) { ?>
			<div class="es-album-location words <?php echo $album->location ? 'has-data' : '';?>" data-album-location>
				<i class="fa fa-map-marker-alt"></i>
				<span class="with-data" data-album-location-caption data-es-toggle="dropdown">
					<?php if ($album->location) { ?>
						<span>
						<?php echo $album->location->getAddress(); ?>
						</span>
					<?php } ?>

					<i class="fa fa-times es-album-location-remove" data-album-location-remove></i>
				</span>

				<span class="without-data" data-album-addLocation-button data-es-toggle="dropdown">
					<?php echo JText::_("COM_EASYSOCIAL_ADD_LOCATION"); ?>
				</span>

				<div class="es-album-location-form dropdown-menu dropdown-static dropdown-arrow-topleft" data-album-location-form>
					<?php echo $this->html('form.location', $album->location, '', 'album'); ?>
				</div>
			</div>
			<?php } ?>

			<?php if ($album->hasDate()) { ?>
			<div data-album-date class="es-album-date words has-data">
				<i class="far fa-clock"></i>
				<span class="with-data" data-album-date-caption data-es-toggle="dropdown">
					<?php echo $this->html( 'string.date', $album->getAssignedDate() , "COM_EASYSOCIAL_ALBUMS_DATE_FORMAT", $album->hasAssignedDate() ? false : true); ?>
				</span>
				<span data-album-addDate-button
					  data-es-toggle="dropdown"
					  class="without-data">
					<?php echo JText::_("COM_EASYSOCIAL_ADD_DATE"); ?>
				</span>
				<div class="es-album-date-form dropdown-menu dropdown-static dropdown-arrow-topright">
					<?php echo $this->html('grid.dateform', 'date-form', $album->getAssignedDate(), '', '', $album->hasAssignedDate() ? false : true); ?>
				</div>
			</div>
			<?php } ?>

		</div>

		<?php if ($lib->hasPrivacy()) { ?>
		<div data-album-privacy class="es-album-privacy solid">
			<?php echo $privacy->form($album->id, SOCIAL_TYPE_ALBUM, $album->uid, 'albums.view', $privacyUseHtml, null, array(), array('iconOnly' => true)); ?>
		</div>
		<input type="hidden" name="original_privacy" value="<?php echo $album->access; ?>" data-privacy-ori-hidden />
		<?php } ?>
	</div>
</div>
