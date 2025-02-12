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
<div class="es-locations " data-es-location>
	<div class="es-location-map" data-location-map>
		<div>
			<img class="es-location-map-image" data-location-map-image />
			<div class="es-location-map-actions">
				<button class="btn btn-es es-location-detect-button btn-sm" type="button" data-detect-location-button>
					<i class="fa fa-map-marker-alt"></i> <?php echo JText::_('COM_EASYSOCIAL_DETECT_MY_LOCATION', true); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="es-location-form has-border" data-location-form>
		<div class="es-location-textbox" data-location-textbox data-language="<?php echo ES::user()->getLocationLanguage(); ?>">
			<input type="text" class="input-sm form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_WHERE_ARE_YOU_NOW'); ?>" autocomplete="off"
				name="<?php echo $name;?>"
				data-location-textField disabled
			/>

			<div class="es-location-autocomplete has-shadow is-sticky" data-location-autocomplete>
				<b><b></b></b>
				<div class="es-location-suggestions" data-location-suggestions></div>
			</div>
		</div>
		<div class="es-location-buttons">
			<i class="es-loading-icon"></i>
			<a class="es-location-remove-button" href="javascript: void(0);" data-location-remove-button>
				<i class="fa fa-times"></i>
			</a>
		</div>

		<input type="hidden" name="latitude" data-location-lat value="<?php echo $latitude;?>" />
		<input type="hidden" name="longitude" data-location-lng value="<?php echo $longitude;?>" />
	</div>
</div>
