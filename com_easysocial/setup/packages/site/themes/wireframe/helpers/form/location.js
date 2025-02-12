<?php if ($this->config->get('location.provider') == 'osm') { ?>
	EasySocial.require()
	.script("site/locations/osm")
	.done(function($){
		$('<?php echo $selector; ?>').addController(EasySocial.Controller.OSM);
	});
<?php } else { ?>
	EasySocial.require()
	.script("site/locations/gmaps")
	.done(function($){
		$('<?php echo $selector; ?>').addController(EasySocial.Controller.Gmaps);
	});
<?php } ?>
