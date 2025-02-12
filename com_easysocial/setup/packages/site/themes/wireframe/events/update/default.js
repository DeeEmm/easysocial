EasySocial.require().script('site/events/update').done(function($) {
    $('[data-events-update]').addController('EasySocial.Controller.Events.Update', {
        postdata: <?php echo $data; ?>,
        updateids: <?php echo $updateids; ?>,
        schedule: <?php echo $schedule; ?>,
        eventId: <?php echo $event->id; ?>,
        isNew: <?php echo $isNew; ?>
    });
});
