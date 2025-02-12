EasySocial
.require()
.done(function($){
	
	$.Joomla('submitbutton' , function(task) {
		$.Joomla('submitform' , [task]);
	});

});
