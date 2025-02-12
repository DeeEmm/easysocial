EasySocial.ready(function($) {
    $('input[name="event_allday"]').on('change', function() {
        $(window).trigger('easysocial.fields.allday.change', [$(this).val()]);
    });
});
