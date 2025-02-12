EasySocial.ready(function($) {

	$('[data-digest-subscribe]').on('click', function() {

		var item = $(this);

		var interval = item.data('interval');
		var id = item.data('id');
		var type = item.data('type');

		EasySocial.ajax('site/views/subscriptions/digest', {
			"uid": id,
			"utype": type,
			"interval": interval
		}).done(function(status) {

			if (status) {

				// remove previous active state.
				$('[data-digest-subscribe].active i').removeClass('fa-check -circle-o t-icon--success')
													.addClass('fa-envelope-o t-text--muted');
				$('[data-digest-subscribe].active').removeClass('active');

				// add current selected active
				$(item).find('i').removeClass('fa-envelope-o t-text--muted')
								.addClass('fa-check -circle-o t-icon--success');
				$(item).addClass('active');
			}

		}).fail(function(msg) {

			EasySocial.dialog({
				"content": msg
			});

		});

	});
});
