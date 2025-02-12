EasySocial.require().script('admin/events/approveRecurring').done(function($) {
	$('[data-recurring-events]').addController('EasySocial.Controller.Events.ApproveRecurring', {
		postdatas: <?php echo $postdatas; ?>,
		schedules: <?php echo $schedules; ?>,
		eventids: <?php echo $eventids; ?>
	});
})
