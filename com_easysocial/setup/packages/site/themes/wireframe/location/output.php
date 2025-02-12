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
<fieldset class="field for-location mapItem" data-id="<?php echo $this->location->id;?>">
	<legend><?php echo JText::_( 'COM_EASYSOCIAL_LOCATION_POSTED_FROM' );?></legend>
	<a class="attach-remove btn btn-mini pull-left removeUserLocation" href="javascript:void(0);">
		<i class="icon-remove"></i>
	</a>
	<div class="map-container">

		<div class="location-map">
			<div class="locationMap" style="width: 100%; height:200px;"></div>
		</div>

		<a href="#" class="btn btn-small pull-right">View Larger Map</a>

		<span class="location-address small locationAddress"><?php echo $this->location->address; ?></span>
	</div>
</fieldset>
