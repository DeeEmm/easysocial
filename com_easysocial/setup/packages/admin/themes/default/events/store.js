EasySocial.require().script('admin/events/store').done(function($) {
    $('[data-recurring-events]').addController('EasySocial.Controller.Events.Update', {
        postdata: <?php echo $data; ?>,
        updateids: <?php echo $updateids; ?>,
        schedule: <?php echo $schedule; ?>,
        eventId: <?php echo $event->id; ?>,
        totalRecurringEvents: <?php echo $totalRecurringEvents; ?>,
        isNew: <?php echo $isNew; ?>
    });
})
