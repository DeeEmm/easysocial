
EasySocial.ready(function($) {

	$('[data-toggle-gmaps-secure]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		$('[data-google-maps-secure]').toggleClass('t-hidden', !checked);
		$('[data-google-maps-normal]').toggleClass('t-hidden', checked);
	});

	var updateLocationPanels = function () {
		var service = $('[data-location-places]').val();

		$('[data-location-service]').addClass('t-hidden');
		$('[data-location-service=' + service + ']').removeClass('t-hidden');
		$('[data-google-api]').toggleClass('t-hidden', service == 'osm');

		$('[data-location-help]').addClass('t-hidden');

		if (service == 'osm') {
			$('[data-location-help]').removeClass('t-hidden');
		}
	}

	updateLocationPanels();

	$(document)
		.on('change.location.service', '[data-location-places]', function() {
			updateLocationPanels();
		});
});
