
EasySocial.require()
.script('site/profile/edit')
.done(function($){

	$('[data-profile-edit]').implement(EasySocial.Controller.Profile.Edit, {
		userid: <?php echo $this->my->id; ?>,
		saveLogic: "<?php echo $this->config->get('users.profile.editLogic', 'default'); ?>"
	});
});
