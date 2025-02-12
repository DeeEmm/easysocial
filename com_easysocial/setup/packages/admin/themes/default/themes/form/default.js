EasySocial.ready(function($) {
    $.Joomla('submitbutton', function(task) {

    	if (task == 'cancel') {
    		window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=themes';
    	}

        $.Joomla('submitform', [task]);
        return false;
    });
})