<?php if ($this->my->guest && ES::sso()->hasAutologin()) { ?>

	function isSiteOnline(callback) {
		// try to load favicon
		var timer = setTimeout(function(){
			// timeout after 5 seconds
			callback(false);
		},5000)

		var img = document.createElement("img");
		img.onload = function() {
			clearTimeout(timer);
			callback(true);
		}

		img.onerror = function() {
			clearTimeout(timer);
			callback(false);
		}

		img.src = "https://www.facebook.com/favicon.ico";
	}

	isSiteOnline(function(found){
		if (found) {
			EasySocial.require()
			.script('//connect.facebook.net/en_US/all.js')
			.done(function($) {

				window.FB.init({
					appId: "<?php echo $this->config->get('oauth.facebook.app');?>",
					status: true,
					cookie: true,
					xfbml: false
				});

				FB.getLoginStatus(function(response) {

					if (response.status == 'unknown' || response.status == 'not_authorized') {
						return;
					}

					// The user has previously already connected with the app, so we just redirect to log them in
					if (response.status == 'connected') {

						// Check if the user is associated with any accounts
						EasySocial.ajax('site/views/oauth/validateId', {
							"id": response.authResponse.userID
						}).done(function(valid) {

							if (valid) {
								window.location = '<?php echo ES::oauth('facebook')->getLoginRedirection();?>';
							}
						});
						
						return;
					}
				});
			});
		} else {
			// site is offline (or favicon not found, or server is too slow)
		}
	})
<?php } ?>