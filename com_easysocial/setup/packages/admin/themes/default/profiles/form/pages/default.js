EasySocial
.ready(function($) {

	var pagesList = $('[data-profile-pages]');
	var emptyPages = $('[data-pages-empty]');

	window.insertPage = function(page) {
		var pages = [page.id];

		emptyPages.hide();

		EasySocial.ajax('admin/views/profiles/getClusterTemplate', {
			"clusters": pages,
			"clusterType": "pages"
		}).done(function(output) {
			pagesList.append(output);
		});

		EasySocial.dialog().close();
	};

	// Bind the insert page button
	$(document).on('click.profile.insert.page', '[data-insert-pages]', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/pages/browse', {"jscallback": "insertPage"})
		});
	});

	// Bind the remove page button
	$(document).on('click.profile.remove.page', '[data-pages-remove]', function() {
		var elem = $(this);
		var parent = $(this).parents('[data-pages-item]');

		// Remove the parent
		parent.remove();

		var items = $('[data-pages-item]');

		if (items.length < 1) {
			emptyPages.show();
		}
	});
});
