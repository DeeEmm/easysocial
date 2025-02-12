
EasySocial.ready(function($) {

	$('[data-view-all]').on('click', function() {
		$(this).hide();
		$('[data-group-item]').removeClass('t-hidden');
	});
});