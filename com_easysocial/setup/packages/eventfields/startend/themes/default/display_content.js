
EasySocial.require().script('apps/fields/event/startend/display').done(function($) {
    
	$('[data-display-field="<?php echo $field->id; ?>"] [data-startend-box]').addController(EasySocial.Controller.Field.Event.Startend.Display.Box, {
        id: <?php echo $field->id; ?>,
        userid: <?php echo $this->my->id; ?>
    });
});
