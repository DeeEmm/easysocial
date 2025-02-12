EasySocial.require()
.script("site/friends/suggest")
.library("textboxlist")
.done(function($) {

	$('[data-friends-suggest]').addController(EasySocial.Controller.Friends.Suggest, {
		"clusterId" : <?php echo $groupId; ?>,
		"clusterType": 'groups',
		"name": 'permission_users[]'
	});

	$('[data-es-stream-permission-member]').on('change', function() {
		var button = $(this);

		if (button.is(':checked')) {
			$('[data-es-stream-permission-profile]').show();
		} else {
			$('[data-es-stream-permission-profile]').hide();
		}
	});

	$('[data-member-type]').on('change', function() {
		var value = $(this).val();

		if (value == 'selected') {
			$('[data-es-stream-profile-type]').removeClass('t-hidden');
			$('[data-es-stream-selected_members]').addClass('t-hidden');
		} else if (value == 'selectedUsers') {
			$('[data-es-stream-profile-type]').addClass('t-hidden');
			$('[data-es-stream-selected_members]').removeClass('t-hidden');
		} else {
			$('[data-es-stream-profile-type]').addClass('t-hidden');
			$('[data-es-stream-selected_members]').addClass('t-hidden');
		}
	});
})
