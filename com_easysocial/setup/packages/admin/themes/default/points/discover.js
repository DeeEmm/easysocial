EasySocial.require()
.script('admin/discovery/discovery')
.done(function($){

	// Implement discover controller.
	$('[data-points-discover]').implement(EasySocial.Controller.Admin.Discovery, {
		"namespaces": {
			"discover": "admin/controllers/points/discoverFiles",
			"install": "admin/controllers/points/scan"
		},
		"messages" : {
			"completed": "<?php echo JText::_('COM_EASYSOCIAL_SCAN_COMPLETED', true);?>"
		}
	});
	
});