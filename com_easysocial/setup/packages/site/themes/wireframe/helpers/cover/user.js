EasySocial.require()
.script('site/avatar/avatar', 'site/cover/cover')
.done(function($){

	$('[data-profile-avatar]').implement(EasySocial.Controller.Avatar, {
		"uid": "<?php echo $user->id;?>",
		"type": "<?php echo SOCIAL_TYPE_USER;?>",
		"redirectUrl": "<?php echo base64_encode($user->getPermalink(false));?>"
	});

	$('[data-profile-cover]').implement(EasySocial.Controller.Cover, {
		"uid": "<?php echo $user->id;?>",
		"type": "<?php echo SOCIAL_TYPE_USER;?>"
	});
});