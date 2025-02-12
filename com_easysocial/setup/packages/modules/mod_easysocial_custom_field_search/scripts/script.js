EasySocial.require().script('site/search/customfield').done(function($) {

	if ($('[data-mod-customfield-search]').data('submit-onclick')) {
		$('[data-checkbox-option]').change(function() {
			$('[data-submit-button]').click();
		});
	}

	$('[data-customfield-search-item]').addController('EasySocial.Controller.Search.Customfield');
});
