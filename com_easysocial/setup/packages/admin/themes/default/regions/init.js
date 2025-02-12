EasySocial.require().script('admin/regions/init').done(function($) {
    $('[data-base]').addController('EasySocial.Controller.Region.Init', {
        callback: function() {
            window.location = "<?php echo FRoute::url(array('view' => 'regions')); ?>";
        }
    });
});
