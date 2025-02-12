EasySocial
.require()
.script('site/vendors/prism', 'site/marketplaces/item')
.library('swiper')
.done(function($) {

	$('[data-marketplace-item]').implement(EasySocial.Controller.Marketplaces.Item, {
		callbackUrl: "<?php echo base64_encode($listing->getPermalink(false));?>"
		<?php if ($cluster) { ?>
		,"clusterId": '<?php echo $cluster->id; ?>',
		"clusterType": '<?php echo $cluster->getType(); ?>'
		<?php } ?>
	});

	// Marketplace swiper
	var esMkpGalleryThumbs = new Swiper('[data-gallery-thumbs]', {
		spaceBetween: 10,
		slidesPerView: 4,
		freeMode: true,
		watchSlidesVisibility: true,
		watchSlidesProgress: true,
	});
	var esMkpGalleryTop = new Swiper('[data-gallery-top]', {
		spaceBetween: 0,
		navigation: {
		nextEl: '.swiper-button-next',
		prevEl: '.swiper-button-prev',
		},
		thumbs: {
		swiper: esMkpGalleryThumbs
		}
	});
});
