EasySocial.require()
.done(function($) {

	// https://github.com/joomla/joomla-cms/issues/475
	// Override if Mootools loaded
	if (typeof MooTools != 'undefined' ) {
		var mHide = Element.prototype.hide;
		var mShow = Element.prototype.show;
		var mSlide = Element.prototype.slide;

		Element.implement({
			hide: function () {
				if (this.hasClass("mootools-noconflict")) {
					return this;
				}
				mHide.apply(this, arguments);
			},

			show: function (v) {
				if (this.hasClass("mootools-noconflict")) {
					return this;
				}
				mShow.apply(this, v);
			},

			slide: function (v) {
				if (this.hasClass("mootools-noconflict")) {
					return this;
				}
				mSlide.apply(this, v);
			}
		});
	};

	var showcase = $('[data-es-ads-showcase]');
	var adslink = $('[data-module-ads-link]');

	if (window.adseen === undefined) {
		window.adseen = [];
	}

	// When there is only 1 ads published.
	var item = showcase.find('.item.active');
	var id = item.data('id');

	EasySocial.ajax('site/controllers/ads/view', {
		"id" : id
	}).done(function() {
		window.adseen.push(id);
	});

	// When there is more than 1 ads published.
	showcase.on('slid.bs.carousel', function() {
		var item = $(this).find('.active');
		var id = item.data('id');

		// Prevent from multiple impressions given when carousel rotated. #4591
		if ($.inArray(id, window.adseen) !== -1) {
			return;
		}

		EasySocial.ajax('site/controllers/ads/view', {
			"id" : id
		}).done(function() {
			window.adseen.push(id);
		});
	});

	// Prev and Next button
	$('a[data-bp-slide="prev"]').click(function() {
		showcase.carousel('prev');
	});
	$('a[data-bp-slide="next"]').click(function() {
		showcase.carousel('next');
	});

	showcase.carousel({
		pause: 'hover'
	});

	adslink.click(function() {
		var item = $(this).parents('[data-module-ads-item]');
		var href = item.data('link');

		EasySocial.ajax('site/controllers/ads/click', {
			"id" : item.data('id')
		});

		window.open(href);
	})
});
