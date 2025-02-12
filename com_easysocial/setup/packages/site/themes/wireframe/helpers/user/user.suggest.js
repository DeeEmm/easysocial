<?php if ($refresh) { ?>
EasySocial.ready(function($) {

	$(document)
		.on('es.friends.add', '[data-es-friends] [data-task="add"]', function(event, button) {

			// Reset refresh time
			if (window.refreshSuggestions) {
				clearTimeout(window.refreshSuggestions);
			}

			// After adding the person, refresh the list after 5 seconds
			window.refreshSuggestions = setTimeout(refreshContent, 5000);
		});

	$(document)
		.on('es.friends.cancel', '[data-es-friends] [data-task="cancel"]', function(event, button) {

			// Reset refresh time
			if (window.refreshSuggestions) {
				clearTimeout(window.refreshSuggestions);
			}

			// Refresh the list after 5 seconds
			window.refreshSuggestions = setTimeout(refreshContent, 5000);			
		});

	function refreshContent() {
		var wrapper = $('[es-suggest-wrapper]');

		var suggestBody = wrapper.find('[es-suggest-body]');
		var loading = wrapper.find('[es-suggest-loading]');

		suggestBody.addClass('hide');
		loading.removeAttr('hidden');

		EasySocial.ajax('site/views/friends/suggestList', {
			"limit": <?php echo $limit; ?>,
			"showMore": <?php echo $showMore ? 'true' : 'false'; ?>
		}).done(function(htmlContent) {
			wrapper.replaceWith(htmlContent);
		})
	}
});
<?php } ?>