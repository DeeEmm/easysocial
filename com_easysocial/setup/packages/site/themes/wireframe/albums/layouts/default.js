EasySocial.require()
.script("site/albums/item")
.done(function($){

	$("[data-album-item=<?php echo $album->uuid(); ?>]")
	.addController("EasySocial.Controller.Albums.Item", {
		uid: "<?php echo $lib->uid;?>",
		type: "<?php echo $lib->type; ?>",
		rtl: <?php echo $rtl ? "true" : "false";?>,
		photoOrder: "<?php echo $photoOrdering == 'asc' ? 'oldest' : 'newest'; ?>",

		<?php if ($lib->editable($album)) { ?>
			editable: <?php echo $options['layout'] == 'row' ? 'false' : 'true';?>,
			plugin: {
				editor: {
					canUpload: <?php echo ($options['canUpload']) ? 'true' : 'false' ?>
				}
			},

			<?php if ($options['canUpload']) { ?>
			"uploader": {
				direction: "<?php echo $photoOrdering === 'desc' ? 'prepend' : 'append'; ?>",
				"settings": {
					url: "<?php echo $uploadUrl ?>",
					max_file_size: "<?php echo $lib->getUploadLimit(); ?>",
					camera: "image"
				}
			}
			<?php } ?>
		<?php } ?>
	});

	// Add classes on the privacy parent
	$(document).on('click.privacy.item', '[data-photo-item] [data-es-privacy-form]', function() {
		var parent = $(this).parents('[data-photo-item]');

		parent.toggleClass('has-privacy-menu-on');
	});

});
