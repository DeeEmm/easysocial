EasySocial.require()
.library('sly')
.script('site/avatar/avatar', 'site/cover/cover')
.done(function($){

	$('[data-cover]').implement(EasySocial.Controller.Cover, {
		"uid": "<?php echo $page->id;?>",
		"type": "page"
	});

	$('[data-avatar]').implement(EasySocial.Controller.Avatar, {
		"uid": "<?php echo $page->id;?>",
		"type": "page"
	});

	<?php if ($this->isMobile()) { ?>
	var navItem = $('[data-es-nav-item]');
	var activeIdx = 0;

	$.each(navItem, function(idx, item) {
		if ($(item).hasClass('is-active')) {
			activeIdx = idx;
		}
	})

	// Activate sly animation
	$('[data-mobile-sly-nav]').sly({
		horizontal: 1,
		itemNav: 'basic',
		smart: 1,
		activateOn: 'click',
		mouseDragging: 1,
		touchDragging: 1,
		releaseSwing: 1,
		activeClass: 'is-active',
		startAt: activeIdx,
		scrollBy: 1,
		speed: 300,
		elasticBounds: 0,
		dragHandle: 1,
		dynamicHandle: 1,
		clickBar: 1
	});
	<?php } ?>

});