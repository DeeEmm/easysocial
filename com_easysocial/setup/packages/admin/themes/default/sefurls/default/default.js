EasySocial
.require()
.script('admin/api/toolbar')
.done(function($){
	$.Joomla('submitbutton', function(task) {

		if (task == 'remove') {
			EasySocial.dialog({
				content : EasySocial.ajax( 'admin/views/sefurls/confirmDelete' ),
				bindings : {
					"{deleteButton} click" : function() {
						Joomla.submitform([task ]);
					}
				}
			});
			return false;
		}

		if (task == 'purgeAll') {
			EasySocial.dialog({
				content : EasySocial.ajax( 'admin/views/sefurls/confirmPurge' ),
				bindings : {
					"{purgeButton} click" : function() {
						Joomla.submitform([task]);
					}
				}
			});
			return false;
		}

		$.Joomla('submitform' , [task]);
	})
})
