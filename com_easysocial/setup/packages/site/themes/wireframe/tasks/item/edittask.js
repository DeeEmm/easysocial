EasySocial.require()
.script('site/apps/tasks/tasks', 'site/members/suggest')
.library("textboxlist")
.done(function($)
 {

	$('[data-tasks-item]').implement('EasySocial.Controller.Apps.Tasks');

	$('[data-members-suggest]').addController('EasySocial.Controller.Members.Suggest', {
		"id": "<?php echo $task->id; ?>",
		"uid": "<?php echo $cluster->id; ?>",
		"max": 1
	});
});
