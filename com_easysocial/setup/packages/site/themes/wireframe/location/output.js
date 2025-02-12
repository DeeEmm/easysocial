<?php
/**
* @package 		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license 		Proprietary Use License http://stackideas.com/licensing.html
* @author 		Stack Ideas Sdn Bhd
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
EasySocial.require()
	.script( 'location' )
	.done(function($){

		$( '.mapItem' )
			.implement(
				EasySocial.Controller.Location.Map,
				{
					locations: [{
						'latitude'	: '<?php echo $location->latitude;?>',
						'longitude'	: '<?php echo $location->longitude;?>',
						'address'	: '<?php echo $location->address;?>'
					}],
					mapType		: '<?php echo $this->config->get( 'location_map_type' );?>',
					width 		: '<?php echo $this->config->get( 'location_map_flash_width' );?>',
					height 		: '<?php echo $this->config->get( 'location_map_flash_height' );?>',
					language	: '<?php echo $this->config->get( 'location_map_lang' );?>',
					zoom 		: '<?php echo $this->config->get( 'location_map_flash_defaultzoom' );?>',
					maxZoom		: '<?php echo $this->config->get( 'location_map_flash_maxzoom' );?>',
					minZoom		: '<?php echo $this->config->get( 'location_map_flash_minzoom' );?>',
					showTip		: false
				},
				function(){
			});
	});
