EasySocial.require()
.script("site/photos/browser")
.done(function($){

	$("[data-photo-browser=<?php echo $uuid; ?>]").addController("EasySocial.Controller.Photos.Browser");

	<?php if ($total > $limit) { ?>
	$('[data-es-photos-loadmore]').on('click', function() {
		var button = $(this);
		var id = $(this).data('id');
		var controller = $('[data-photo-browser=<?php echo $uuid; ?>]').controller();

		var total = button.data('total');
		var current = button.data('current');

		button.addClass('is-loading');

		EasySocial.ajax('site/views/photos/loadSidebarPhotos', {
			"albumId": id,
			"current": current
		}).done(function(photos, currentTotal) {
			button.removeClass('is-loading');
			button.data('current', currentTotal);

			if (currentTotal >= total) {
				button.remove();
			}

			$('[data-photo-list-item-group]').append(photos);

			controller.setLayout();
		});
	});
	<?php } ?>
});
