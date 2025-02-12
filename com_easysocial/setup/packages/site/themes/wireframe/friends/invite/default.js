EasySocial.require()
.library('toast')
.done(function($) {

	$('[data-es-copy]').on('click', function() {
		$('[data-clipboard-input]').focus().select();

		document.execCommand('copy');

		// Show the notification to the users to tell them that the link has been copied
		new $.toast({
			text: '<?php echo JText::_('COM_ES_INVITE_FRIENDS_VIA_URL_LINK_COPIED_MESSAGE');?>',
			loader: false,
			textAlign: 'center',
			showHideTransition: 'fade',
			allowToastClose: true,
			hideAfter: 3000,
			position: 'bottom-center',
			loaderBg: '#000000',

			// false if there should be only one toast at a time or a number representing the maximum number of toasts to be shown at a time
			stack: false
		});
	});
});
