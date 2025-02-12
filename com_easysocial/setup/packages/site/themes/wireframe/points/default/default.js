EasySocial.require()
.script("site/points/counter")
.done(function($){
	$(document).ready(function() {
		$("[data-points-wrapper]").addController("EasySocial.Controller.Points.Counter");
	});
});
