EasySocial
.ready(function($) {

	var groupsList = $('[data-profile-groups]');
	var emptyGroups = $('[data-groups-empty]');

	window.insertGroup = function(group) {
		var groups = [group.id];

		emptyGroups.hide();

		EasySocial.ajax('admin/views/profiles/getClusterTemplate', {
			"clusters": groups,
			"clusterType": "groups"
		}).done(function(output) {
			groupsList.append(output);
		});

		EasySocial.dialog().close();
	};

	// Bind the insert group button
	$(document).on('click.profile.insert.group', '[data-insert-groups]', function() {

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/groups/browse', {"jscallback": "insertGroup"})
		});
	});

	// Bind the remove group button
	$(document).on('click.profile.remove.group', '[data-groups-remove]', function() {
		var elem = $(this);
		var parent = $(this).parents('[data-groups-item]');

		// Remove the parent
		parent.remove();

		var items = $('[data-groups-item]');

		if (items.length < 1) {
			emptyGroups.show();
		}
	});
});
