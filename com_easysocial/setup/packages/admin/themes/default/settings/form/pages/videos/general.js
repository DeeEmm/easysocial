EasySocial.ready(function($) {

	$('[data-video-uploads]').on('change', function() {
		var checkbox = $(this);
		var checked = checkbox.is(':checked');

		$('[data-video-encoding]').toggleClass('t-hidden', !checked);
	});

	$('[data-video-cpu-limit]').on('change', function() {
		var checked = $(this).is(':checked');

		$('[data-es-video-threads]').toggleClass('t-hidden', !checked);
	});

	$('[data-twitch-login-button]').on('click', function() {
		var url = $(this).data('url');

        // Width for the popup
        var width = 600;
        var height = 450;

        // Get the top and left
        var top = (screen.height / 2) - (height / 2);
        var left = (screen.width / 2) - (width / 2);

        window.open(url, '', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
	});

    window.doneLogin = function() {
        window.location.href = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_easysocial&view=settings&layout=form&page=videos&tab=integrations';
    }
});
