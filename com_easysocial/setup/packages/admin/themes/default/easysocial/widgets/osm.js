
EasySocial.require()
.library('leaflet')
.done(function($) {

	var renderMap = function() {

		EasySocial.ajax('admin/controllers/easysocial/getCountries', {
		}).done(function(countries, content) {

			var newContent = $(content);

			// Render the map first
			self.osm = L.map('user-locations', {
				zoom: 15
			});

			self.osm.fitWorld();
			self.osm.setZoom(1);

			// L.tileLayer.provider('Wikimedia').addTo(self.osm);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(self.osm);

			var latlng = {
						lat: parseFloat(-12.043333),
						lng: parseFloat(-77.028333)
					}

			// convert it to json string
			var countriesString = JSON.stringify(countries);

			EasySocial.ajax('admin/controllers/easysocial/getLocations', {
				query: countriesString,
				type: 'dashboard'
			})
			.done(function(locations) {
				$.each(locations, function(index, value) {
					var country = value.formatted_address;
					var tableRow = $('[data-country="' + country + '"]');
					var count = tableRow.find('[data-counter]').text();

					$(newContent)
						.find('[data-stat-country="' + value + '"]')
						.html(country);

					var latlng = [parseFloat(value.latitude), parseFloat(value.longitude)]

					L.marker(latlng).addTo(self.osm);
				});
			});

			$('[data-map-table-wrapper]').html(newContent);
		});

	};

	// Render map when the page loads
	renderMap();
});
