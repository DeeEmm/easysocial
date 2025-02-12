EasySocial.require()
.script('site/videos/item')
.done(function($) {

	$('[data-video-item]').implement(EasySocial.Controller.Videos.Item, {
		callbackUrl: "<?php echo base64_encode($video->getPermalink(false));?>"

		<?php if ($usersTagsList) { ?>
		,"tagsExclusion": <?php echo $usersTagsList;?>
		<?php } ?>

		<?php if ($cluster) { ?>
		,"clusterId": '<?php echo $cluster->id; ?>',
		"clusterType": '<?php echo $cluster->getType(); ?>'
		<?php } ?>
	});
});
