EasySocial.require()
.script('admin/discovery/discovery')
.done(function($){

	// Implement discover controller.
	$('[data-privacy-discover]').implement(EasySocial.Controller.Admin.Discovery, {
		"namespaces": {
			"discover": "admin/controllers/privacy/discoverFiles",
			"install": "admin/controllers/privacy/scan"
		},
		"messages" : {
			"completed": "<?php echo JText::_('COM_EASYSOCIAL_SCAN_COMPLETED', true);?>"
		}
	});
	
});