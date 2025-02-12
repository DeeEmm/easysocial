<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="form-location" data-locationForm>

	<h5>
		<i class="icon-es-map"></i> <?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_SHARE_LOCATION' );?>
	</h5>
	<hr />

	<div class="location-address">
		<input type="text" class="input input-xlarge" name="address"
			placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_ADDRESS_PLACEHOLDER' , true );?>"
			data-locationForm-input />

		<a href="javascript:void(0)" class="btn btn-es-inverse ml-5"
			data-es-provide="tooltip"
			data-original-title="<?php echo JText::_( 'Let us detect your current location' , true );?>"
			data-placement="bottom"
			data-locationForm-autodetect
		><i class="icon-es-marker"></i> <?php echo JText::_( 'COM_EASYSOCIAL_DETECT_LOCATION_BUTTON' );?>
		</a>
	</div>

	<div class="location-coordinates" data-locationForm-coordinates>
		<h5>
			<?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_COORDINATES' ); ?>
			<span class="editCoordinates">( <a href="javascript:void(0);" class="editLink" data-locationForm-edit><?php echo JText::_( 'COM_EASYSOCIAL_EDIT_BUTTON' ); ?></a> )</span>

			<span class="pull-right">
				<a href="javascript:void(0);" class="btn btn-es-danger btn-small removeLocation" data-locationForm-clear><?php echo JText::_( 'COM_EASYSOCIAL_REMOVE_LOCATION_BUTTON' );?></a>
			</span>
		</h5>
		<hr class="separator" />

		<div class="row mt-10">
			<div class="col-md-6">
				<span><?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_LATITUDE' ); ?></span>:
				<span class="coordinates-info" data-locationForm-latitudeDisplay></span>
				<input type="text" name="latitude" value="" class="input-mini" data-locationForm-latitude/>
			</div>

			<div class="col-md-6">
				<span><?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_LONGITUDE' ); ?></span>:

				<span class="coordinates-info" data-locationForm-longitudeDisplay></span>
				<input type="text" name="longitude" value="" class="input-mini" data-locationForm-longitude />
			</div>
		</div>

		<div class="coordinate-actions row">
			<div class="col-md-12">
				<hr />
				<div class="pull-right">
					<a href="javascript:void(0);" class="btn btn-es cancelButton" data-locationForm-cancel><?php echo JText::_('COM_ES_CANCEL'); ?></a>
					<a href="javascript:void(0);" class="btn btn-es-inverse updateButton" data-locationForm-update><?php echo JText::_('COM_ES_UPDATE'); ?></a>
				</div>
			</div>
		</div>
	</div>


	<div class="location-map locationMapWrapper">
		<div class="map" style="width: 100%; height: 190px;" data-locationForm-map>&nbsp;</div>

		<div class="o-btn-group">
			<a href="javascript:void(0);" target="_blank"><?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_VIEW_LARGER_MAP' ); ?></a>
			<a href="javascript:void(0);" data-locationForm-removeMap><?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_REMOVE_MAP' ); ?></a>
		</div>
	</div>
</div>
