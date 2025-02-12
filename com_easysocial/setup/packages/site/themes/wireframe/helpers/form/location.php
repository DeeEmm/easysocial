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
<div class="es-locations <?php echo $classname; ?>" <?php echo $selectorName; ?> data-language="<?php echo ES::user()->getLocationLanguage(); ?>">
	<div id="map" class="es-location-map <?php echo 'is-' . $this->config->get('location.provider'); ?>" data-location-map>
		<div>
			<img class="es-location-map-image" data-location-map-image />
			<div class="es-location-map-actions">
				<button class="btn btn-es-default-o btn-sm es-location-detect-button" type="button" data-detect-location-button>
					<i class="fa fa-map-marker-alt t-text--danger"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_DETECT_MY_LOCATION', true); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="es-location-form">
		<div class="es-location-textbox">
			<input type="text" class="input-sm form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_WHERE_ARE_YOU_NOW'); ?>" autocomplete="off" data-location-textField disabled />

			<div class="es-location-autocomplete has-shadow is-sticky" data-location-autocomplete>
				<b><b></b></b>
				<div class="es-location-suggestions" data-location-suggestions></div>
			</div>
		</div>
		<div class="es-location-buttons">
			<div class="o-loader o-loader--sm"></div>
			<a class="es-location-remove-button" href="javascript: void(0);" data-location-remove-button>
				<i class="fa fa-times"></i>
			</a>
		</div>
	</div>

	<input type="hidden" name="lat" data-location-lat value="<?php echo $location->latitude; ?>" />
	<input type="hidden" name="lng" data-location-lng value="<?php echo $location->longitude; ?>" />
</div>
