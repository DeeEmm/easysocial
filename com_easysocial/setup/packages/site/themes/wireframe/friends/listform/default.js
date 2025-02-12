
EasySocial.require()
	.script("site/friends/suggest")
	.library("textboxlist")
	.done(function($){
		$('[data-friends-suggest]').addController(EasySocial.Controller.Friends.Suggest);
	});
