<?php if ($this->config->get('location.provider') == 'osm') { ?>
	EasySocial.require()
	.script("site/locations/osm")
	.done(function($){
		$('<?php echo $selector; ?>').addController(EasySocial.Controller.OSM, {
			latitude: <?php echo !empty($location->latitude) ? $location->latitude : '""'; ?>,
			longitude: <?php echo !empty($location->longitude) ? $location->longitude : '""'; ?>
		});
	});
<?php } else { ?>
	EasySocial.require()
	.script("site/locations/gmaps")
	.done(function($){
		$('<?php echo $selector; ?>').addController(EasySocial.Controller.Gmaps, {
			latitude: <?php echo !empty($location->latitude) ? $location->latitude : '""'; ?>,
			longitude: <?php echo !empty($location->longitude) ? $location->longitude : '""'; ?>
		});
	});
<?php } ?>
