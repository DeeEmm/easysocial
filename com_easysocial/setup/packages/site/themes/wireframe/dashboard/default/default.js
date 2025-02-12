
EasySocial.require()
.script('site/stream/filter')
.done(function($){

	var title = "<?php echo addslashes($title);?>";

	var controller = $('[data-es-dashboard]').addController(EasySocial.Controller.Stream.Filter, {
		"{contents}": "[data-contents][data-stream-contents]",
		"title": title,
		"type": "user",
		"ajaxNamespace": "site/controllers/dashboard/getStream",
		"ajaxController": "dashboard",
		"ajaxTask": "getStream",
		"ajaxOptions": {
			"view": "dashboard"
		},
		"isMobile": <?php echo $this->isMobile() ? 'true' : 'false'; ?>
	});

	// Simulate the click event on a filter
	<?php if ($hashtag) { ?>
		// Since clicking on a hashtag does not have the capability to filter by post types, we do not need to send the post types
		controller.updateStream('hashtag', '<?php echo $hashtag;?>');
	<?php } else { ?>
		var filter = $('[data-filter-wrapper]').find('[data-filter-item].active');
		filter.click();
	<?php } ?>
});
