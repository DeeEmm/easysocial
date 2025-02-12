EasySocial.require()
.script('admin/discovery/discovery')
.done(function($){

	// Implement discover controller.
	$('[data-badges-discover]').implement(EasySocial.Controller.Admin.Discovery, {
		"namespaces": {
			"discover": "admin/controllers/badges/discoverFiles",
			"install": "admin/controllers/badges/scan"
		},
		"messages" : {
			"completed": "<?php echo JText::_('COM_EASYSOCIAL_SCAN_COMPLETED', true);?>"
		}
	});

});