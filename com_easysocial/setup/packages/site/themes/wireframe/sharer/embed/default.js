EasySocial.ready(function($) {

	$('[data-es-copy]').on('click', function() {
		$('[data-es-embed-textarea]').focus().select();

		document.execCommand('copy');
	});
	
});