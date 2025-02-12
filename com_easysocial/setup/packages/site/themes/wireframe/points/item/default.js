EasySocial.require()
.script('site/points/point')
.done(function($) {
	$('[data-point]').addController('EasySocial.Controller.Points.Point');
});