EasySocial.ready(function($) {
    
    $.Joomla('submitbutton', function(task) {

        if (task == 'cancel') {
            window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=mailer&layout=editor';

            return;
        }

        $.Joomla('submitform', [task]);
        return false;
    });
});