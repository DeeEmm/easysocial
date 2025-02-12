<?php if ($this->isMobile()) { ?>
EasySocial.require()
.library('swiper')
.done(function($){
	var navigationBar = $('[data-mobile-swiper-nav="<?php echo $uniqid; ?>"]');
	var navItem = navigationBar.find('[data-es-swiper-item]');
	var swiperContainer = navigationBar.find('[data-mobile-swiper-container]');

	var activeIdx = 0;

	$.each(navItem, function(idx, item) {
		if ($(item).hasClass('is-active')) {
			activeIdx = idx;
		}
	})

	var swiper = new Swiper(swiperContainer, {
		init: false,
		slidesPerView: 'auto',
		spaceBetween: 0,
		initialSlide: activeIdx, //slide number which you want to show-- 0 by default
		resistance: false,
		freeMode: false,
		freeModeMomentum: false,
		freeModeMomentumBounce: false,
		centerInsufficientSlides: true,
		freeModeSticky: true,
		centeredSlides: false
	});

	// Simulate centeredSlides #3492#note_114545
	swiper.on('init', function() {
		swiper.slideTo(activeIdx - 1);
	});

	swiper.on('touchMove', function() {
		navigationBar.removeClass('is-end-left is-end-right');
		// swiper.allowSlidePrev = true;
		// swiper.allowSlideNext = true;
		swiper.noSwiping = false;

		if (swiper.isBeginning) {
			navigationBar.addClass('is-end-left');
			swiper.noSwiping = true;
		}

		if (swiper.isEnd) {
			navigationBar.addClass('is-end-right');
			swiper.noSwiping = true;
		}
	});

	navItem.on('click', function(el, ev) {
		var index = swiper.clickedIndex;
		var slide = swiper.clickedSlide;

		navItem.removeClass('is-active');
		$(slide).addClass('is-active');

		// Simulate centeredSlides #3492#note_114545
		swiper.slideTo(index - 1);
	});

	swiper.init();
});
<?php } ?>
