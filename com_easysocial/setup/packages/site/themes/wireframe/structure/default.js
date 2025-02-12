
<?php if (!$this->my->guest) { ?>
EasySocial.require()
.script('site/vendors/idle', 'site/system/notifier', 'site/vendors/gritter', 'site/system/notifications', 'site/system/keepalive')
.done(function($) {

	// Allow notifications to manipulate the page title with the original title
	window.es.pageTitle = "<?php echo ES::string()->escape($pageTitle);?>";

	<?php if ($this->config->get('notifications.broadcast.popup')) { ?>
	$('body').on('notifier.updates', function(event, data) {

		if (data.broadcasts == undefined || data.broadcasts == false) {
			return;
		}

		// Own data
		if (data.broadcasts.length <= 0) {
			return;
		}

		var period = "<?php echo $this->config->get('notifications.broadcast.period');?>";
		var sticky = <?php echo $this->config->get('notifications.broadcast.sticky') ? 'true' : 'false'; ?>;

		// Means something to do
		$(data.broadcasts).each(function(i, item) {

			var info = {
				title: item.title,
				raw_title: item.raw_title,
				text: item.content,
				image: item.authorAvatar,
				sticky: sticky,
				time: period * 1000,
				class_name: 'es-broadcast'
			};

			$.gritter.add(info);
		});

	});
	<?php } ?>

	$('body').implement(EasySocial.Controller.System.Notifier, {
		"interval": <?php echo ES_NOTIFIER_POLLING_INTERVAL; ?>,
		"guest": <?php echo $this->my->guest ? 'true' : 'false'; ?>
	});

	$('body').implement(EasySocial.Controller.System.Notifications, {
		"interval": <?php echo $this->config->get('notifications.polling.interval');?>,
		"userId": "<?php echo $this->my->id;?>",
		"type": "<?php echo $this->config->get('notifications.polling.single') ? 'single' : 'multiple';?>"
	});


	<?php if ($this->config->get('users.inactivity.enabled')) { ?>
	<?php
		$inactiveDuration = $this->config->get('users.inactivity.duration', '15');
		$inactiveDuration = $inactiveDuration * 60 * 1000;

	?>
	$(document).idle({
		onIdle: function(){
			EasySocial.dialog({
				content: EasySocial.ajax('site/views/dashboard/confirmPageRefresh')
			});
		},
		idle: <?php echo $inactiveDuration; ?>
	});
	<?php } ?>

	<?php if (ES::keepAlive()) { ?>
		// if user viewing with mobile device,
		// we need to handle the visibilityState if browser is minimized.
		// to renew session token.
		if (window.es.mobile || window.es.tablet) {
			$('body').implement(EasySocial.Controller.System.KeepAlive);
		}
	<?php } ?>

});
<?php } ?>
