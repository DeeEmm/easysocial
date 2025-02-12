
EasySocial
.require()
.script('site/friends/browser')
.done(function($) {

	$('[data-es-friends-wrapper]').addController(EasySocial.Controller.Friends.Browser, {
		activeList: "<?php echo $activeList ? $activeList->id : '';?>"
	});

	// Get the controller once it is initiated
	var controller = $('[data-es-friends-wrapper]').controller();

	// Custom actins when a friend is removed
	$(document).on('es.friends.unfriend', '[data-es-friends] [data-task="unfriend"]', function(){

		var parent = $(this).closest('[data-item]');
		var id = parent.data('id');

		// Remove the item
		controller.removeItem(id, 'standard');
	});

	// Custom actions when a friend request is rejected
	$(document).on('es.friends.reject', '[data-es-friends] [data-task="reject"]', function() {

		var parent = $(this).closest('[data-item]');
		var id = parent.data('id');

		// Remove the item from the list
		controller.removeItem(id, 'standard');
	});

	$(document)
		.on('es.friends.accept', '[data-es-friends] [data-task="accept"]', function() {

			// Get the parent item
			var parent = $(this).parents('[data-item]');
			var id = parent.data('id');

			// Remove this item from the pending list.
			controller.removeItem(id, 'standard');
		});

	$(document)
		.on('es.friends.cancel', '[data-es-friends] [data-task="cancel"]', function() {

			var parent = $(this).closest('[data-item]');
			var id = parent.data('id');

			// Remove the item
			controller.removeItem(id, 'standard');
		});
});
