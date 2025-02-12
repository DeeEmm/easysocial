EasySocial.require()
.script('site/leaderboard/leaderboard')
.done(function($){
	$("[data-leadearboard]").implement('EasySocial.Controller.Leaderboard');
});