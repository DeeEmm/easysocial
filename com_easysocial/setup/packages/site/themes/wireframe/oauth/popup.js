// Reload parent's window
// iOS Facebook Mobile App Browser detected
if (navigator.userAgent.match(/(iPod|iPhone|iPad)/) && navigator.userAgent.match(/FBAN/i)) {

	// for some reason that window.close() not function in Facebook in-app browser
	// for now have to use this
	window.location.href = "<?php echo $redirect;?>";

} else {

	if (window.opener !== null) {
		window.opener.location = "<?php echo $redirect;?>";
	} else {
		window.location = "<?php echo $redirect;?>";
	}

	// Only proceed this auto close browser windows tab in Joomla 3 and not Facebook oauth client #5362
	<?php if ($isJoomla3 && !$isFacebookOauthClient) { ?>
		// We cannot just close the window.
		// This timeout is to fix window close issue in chrome.
		setTimeout(function(){
			window.close();
		}, 1);
	<?php } ?>
}
