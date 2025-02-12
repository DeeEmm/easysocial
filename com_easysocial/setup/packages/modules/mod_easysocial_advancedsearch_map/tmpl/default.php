<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-advancedseach-map <?php echo $lib->getSuffix();?>" data-mod-advancedsearch-map style="padding-bottom: 5px;">
	<div class="es-locations" data-location-base data-provider="<?php echo $config->get('location.provider'); ?>" >

		<div id="mapmodule" class="es-location-map <?php echo 'is-' . $config->get('location.provider'); ?> <?php echo $mapItems ? 'has-data' : 'is-empty'; ?>" style="padding-top: 45%">
		</div>
		<input type="hidden" name="source" data-location-source value="<?php echo $mapItems ? FD::string()->escape(json_encode($mapItems)) : ''; ?>" />
	</div>
</div>
