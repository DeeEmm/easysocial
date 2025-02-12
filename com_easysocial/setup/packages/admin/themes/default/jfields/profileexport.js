
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectProfile = function(obj) {

		$('[data-jfield-profile-title]').val(obj.title);
		$('[data-jfield-profile-value]').val(obj.id);

		// here we need to fetch the custom fields and display in 'Custom Fields' tab.
		if (obj.id) {
			window.fetchExportFields(obj.id);
		}

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-profile-remove]').on('click', function() {
		$('[data-jfield-profile-title]').val('');
		$('[data-jfield-profile-value]').val('');
	});

	$('[data-jfield-profile]').on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax( 'admin/views/profiles/browse', {
								'dialogTitle': '<?php echo JText::_('COM_EASYSOCIAL_USERS_BROWSE_USERS_DIALOG_TITLE');?>',
								'jscallback': 'selectProfile'
							})
		});
	});

});
