EasySocial.require()
.script('admin/discovery/discovery')
.done(function($){

	// Implement discover controller.
	$('[data-alerts-discover]').implement(EasySocial.Controller.Admin.Discovery, {
		"namespaces": {
			"discover": "admin/controllers/alerts/discoverFiles",
			"install": "admin/controllers/alerts/scan"
		},
		"messages" : {
			"completed": "<?php echo JText::_('COM_EASYSOCIAL_SCAN_COMPLETED', true);?>"
		}
	});
	
});