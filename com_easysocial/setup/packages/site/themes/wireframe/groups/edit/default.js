
EasySocial.require()
.script('site/groups/edit')
.done(function($) {
	$('[data-groups-edit]').implement(EasySocial.Controller.Groups.Edit, {
		"id" : "<?php echo $group->id;?>"
	});
});
