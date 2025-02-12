
EasySocial.require()
.done(function($) {
	$('[data-es-gdpr-request]').on('click.es.gdpr.link', function() {
		EasySocial.dialog({
			"content": EasySocial.ajax('site/views/profile/confirmDownload'),
		});
	});
});