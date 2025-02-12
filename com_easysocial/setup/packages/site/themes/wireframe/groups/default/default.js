EasySocial
.require()
.script('site/groups/browser')
.done(function($){

	$('[data-es-groups]').implement(EasySocial.Controller.Groups.Browser, {
		"filter": "<?php echo $filter;?>",

		<?php if ($activeCategory) { ?>
		"categoryid": "<?php echo $activeCategory->id;?>",
		<?php } ?>
		browseView: "<?php echo $browseView; ?>",
		"userId": "<?php echo $user ? $user->id : '';?>",
		"latitude": '<?php echo $hasLocation ? $userLocation['latitude'] : ''; ?>',
		"longitude": '<?php echo $hasLocation ? $userLocation['longitude'] : ''; ?>',
	});
});
