EasySocial.require().script('apps/fields/event/recurring/content').done(function($) {

	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Event.Recurring', {
		value: <?php echo ES::json()->encode($original); ?>,
		dateFormat: '<?php echo $dateFormat; ?>',
		allday: <?php echo $allday ? 1 : 0; ?>,
		showWarningMessages: <?php echo $showWarningMessages; ?>,
		eventId: <?php echo $eventId ? $eventId : 'null'; ?>,
		dow: <?php echo $this->config->get('events.startofweek', 0); ?>
	});
});
