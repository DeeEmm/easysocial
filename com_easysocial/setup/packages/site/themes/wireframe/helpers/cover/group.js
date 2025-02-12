EasySocial.require()
.script('site/avatar/avatar', 'site/cover/cover')
.done(function($){

	// Implement the avatar and cover
	$('[data-cover]').implement(EasySocial.Controller.Cover, {
		"uid": "<?php echo $group->id;?>",
		"type": "group"
	});

	$('[data-avatar]').implement(EasySocial.Controller.Avatar, {
		"uid": "<?php echo $group->id;?>",
		"type": "group"
	});
});