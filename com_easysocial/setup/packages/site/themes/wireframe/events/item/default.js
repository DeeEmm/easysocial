EasySocial
.require()
.script('site/stream/filter')
.done(function($){

	// Process the title so that there is no double qoute issue.
	var title = "<?php echo addslashes($title);?>";

	$('[data-es-event]').implement('EasySocial.Controller.Stream.Filter', {
		"{contents}": "[data-contents][data-stream-contents]",
		"title": title,
		"type": "<?php echo SOCIAL_TYPE_EVENT;?>",
		"uid": "<?php echo $event->id;?>",
		"ajaxNamespace": "site/controllers/events/getStream",
		"ajaxController": "events",
		"ajaxTask": "getStream",
		"ajaxOptions": {
			"eventId": "<?php echo $event->id;?>"
		}
	});

	// Implement puller
	<?php if ($this->isMobile() && !$this->my->guest) { ?>
	EasySocial.require()
	.script('site/vendors/puller')
	.done(function($) {

		window.initPuller = function() {
			return window.es.puller.init({
									mainElement: '.es-profile-header',
									triggerElement: '.es-profile-header',
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
