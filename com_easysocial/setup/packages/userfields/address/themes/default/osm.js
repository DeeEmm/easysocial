EasySocial.require().script('apps/fields/user/address/osm').done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Address.Osm', {
		id: <?php echo $field->id; ?>,
		latitude: <?php echo !empty($value->latitude) ? $value->latitude : '""'; ?>,
		longitude: <?php echo !empty($value->longitude) ? $value->longitude : '""'; ?>,
		address: '<?php echo addslashes($value->toString()); ?>',
		zoom: <?php echo !empty($value->zoom) ? $value->zoom : 2; ?>,
		required: <?php echo $required; ?>,
		mapElementId: 'addressfield-map-<?php echo $field->id; ?>',
		staticMap: <?php echo (int) $params->get('static_maps', 0); ?>
	});
});
