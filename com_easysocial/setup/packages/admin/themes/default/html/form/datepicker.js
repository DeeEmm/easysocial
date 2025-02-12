var lang = EasySocial.options.momentLang;

EasySocial.require()
.library('datetimepicker', 'moment/' + lang)
.done(function($) {

var wrapper = $('[data-es-datepicker]');
var picker = wrapper.find('[data-es-datepicker-picker]');
var toggle = wrapper.find('[data-es-datepicker-toggle]');
var input = wrapper.find('[data-es-datepicker-input]');

picker._datetimepicker({
	component: "es",
	useCurrent: false,
	format: "<?php echo $format;?>",
	icons: {
		time: 'fa fa-time',
		date: 'fa fa-calendar',
		up: 'fa fa-chevron-up',
		down: 'fa fa-chevron-down'
	},
	sideBySide: false,
	pickTime: false,
	minuteStepping: 1,
	language: lang
});

var current = wrapper.data('value');

console.log(current);

if (current !== '') {
	picker.data('DateTimePicker')['setDate']($.moment(current));
}

toggle.on('click', function() {
	picker.focus();
});

picker.on('dp.change', function(event) {
	var date = event.date.toDate();

	input.val(date.getFullYear() + '-' +
		('00' + (date.getMonth()+1)).slice(-2) + '-' +
		('00' + date.getDate()).slice(-2) + ' ' +
		('00' + date.getHours()).slice(-2) + ':' +
		('00' + date.getMinutes()).slice(-2) + ':' +
		('00' + date.getSeconds()).slice(-2));
});

});
