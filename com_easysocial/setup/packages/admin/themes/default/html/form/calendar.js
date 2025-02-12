
var lang = EasySocial.options.momentLang;

EasySocial.require()
.library('datetimepicker', 'moment/' + lang)
.done(function($) {

	var element = $('[data-form-calendar-<?php echo $uuid;?>]');

	var minDate = new $.moment();
	// minus 1 as we need to include today.
	minDate = minDate.date(minDate.date() - 1);

	<?php if (!$restrictMinDate) { ?>
		minDate = new $.moment({ y: 1900 });
	<?php } ?>

	element._datetimepicker({
		component: "es",
		useCurrent: false,
		format: "<?php echo $format;?>",
		minDate: minDate,
		sideBySide: false,
		pickTime: <?php echo $time ? 'true' : 'false';?>,
		minuteStepping: 1,
		language: lang,
		icons: {
			time: 'far fa-clock',
			date: 'fa fa-calendar',
			up: 'fa fa-chevron-up',
			down: 'fa fa-chevron-down'
		}
	});

});
