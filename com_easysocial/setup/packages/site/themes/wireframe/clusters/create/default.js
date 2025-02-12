
EasySocial
.require()
.script('site/clusters/create')
.done(function($){
    $('[data-es-select-category]').implement(EasySocial.Controller.Clusters.Create, {
        "clusterType": "<?php echo $clusterType ?>"
    });
});
