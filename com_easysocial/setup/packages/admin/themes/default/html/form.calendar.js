
EasySocial.require()
.library('ui/timepicker')
.done(function($) {

	var element = $('[data-form-calendar-<?php echo $uuid;?>]');
	
	element.datetimepicker({
		"changeMonth": true,
		"changeYear": true,
		"timeFormat": "HH:mm:ss",
		"dateFormat": "yy-mm-dd",
		"lang": "<?php echo $language;?>",
		region: {
			dateFormat: 'yy-mm-dd'
		},
		"onSelect": function(value) {
		}
	});
});
