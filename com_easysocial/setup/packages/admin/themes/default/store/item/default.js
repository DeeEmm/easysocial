EasySocial.require()
.script('admin/vendors/raty', 'admin/apps/store')
.done(function($) {
	
	$('[data-apps-store]').implement(EasySocial.Controller.Apps.Store);
	    
    $('[data-ratings]').raty({
        readOnly: true,
        score: function() {
            return $(this).data('score');
        }
    })
});