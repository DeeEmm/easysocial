
EasySocial.require()
.script('site/clusters/create')
.done(function($){
    $('[data-es-pages-create]').implement(EasySocial.Controller.Clusters.Create, {
        "previousLink": "<?php echo ESR::pages(array('layout' => 'steps' , 'step' => ($currentStep - 1)) , false);?>"
    });
});
