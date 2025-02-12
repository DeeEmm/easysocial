EasySocial.ready(function($) {

$(document).on('keyup', '[data-table-grid-search-input]', $.debounce(function() {

var value = $.trim($(this).val());
var clearButton = $('[data-table-grid-search-reset]');

if (value.length > 0) {
	clearButton.removeClass('t-hidden');
	return;
}

clearButton.addClass('t-hidden');

}, 150));


});
