EasySocial.require()
.done(function($) {

	var loader = $('[data-es-events-calendar-loading]');
	var wrapper = $('[data-es-events-calendar]');
	var loadEvents = function(timestamp) {
		var options = {
			"date": timestamp,
			"filter": "<?php echo $filter;?>"
		};

		wrapper.html('');
		loader.addClass('is-empty');

		EasySocial.ajax('site/views/events/renderFullCalendar', options)
		.done(function(html) {
			loader.removeClass('is-empty');
			wrapper.html(html);
		});
	}

	// On initial page load, try to render events
	loadEvents();

	$(document).on('click.es.calendar.next', '[data-calendar-nav]', function() {
		var element = $(this);
		var timestamp = element.data('calendar-nav');

		loadEvents(timestamp);
	});


});
