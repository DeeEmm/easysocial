// Monitor clicks on the body. So that all dropdowns should be hidden whenever clicks are made on the body.

EasySocial.ready(function($) {

	// This should only get executed once
	if(!EasySocial.sharingPopover) {
		EasySocial.sharingPopover = true;

		$('body').on('click.sharing-out-of-dropdown', function() {
			$('[data-sharing-popover]').removeClass('open');
		});
	}

	var popover = $('[data-sharing-popover-<?php echo $uniqueid; ?>]');

	popover.find('[data-sharing-popover-link]').on('click', function(event){

		event.stopPropagation();

		// Store original state first
		var opened = popover.hasClass('open');

		// Close all other popovers
		$('[data-sharing-popover]').removeClass('open');

		// Toggle this popover with reverse opened state
		popover.toggleClass('open', !opened);
	});

	popover.find('[data-sharing-contents]').on('click', function(event) {
		event.stopPropagation();
	});

});
