EasySocial.ready(function($) {

// Simulate the read more in comments, since this is rendered from the back-end
$('span[data-es-comment-readmore]').on('click', function() {
	var fullContent = $(this).siblings('[data-es-comment-full]');
	var wrapper = $(this).parents('[data-comment-wrapper]');

	wrapper.html(fullContent.html());
});

});
