EasySocial.require()
.library('sly')
.done(function($) {
	var slySlider = $('[data-sly-slider]');

	if (slySlider.length < 1) {
		return;
	}

	// get the slider dive width
	var sliderWidth = slySlider.outerWidth();

	var slyItems = $('[data-slider-item]');

	slyItems.each(function() {
		var item = $(this);
		item.css('width', sliderWidth + 1);
	});

	// Activate sly animation
	slySlider.sly({
		horizontal: 1,
		itemNav: 'centered',
		smart: 1,
		activateOn: 'click',
		mouseDragging: 0,
		touchDragging: 0,
		releaseSwing: 1,
		activeClass: 'is-active',
		startAt: 0,
		scrollBy: 1,
		speed: 300,
		elasticBounds: 1,
		dragHandle: 1,
		dynamicHandle: 1,
		clickBar: 1,
		pagesBar: slySlider.find('.es-slider-pages'),
		activatePageOn: 'click'

	});

	slySlider.sly('on', 'load move', function() {
		slySlider.removeClass('is-end-left is-end-right');

		if (this.pos.cur == this.pos.start) {
			slySlider.addClass('is-end-left');
		}

		if (this.pos.cur == this.pos.end) {
			slySlider.addClass('is-end-right');
		}
	});
});