EasySocial.require()
.script('site/events/browser')
.done(function($) {
	$('[data-es-events]').addController('EasySocial.Controller.Events.Browser', {
		distance: '<?php echo $distance; ?>',
		activeUserId: "<?php echo $activeUser->id ? $activeUser->id : '';?>",
		browseView: "<?php echo $browseView; ?>",
		hasLocation: <?php echo $hasLocation ? 1 : 0; ?>,
		userLatitude: '<?php echo $hasLocation ? $userLocation['latitude'] : ''; ?>',
		userLongitude: '<?php echo $hasLocation ? $userLocation['longitude'] : ''; ?>',
		delayed: <?php echo $delayed ? 'true' : 'false'; ?>,
		includePast: <?php echo $includePast ? 'true' : 'false'; ?>,
		ordering: '<?php echo $ordering; ?>',
		clusterId: '<?php echo $cluster ? $cluster->id : ''; ?>'
	});
});
