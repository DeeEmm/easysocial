
EasySocial.require()
.library('gmaps')
.done(function($) {

	var renderMap = function() {

		EasySocial.ajax('admin/controllers/easysocial/getCountries', {
		}).done(function(countries, content) {

			var newContent = $(content);

			// Render the map first
			var map = new $.GMaps({
									div: '#user-locations',
									zoom: 1,

									// Default latitude and longitude
									lat: -12.043333,
									lng: -77.028333
								});

			$.each(countries, function(index, value) {
				$.GMaps.geocode({
					"address": value,
					"callback": function(results, status) {

						if (status == 'OK') {

							var country = results[0].formatted_address;
							var latlng = results[0].geometry.location;

							var tableRow = $('[data-country="' + country + '"]');
							var count = tableRow.find('[data-counter]').text();

							$(newContent)
								.find('[data-stat-country="' + value + '"]')
								.html(country);

							map.addMarker({
								"lat": latlng.lat(),
								"lng": latlng.lng(),
								"infoWindow": {
									"content": '<b>' + country + '</b> (' + count + ')'
								}
							});
						}
					}
				});
			});

			$('[data-map-table-wrapper]').html(newContent);

		});

	};

	// Render map when the page loads
	renderMap();
});
