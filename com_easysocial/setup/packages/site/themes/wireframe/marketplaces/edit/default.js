EasySocial.require().script('site/marketplaces/edit').done(function($) {
	$('[data-marketplaces-edit]').addController('EasySocial.Controller.Marketplaces.Edit', {
		'id': '<?php echo $listing->id; ?>'
	});
});
