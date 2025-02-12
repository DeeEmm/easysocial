EasySocial.ready(function($) {

$('[data-logo]').on('change', function() {

var el = $(this);
var label = el.val().replace(/\\/g, '/').replace(/.*\//, '');


$('[data-logo-title]').val(label);

});

});
