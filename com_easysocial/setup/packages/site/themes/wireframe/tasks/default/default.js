EasySocial.require()
.script('site/apps/tasks/tasks')
.done(function($) {
    $('[data-tasks-milestones]').implement(EasySocial.Controller.Apps.Tasks.Milestones.Browse, {
    	"return": "<?php echo base64_encode($cluster->getAppPermalink('tasks'));?>"
    })
});
