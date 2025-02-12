EasySocial.ready(function($) {

	var form = $('[data-installer-form]');

	// Set the task to the correct task.
	$('[data-install-directory]').bind( 'click' , function() {
		form.find('input[name=task]').val('installFromDirectory');
		form.submit();
	});

	// Set the task to the correct task.
	$('[data-install-upload]').bind( 'click' , function() {
		form.find('input[name=task]').val('installFromUpload');
		form.submit();
	});

});
