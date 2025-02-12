EasySocial.require()
.done(function($){

<?php if ($this->config->get('users.dashboard.guest', true)) { ?>
	EasySocial.ajax('site/controllers/dashboard/getPublicStream', {
		"hashtag": "<?php echo $hashtag;?>"
	})
	.done(function(content, count) {

		if (count == 0) {
			$('[data-wrapper]').addClass('is-empty');
		}

		// Update the contents of the dashboard area
		$('[data-wrapper]').removeClass("is-loading");

		$('body').trigger('beforeUpdatingContents');

		// Hide the content first.
		$.buildHTML(content)
			.appendTo($('[data-es-dashboard] [data-contents]'));

		$('body').trigger('afterUpdatingContents');

		// 3PD FIX: Kunena [text] replacement
		try {
			MathJax && MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
		} catch(err) {};

	}).fail(function(message) {
		return message;
	});
<?php } ?>
});
