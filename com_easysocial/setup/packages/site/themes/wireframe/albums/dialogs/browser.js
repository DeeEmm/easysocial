EasySocial.require()
	.script("site/albums/browser")
	.done(function($){

		$("[data-album-browser=<?php echo $uuid; ?>]")
			.addController("EasySocial.Controller.Albums.Browser", {
        		isMobile : <?php echo $this->isMobile() ? 'true' : 'false' ?>,
				itemRenderOptions: {
					layout: "dialog",
					limit: 20,
					canUpload: 0,
					showToolbar: 0,
					showInfo: 0,
					showStats: 0,
					showResponse: 0,
					showTags: 0,
					showForm: 0,

					photoItem: {
						showToolbar: 0,
						showStats: 0,
						showForm: 0,
						openInPopup: 0
					}
				}
			});
	});
