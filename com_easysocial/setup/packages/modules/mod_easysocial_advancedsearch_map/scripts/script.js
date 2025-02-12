EasySocial.require()
.library('image', 'gmaps', 'leaflet')
.script('https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js')
.done(function($) {
	EasySocial.Controller('Module.Profiles.Maps', {
		defaultOptions: {
			zoom: 2,

			latitude: null,
			longitude: null,
			address: null,

			singleLocation: true,
			staticMap: false,

			required: false,

			ratio: 3,

			'{base}': '[data-location-base]',

			'{map}': '[data-location-map]',
			'{mapImage}': '[data-location-map-image]',

			'{source}': '[data-location-source]',

			view: {
				suggestion: 'maps.suggestion'
			}
		}
	}, function(self, opts, base){ return {

		init: function() {
			if (base.data('provider') === 'osm') {
				self.initOsm();
			} else {
				self.initMaps();
			}
		},

		initMaps: function() {
			var source = {address1:"",address2:"",city:"Kuala Lumpur",state:"",zip:"59200",country:"Malaysia",latitude:"3.120821",longitude:"101.677506",address:"Suite A-5-3, North Point Offices Mid Valley City, No. 1, Medan Syed Putra Utara, Wilayah Persekutuan, 59200 Kuala Lumpur, Malaysia"};

			if (!$.isEmpty(source)) {
				var data = source;

				if (data.latitude && data.longitude) {
					self.navigateDynamic(data.latitude, data.longitude);

					self.base().addClass("has-location");
				}
			}
		},

		initOsm: function() {
			osm = L.map('mapmodule');
			osm.fitWorld();

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				minZoom: 1,
				maxZoom: 19,
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(osm);

			if ($.isEmpty(self.source().val())) {
				return;
			}

			var locations = JSON.parse(self.source().val());

			if (locations.length < 1) {
				return;
			}

			var bounds = new L.LatLngBounds();
			var infoWindow = new Array();

			$.each(locations, function(index, location) {
				var contentString = '<div id="content" style="text-align: center;">' +
							'<div id="siteNotice"></div>' +
							'<div>' + location.avatar + '</div>' +
							'<div>' + location.name + '</div>' +
							'<div>' + location.address + '</div>' +
							'</div>';

				var infoWindow = L.popup().setContent(contentString);
				var marker = L.marker([location.lat, location.lng])
					.addTo(osm)
					.bindPopup(infoWindow)

				marker.addTo(osm);
				bounds.extend(marker.getLatLng());
			});

			osm.fitBounds(bounds, {'maxZoom': 3});
		},

		infoWindows: [],

		renderDynamicMap: function(latitude, longitude) {
			var optsLat = opts.latitude;
			var optsLng = opts.longitude;

			var center = {lat: 40, lng: -100};
			var zoom = 3;

			if (typeof gmap === 'undefined') {
				gmap = new google.maps.Map(document.getElementById('mapmodule'), {
					zoom: zoom,
					center: center,
					streetViewControl: false,
					mapTypeControl: false,
					fullscreenControl: false
				});

			} else {
				gmap.setCenter(40, -100);
			}

			if (!$.isEmpty(self.source().val())) {
				self.generateMarkers();
			}

			gmap.addListener('click', function() {
				for (var i=0;i<self.infoWindows.length;i++) {
					self.infoWindows[i].close();
				}
			});
		},

		generateMarkers: function() {
			var data = JSON.parse(self.source().val());

			if (data.length < 1) {
				return;
			}

			var bounds = new google.maps.LatLngBounds();

			var markers = data.map(function(loc, i) {
				var icon = {
					url: window.es.rootUrl + '/modules/mod_easysocial_advancedsearch_map/assets/images/marker.png', // url
					scaledSize: new google.maps.Size(32, 32), // scaled size
					origin: new google.maps.Point(0,0), // origin
					anchor: new google.maps.Point(16, 32) // anchor
				};

				var contentString = '<div id="content">' +
							'<div id="siteNotice"></div>' +
							'<div>' + loc.avatar + '</div>' +
							'<div>' + loc.name + '</div>' +
							'<div>' + loc.address + '</div>' +
							'</div>';

				var infowindow = new google.maps.InfoWindow({content: contentString, maxWidth: 300});
				self.infoWindows.push(infowindow);
				var position = new google.maps.LatLng(loc.lat, loc.lng);

				var marker = new google.maps.Marker({
					position: position,
					icon: icon,
					infowindow: infowindow
				});

				bounds.extend(position);

				marker.addListener('click', function() {
					hideAllInfoWindows(gmap);
					infowindow.open(gmap, marker);
				});

				marker.setMap(gmap);

				return marker;
			});

			if (markers.length > 0) {
				gmap.fitBounds(bounds);
			}

			var listener = google.maps.event.addListener(gmap, "idle", function() {
				if (gmap.getZoom() > 16) {
					gmap.setZoom(16);
				}

				google.maps.event.removeListener(listener);
			});

			function hideAllInfoWindows(map) {
				markers.forEach(function(marker) {
					marker.infowindow.close(map, marker);
				});
			}

		},

		"{window} resize": $.debounce(function() {

			var data = JSON.parse(self.source().val());

			if (!data.latitude || !data.longitude) {
				return;
			}

			var mapImage = self.mapImage();

			if (mapImage.data("width") !== mapImage.width()) {
				self.navigate(data.latitude, data.longitude);
			}

		}, 250),

		navigateDynamic: function(lat, lng) {
			self.renderDynamicMap(lat, lng);
		}

	}});

	$('[data-location-base]').addController('EasySocial.Controller.Module.Profiles.Maps', {
		latitude: "<?php echo $frmLatitude ? $frmLatitude : ''; ?>",
		longitude: "<?php echo $frmLongitude ? $frmLongitude : ''; ?>"
	});
});
