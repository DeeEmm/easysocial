
EasySocial.require()
.library('dialog')
.done(function($) {
	window.selectPoints = function(obj) {

		console.log(obj);

		$('[data-jfield-points-title]').val(obj.title);
		$('[data-jfield-points-value]').val(obj.alias);

		EasySocial.dialog().close();
	};

	$('[data-jfield-points]').on('click', function() {
		EasySocial.dialog({
			"content": EasySocial.ajax( 'admin/views/points/browse' , { 'jscallback' : 'selectPoints' })
		});
	});

});
