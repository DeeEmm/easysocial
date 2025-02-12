EasySocial.require()
.script('site/events/create')
.done(function($){

    $('[data-create-form]').implement('EasySocial.Controller.Events.Create' ,{
            'previousLink'  : "<?php echo FRoute::events(array('layout' => 'steps' , 'step' => ($currentIndex - 1)) , false);?>"
        }
    );
});
