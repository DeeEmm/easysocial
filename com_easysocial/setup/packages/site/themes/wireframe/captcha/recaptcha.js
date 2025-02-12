EasySocial.ready(function($) {
	
	// Create recaptcha task
	var task = [
		'recaptcha_<?php echo $uid;?>', {
			'sitekey': '<?php echo $key;?>',
			'theme': '<?php echo $theme;?>'
		}
	];

	<?php if ($invisible) { ?>
	window.recaptchaDfd = $.Deferred();

	window.getResponse = function() {

		var token = grecaptcha.getResponse();
		var responseField = $('[data-es-recaptcha-response]');

		if (token) {
			responseField.val(token);
			window.recaptchaDfd.resolve();
			return;
		}

		grecaptcha.reset();
		window.recaptchaDfd.reject();
	};

	$('[data-field-<?php echo $elementId;?>]').on('onSubmit', function(event, register) {
		register.push(window.recaptchaDfd);

		grecaptcha.execute();
	});
	<?php } ?>

	// Render recaptcha form
	var runTask = function(task) {

		// Only run if the task really exists
		if (task) {
			<?php if (!$invisible) { ?>
			// Captcha input
			grecaptcha.render.apply(grecaptcha, task);
			<?php } ?>

			<?php if ($invisible) { ?>

			// Initialize the index
			if (!window.invisibleCaptchaIndex) {
				window.invisibleCaptchaIndex = 1;
			} else {
				window.invisibleCaptchaIndex++;
			}

			// Support infinite scroll where multiple invisible captcha is rendered
			var invisibleCaptchaIndex = $('[data-es-recaptcha-invisible]').length - window.invisibleCaptchaIndex;
			var element = $('[data-es-recaptcha-invisible]')[invisibleCaptchaIndex];

			// Invisible captcha
			if (!window.JoomlaInitReCaptcha2 || (window.JoomlaInitReCaptcha2 && invisibleCaptchaIndex != 0)) {
				grecaptcha.render(element, {
							"sitekey": "<?php echo $key;?>",
							"callback": getResponse
				});
			}
			<?php } ?>
		}
	}

	// If grecaptcha is not ready, add to task queue
	if (!window.grecaptcha || (window.grecaptcha && !window.grecaptcha.render)) {
		var tasks = window.recaptchaTasks || (window.recaptchaTasks = []);
		tasks.push(task);
	// Else run task straightaway
	} else {
		runTask(task);
	}

	// If recaptacha script is not loaded
	if (!window.recaptchaScriptLoaded && (!window.grecaptcha || (window.grecaptcha && !window.grecaptcha.render))) {

		if (window.JoomlaInitReCaptcha2) {
			// joomla recaptcha already loaded. let ride ontop of JoomlaInitReCaptcha2 callback.
			var joomlaRecaptcha = window.JoomlaInitReCaptcha2;

			// reset
			window.JoomlaInitReCaptcha2 = function() {
				var task;

				// execute our task.
				while (task = tasks.shift()) {
					runTask(task);
				};

				// now we execute joomla callback.
				$(joomlaRecaptcha);
			};
		} else {

			// Load the recaptcha library
			EasySocial.require()
				.script("//www.google.com/recaptcha/api.js?onload=recaptchaCallback&render=explicit&hl=<?php echo $language;?>");

			window.recaptchaCallback = function() {
				var task;

				while (task = tasks.shift()) {
					runTask(task);
				}
			};

		}

		window.recaptchaScriptLoaded = true;
	}
});