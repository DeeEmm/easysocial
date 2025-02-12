EasySocial.require()
.script('site/apps/tasks/tasks')
.done(function($) {

    // Apply controller
    $('[data-task-milestone]').implement('EasySocial.Controller.Apps.Tasks.Milestones.Form', {
        <?php if ($milestone->user_id) { ?>
        "exclusion": <?php echo ES::json()->encode(array($milestone->user_id)); ?>,
        <?php } ?>
        "return": "<?php echo base64_encode($cluster->getAppPermalink('tasks'));?>"
    });
});
