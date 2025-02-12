EasySocial.require().script('site/events/createRecurring').done(function($) {
    $('[data-events-create]').addController('EasySocial.Controller.Events.CreateRecurring', {
        schedule: <?php echo ES::json()->encode($schedule); ?>,
        totalRecurringEvents: <?php echo $totalRecurringEvents; ?>,
        eventId: '<?php echo $event->id; ?>'
    });
});
