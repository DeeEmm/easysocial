EasySocial.ready(function($){

	$.Joomla('submitbutton' , function(task) {

		if (task == 'discover') {
			window.location = 'index.php?option=com_easysocial&view=languages&layout=discover';
			return false;
		}

        if (task == 'uninstall') {
        	var selected = [];

			$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i , el ){
				selected.push($(el).val());
			});

            EasySocial.dialog({
            	content: EasySocial.ajax('admin/views/languages/confirmDelete', {
            		"cid": selected
            	})
         	});

            return;
        }

		$.Joomla('submitform', [task]);

	});
});
