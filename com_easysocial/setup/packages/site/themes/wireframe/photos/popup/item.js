EasySocial.require()
.script('site/photos/item')
.done(function($){

	$("[data-photo-item=<?php echo $photo->uuid(); ?>]")
		.addController("EasySocial.Controller.Photos.Item", {
			<?php if ($options['showForm']) { ?>
			editable: <?php echo ($album->editable()) ? 1 : 0; ?>,
			taggable: <?php echo ($photo->taggable()) ? 1 : 0; ?>,
			<?php } ?>
			<?php if ($options['showNavigation']) { ?>
			navigation: true,
			<?php } ?>
			clusterId: '<?php echo $clusterId; ?>',
			clusterType: '<?php echo $clusterType; ?>',
			clusterPrivate: '<?php echo $clusterPrivate; ?>'
		});
});
