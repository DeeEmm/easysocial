
EasySocial.require()
.done(function($) {

	$('[data-profile-menu-toggle]').on('click', function() {
		// Get the menu contents
		var contents = $('[data-es-profile-menu]').html();

		EasySocial.dialog({
			"title": "<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_MENU_TITLE', true);?>",
			"content": contents
		});
	});

	// Implement puller
	<?php if ($this->isMobile() && !$this->my->guest) { ?>
	EasySocial.require()
	.script('site/vendors/puller')
	.done(function($) {

		window.initPuller = function() {
			return window.es.puller.init({
									mainElement: '[data-profile-header]',
									triggerElement: '[data-profile-header]',
									onRefresh: function (done) {
										setTimeout(function () {
											var controller = $('body').controller(EasySocial.Controller.System.Notifier);

											controller.check(true, true);
											done();

										}, 150);
									}
								});
		};

		var puller = this.initPuller();
	});
	<?php } ?>
});
