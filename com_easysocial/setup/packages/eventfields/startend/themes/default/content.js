EasySocial
.require()
.script('apps/fields/event/startend/content').done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Event.Startend', {
		requiredEnd: <?php echo $params->get('require_enddate') ? 1 : 0; ?>,
		dateFormat: '<?php echo $dateFormat; ?>',
		allowTime: <?php echo $params->get('allow_time') ? 1 : 0; ?>,
		allowTimezone: <?php echo $params->get('allow_timezone') ? 1 : 0; ?>,
		yearfrom: '<?php echo $params->get('yearfrom'); ?>',
		yearto: '<?php echo $params->get('yearto'); ?>',
		disallowPast: <?php echo $params->get('disallow_past') ? 1 : 0; ?>,
		minuteStepping: <?php echo $params->get('minute_stepping', 15); ?>,
		defaultStart: '<?php echo $params->get('default_start', 'nexthour'); ?>',
		allday: <?php echo $allday ? 1 : 0; ?>,
		calendarLanguage: '<?php echo $params->get('calendar_language', 'english'); ?>',
		dow: <?php echo $this->config->get('events.startofweek', 0); ?>
	});

	// Trigger manually on the first load if the all day is enabled
	<?php if ($allday) { ?>
	$(window).trigger('easysocial.fields.allday.change', [1]);
	<?php } ?>
});
