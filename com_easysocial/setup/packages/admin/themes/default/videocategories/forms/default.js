
EasySocial.require()
.script('admin/utilities/alias')
.done(function($) {

    $.Joomla('submitbutton', function(task) {

        if (task == 'cancel') {
            window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=videocategories';

            return;
        }

        return $.Joomla('submitform', [task]);
    });


    $('[data-videos-category-form]').implement(EasySocial.Controller.Utilities.Alias, {
        "{source}": "[data-category-title]",
        "{target}": "[data-category-alias]"
    });
});