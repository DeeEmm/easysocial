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
<?php
	$hideLocationRemoveButton = (ES::isFromAdmin() && ($this->input->get('view', '', 'cmd') == 'profiles')) ? true : false;
	$fulladdress = !empty($value->address) ? $value->address : $value->toString();
?>
<div class="es-locations" data-location-base>
	<div id="map" class="es-location-map is-maps <?php echo !empty($fulladdress) ? 'has-data' : 'is-empty'; ?>" data-location-map>
		<div>
			<div class="es-location-map-image" data-location-map-image></div>
			<div class="es-location-map-actions">
				<button class="btn btn-es-primary-o btn-sm es-location-detect-button" type="button" data-location-detect><i class="fas fa-search-location"></i> <?php echo JText::_('COM_EASYSOCIAL_DETECT_MY_LOCATION', true); ?></button>
			</div>
		</div>
	</div>

	<div class="es-location-form es-field-location-form has-border" data-location-form>
		<div class="es-location-textbox" data-location-textbox data-language="<?php echo ES::user()->getLocationLanguage(); ?>">
			<label for="<?php echo $inputName;?>-address" class="t-hidden">Address</label>
			<input id="<?php echo $inputName;?>-address" type="text" placeholder="<?php echo JText::_('PLG_FIELDS_ADDRESS_SET_A_LOCATION'); ?>" autocomplete="off" data-location-textfield disabled <?php echo !empty($fulladdress) ? 'value="' . $fulladdress . '"' : ''; ?> />
			<div class="es-location-autocomplete has-shadow is-sticky" data-location-autocomplete>
				<b><b></b></b>
				<div class="es-location-suggestions" data-location-suggestions>
				</div>
			</div>
		</div>
		<div class="es-location-buttons<?php echo ($hideLocationRemoveButton) ? ' hide' : '';?>">
			<a class="es-location-detect-icon" href="javascript: void(0);" data-location-detect><i class="fas fa-search-location"></i></a>
			<a class="es-location-remove-button" href="javascript: void(0);" data-location-remove><i class="fa fa-times"></i></a>
		</div>
	</div>

	<input type="hidden" name="<?php echo $inputName; ?>" data-location-source value="<?php echo ES::string()->escape($value->toJson()); ?>" />
</div>


