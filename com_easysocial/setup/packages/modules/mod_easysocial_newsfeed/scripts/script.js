EasySocial.ready(function($) {

	$(document).on("onEasySocialFilterClick", function() {

		var activeFilter = $('[data-filter-wrapper] [data-filter-item].active');

		if (activeFilter === undefined) {
			return false;
		}

		var filterType = activeFilter.data('type');
		var filterId = activeFilter.data('id');

		// clear all the active state in module filter.
		$('[data-es-module-newsfeed] [data-filter-item]').removeClass('active');

		// add active state to the corresponding fiter.
		var filterSelector = '[data-es-module-newsfeed] [data-filter-item][data-type="' + filterType + '"]';

		if (filterId) {
			filterSelector += '[data-id="' + filterId + '"]';
		}

		$(filterSelector).addClass('active');
	});

	$('[data-es-module-newsfeed] [data-mod-filter-create]').on('click', function(event) {
		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/stream/getFilterFormDialog', {"type": "user"})
		});
	});


	$('[data-es-module-newsfeed] [data-filter-item]').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();

		// Find the dashboard controller
		var controller = $('[data-es-dashboard]').controller();

		if (controller === undefined) {
			// dont do anything.
			return false;
		}

		if (controller.clicked) {
			// this mean the filter is running. stop further process.
			return false;
		}

		// all state passed. let continue.

		// Remove all active state
		$('[data-es-module-newsfeed] [data-filter-item]').removeClass('active');

		// Set an active state on the current element
		var element = $(this);
		element.addClass('active');

		var type = $(this).data('type');
		var id = $(this).data('id');
		var filterSelector = '[data-type=' + type + ']';

		if (id !== undefined) {
			filterSelector += '[data-id=' + id + ']';
		}

		var filter = controller.filter('[data-filter-wrapper] ' + filterSelector);

		// Simulate the click on the filter
		filter.click();
	});
});
