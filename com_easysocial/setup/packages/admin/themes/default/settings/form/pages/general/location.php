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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_LOCATION_GENERAL', '', '/administrators/configuration/location-services-settings'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'location.provider', $this->config->get('location.provider'), [
								['value' => 'foursquare', 'text' => 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_FOURSQUARE'],
								['value' => 'places', 'text' => 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_GOOGLE_PLACES'],
								['value' => 'maps', 'text' => 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_GOOGLE_MAPS'],
								['value' => 'osm', 'text' => 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_OPEN_STREET_MAP']
							], '', ['data-location-places']); ?>

						<div data-location-help class="help-block <?php echo $this->config->get('location.provider') == 'osm' ? '' : 't-hidden';?>">
							<?php echo JText::_('When using OSM Map, maps in the activity stream are always displayed inline instead of a popup');?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LOCATION_SETTINGS_PROXIMITY_SEARCH_UNIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'general.location.proximity.unit', $this->config->get('general.location.proximity.unit'), array(
								array('value' => 'mile', 'text' => 'COM_EASYSOCIAL_LOCATION_SETTINGS_IN_MILES'),
								array('value' => 'km', 'text' => 'COM_EASYSOCIAL_LOCATION_SETTINGS_IN_KILOMETERS')
							), ''); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel" data-google-api>
			<?php echo $this->html('panel.heading', 'COM_ES_LOCATION_SETTINGS_SERVICE_PROVIDER_GOOGLE_MAPS_REQUIRED', '', '/administrators/configuration/location-services-settings#googlemaps'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'location.maps.secure.api', 'COM_ES_LOCATION_SETTINGS_GOOGLE_MAPS_USE_SECURE_API', '', 'data-toggle-gmaps-secure'); ?>

				<div class="form-group <?php echo $this->config->get('location.maps.secure.api') ? 't-hidden' : ''; ?>" data-google-maps-normal>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_GOOGLE_MAPS_API'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'location.maps.api', $this->config->get('location.maps.api')); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('location.maps.secure.api') ? '' : 't-hidden'; ?>" data-google-maps-secure>
					<?php echo $this->html('panel.label', 'COM_ES_LOCATION_SETTINGS_GOOGLE_MAPS_API_BROWSER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'location.maps.secure.browser', $this->config->get('location.maps.secure.browser')); ?>
					</div>
				</div>
				<div class="form-group <?php echo $this->config->get('location.maps.secure.api') ? '' : 't-hidden'; ?>" data-google-maps-secure>
					<?php echo $this->html('panel.label', 'COM_ES_LOCATION_SETTINGS_GOOGLE_MAPS_API_SERVER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'location.maps.secure.server', $this->config->get('location.maps.secure.server')); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel t-hidden" data-location-service="foursquare">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_FOURSQUARE', '', '/administrators/configuration/location-services-settings#foursquare'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.textbox', 'location.foursquare.clientid', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_FOURSQUARE_CLIENT_ID'); ?>
				<?php echo $this->html('settings.textbox', 'location.foursquare.clientsecret', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_FOURSQUARE_CLIENT_SECRET'); ?>
			</div>
		</div>

		<div class="panel t-hidden" data-location-service="places">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_GOOGLE_PLACES', '', '/administrators/configuration/location-services-settings#googleplaces'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.textbox', 'location.places.api', 'COM_EASYSOCIAL_LOCATION_SETTINGS_SERVICE_PROVIDER_GOOGLE_PLACES_API'); ?>
			</div>
		</div>
	</div>
</div>
