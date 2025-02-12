EasySocial.ready(function($) {

$.Joomla('submitbutton', function(task) {

	if (task == 'purge') {
		var proceed = confirm('<?php echo JText::_('COM_ES_CONFIRM_PURGE_HONEYPOT');?>');

		if (proceed) {
			$.Joomla('submitform', [task]);
		}

		return;
	}

	$.Joomla('submitform', [task]);
});

$('[data-view]').on('click', function(event) {
	event.preventDefault();
	event.stopPropagation();

	var id = $(this).data('id');

	EasySocial.dialog({
		"content": EasySocial.ajax('admin/views/honeypot/data', {"id": id})
	});
});

});
