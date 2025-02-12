
EasySocial.ready(function($) {

	$.Joomla( 'submitbutton' , function( action ) {

		if (action == 'purge') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/stream/confirmDelete'),
				bindings: {
					"{deleteButton} click" : function() {
						$.Joomla('submitform', [action]);
					}
				}
			});
			return false;
		} else if (action == 'restore' || action == 'restoreTrash') {
			EasySocial.dialog(
			{
				content		: EasySocial.ajax( 'admin/views/stream/confirmRestore' ),
				bindings 	:
				{
					"{restoreButton} click" : function()
					{
						$.Joomla( 'submitform' , [ action ] );
					}
				}
			});
			return false;
		} else if (action == 'archive') {
			EasySocial.dialog(
			{
				content		: EasySocial.ajax( 'admin/views/stream/confirmArchive' ),
				bindings 	:
				{
					"{archiveButton} click" : function()
					{
						$.Joomla( 'submitform' , [ action ] );
					}
				}
			});
			return false;
		} else if (action == 'trash') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/stream/confirmTrash'),
				bindings: {
					"{trashButton} click" : function() {
						$.Joomla('submitform', [action]);
					}
				}
			});
			return false;
		}

		$.Joomla( 'submitform' , [ action ] );
	});
});
