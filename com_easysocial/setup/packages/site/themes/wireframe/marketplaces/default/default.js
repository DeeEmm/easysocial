EasySocial.require()
.script('site/marketplaces/browser')
.done(function($) {
	$('[data-es-marketplaces]').addController('EasySocial.Controller.Marketplaces.Browser', {
		activeUserId: "<?php echo $activeUser->id ? $activeUser->id : '';?>",
		browseView: "<?php echo $browseView; ?>",
		ordering: '<?php echo $sort; ?>',
		clusterId: '<?php echo $cluster ? $cluster->id : ''; ?>',
		uid: '<?php echo $uid; ?>',
		type: '<?php echo $type; ?>',
		hasLocation: <?php echo $hasLocation ? 1 : 0; ?>,
		userLatitude: '<?php echo $hasLocation ? $userLocation['latitude'] : ''; ?>',
		userLongitude: '<?php echo $hasLocation ? $userLocation['longitude'] : ''; ?>',
		delayed: <?php echo $delayed ? 'true' : 'false'; ?>
	});
});
