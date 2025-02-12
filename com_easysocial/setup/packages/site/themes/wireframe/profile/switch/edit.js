
EasySocial.require()
.script('site/profile/edit')
.done(function($){
	$('[data-profile-edit]').implement(EasySocial.Controller.Profile.Edit, {
		userid: <?php echo $this->my->id; ?>
	});
});
