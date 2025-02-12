EasySocial.ready(function($) {

$('[data-copy]').on('click', function() {
	var wrapper = $(this).parents('[data-copy-wrapper]');
	var input = wrapper.find('input[type=text]');

	input.select();
	document.execCommand("Copy");
});


});
