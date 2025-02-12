EasySocial.require()
.script("site/albums/browser")
.done(function($){

	var loadMoreMyclusterStartLimit = "<?php echo $startlimit; ?>";
	var loadMoreAlbumStartLimit = "<?php echo $startlimit; ?>";

	$("[data-album-browser=<?php echo $uuid; ?>]")
		.addController("EasySocial.Controller.Albums.Browser", {
			"uid": "<?php echo $lib->uid;?>",
			"type": "<?php echo $lib->type; ?>"
		});

	$('[data-album-mycluster-showall]').on('click', function() {

		var button = $(this);

		EasySocial.ajax('site/views/albums/showMoreAlbums', {
			"myTotalClusterAlbums": "<?php echo $myTotalClusterAlbums; ?>",
			"startlimit": loadMoreMyclusterStartLimit,
			"userAlbumOwnerId": "<?php echo $lib->uid; ?>",
			"albumType": "<?php echo $lib->type; ?>",
			"albumId": "<?php echo $id; ?>",
			"userId": "<?php echo $currentLoggedInUserId; ?>"

		}).done(function(contents, nextlimit) {

			// append the rest of the albums item
			$('[data-album-list-item-container-myclusteralbum]').append(contents);

			if (nextlimit > 0) {
				loadMoreMyclusterStartLimit = nextlimit;
			} else {
				// hide the view all button
				button.hide();
			}

		});
	});

	$('[data-album-showall]').on('click', function() {

		var button = $(this);

		EasySocial.ajax('site/views/albums/showMoreAlbums', {
			"totalalbums": "<?php echo $totalAlbums; ?>",
			"startlimit": loadMoreAlbumStartLimit,
			"userAlbumOwnerId": "<?php echo $lib->uid; ?>",
			"albumType": "<?php echo $lib->type; ?>",
			"albumId": "<?php echo $id; ?>",
			"othersAlbum": "<?php echo $lib->type == SOCIAL_TYPE_USER ? '0' : '1'; ?>"

		}).done(function(contents, nextlimit) {

			// append the rest of the albums item
			$('[data-album-list-item-container-regular]').append(contents);

			if (nextlimit > 0) {
				loadMoreAlbumStartLimit = nextlimit;
			} else {
				// hide the view all button
				button.hide();
			}

		});
	});
});
