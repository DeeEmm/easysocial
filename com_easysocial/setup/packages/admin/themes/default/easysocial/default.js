EasySocial.require()
.done(function($) {

	// Fix chart plot not showing. #1712
	setTimeout(function() {
		var activeTab = $('.active[data-form-tabs]').data('item');
		var contents = $('[data-dashbooard-content-tab]').children();

		activeTab = activeTab + '-tabs';

		$.each(contents, function() {
			var tab = $(this);

			if (tab.attr('id') !== activeTab) {
				tab.removeClass('active');
			}
		})
	}, 210);
});
