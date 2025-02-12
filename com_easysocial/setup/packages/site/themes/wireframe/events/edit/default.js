EasySocial.require().script('site/events/edit').done(function($) {
	$('[data-events-edit]').addController('EasySocial.Controller.Events.Edit', {
		'id': '<?php echo $event->id; ?>',
		'isRecurring': <?php echo $event->isRecurringEvent() ? 1 : 0; ?>,
		'hasRecurring': <?php echo $event->hasRecurringEvents() ? 1 : 0; ?>
	});
});
