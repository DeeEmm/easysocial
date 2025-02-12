
EasySocial.require()
.script('site/clusters/create')
.done(function($){
    $('[data-es-groups-create]').implement(EasySocial.Controller.Clusters.Create, {
        "previousLink": "<?php echo FRoute::groups(array('layout' => 'steps' , 'step' => ($currentStep - 1)) , false);?>"
    });
});
