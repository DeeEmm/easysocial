EasySocial.require()
.script('site/apps/tasks/tasks')
.done(function($) {

	$('[data-tasks-item]').implement('EasySocial.Controller.Apps.Tasks', {
		"return": "<?php echo base64_encode($cluster->getAppPermalink('tasks'));?>"
	});
});
