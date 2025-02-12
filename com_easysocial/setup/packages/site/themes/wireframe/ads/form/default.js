EasySocial.require()
.script('site/ads/form')
.done(function($) {
	$('[data-ad-form]').implement(EasySocial.Controller.Ads.Form);
});
