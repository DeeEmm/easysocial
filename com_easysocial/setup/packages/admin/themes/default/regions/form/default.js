EasySocial.require()
.script('admin/regions/form')
.done(function($) {
    
    var controller = $('[data-form]').addController('EasySocial.Controller.Regions.Form');

    $.Joomla('submitbutton', function(task) {

        if (task == 'cancel') {
            window.location = 'index.php?option=com_easysocial&view=regions';
            return false;
        }

        var valid = controller.validate();

        if (!valid) {
        	alert("<?php echo JText::_('COM_EASYSOCIAL_REGIONS_FORM_INCOMPLETE', true);?>");
        	return;
        }

        $.Joomla('submitform', [task]);
    });
});
