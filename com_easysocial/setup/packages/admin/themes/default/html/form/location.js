
EasySocial.require()
.script('locations')
.done(function($) {
    $('[data-es-location]').implement(EasySocial.Controller.Locations,{
        "latitude": "<?php echo $latitude;?>",
        "longitude": "<?php echo $longitude;?>"
    });
});