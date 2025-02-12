
EasySocial.require()
.script('admin/api/toolbar')
.library('scrollTo')
.done(function($) {

	// Append the settings search to the toolbar
	var searchWrapper = $('[data-search-wrapper]');
	var searchResult = $('[data-search-result]');
	var searchInput = $('[data-settings-search]');

	searchWrapper
		.appendTo('#toolbar')
		.removeClass('hidden');


	searchInput.on('keyup', $.debounce(function() {
		var search = $(this).val();

		if (search === "") {
			searchResult.addClass('hidden');

			return;
		}

		EasySocial.ajax('admin/views/settings/search', {
			'text': search
		}).done(function(output) {
			searchResult
				.html(output)
				.removeClass('hidden');
		});

	}, 250));

	$('body').on('click', function(event) {
		var target = $(event.target);

		if (target.is(searchInput) || target.is(searchResult) || target.is(searchWrapper) || target.parents().is(searchResult)) {
			return;
		}

		searchResult.addClass('hidden');
	});

	<?php if ($goto) { ?>
	var element = $('#<?php echo $goto;?>');
	var wrapper = element.parents('.form-group');

	wrapper.css({
		'background': '#fff9c4',
		'transition': 'background 1.0s ease-in-out'
	});

	var resetBackground = function() {
		wrapper.css({
			'background': '#fff'
		});
	};

	setInterval(function() {
		resetBackground();
	}, 5000);

	$.scrollTo(element);
	<?php } ?>

	$('[data-image-restore]').on('click', function() {
		var parent = $(this).parent();
		var type = $(this).data('type');
		var image = parent.siblings('[data-image-source]');

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/settings/confirmRestoreImage', {type: type}),
			bindings: {
				'{restoreButton} click': function() {

					EasySocial.ajax('admin/controllers/settings/restoreImage', {type: type}).done(function() {
						parent.addClass('t-hidden');

						image.attr('src', image.data('default'));
						EasySocial.dialog().close();
					});
				}
			}
		});
	});

	// Bind the active tab so that we know which page to redirect the user to
	$(document)
	.on('click.settings.tabs', '[data-es-toggle]', function() {
		var tab = $(this);
		var id = tab.attr('href').replace('#', '');

		$('[data-active-tab]').val(id);
	});

	$.Joomla('submitbutton', function(task) {

		if (task == 'reset') {

			EasySocial.dialog({
				"content": EasySocial.ajax("admin/views/settings/confirmReset", { "section" : "<?php echo $page;?>"} ),
				"bindings": {
					"{resetButton} click" : function() {
						this.resetForm().submit();
					}
				}
			});

			return false;
		}

		if (task == 'rebuildSearch') {
			window.location = "<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=settings&layout=rebuildSearch";
			return;
		}

		if (task == 'export') {
			$.download( '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=settings&format=raw&layout=export&tmpl=component' );
			return false;
		}

		if (task == 'import') {
			EasySocial.dialog(
			{
				"content": EasySocial.ajax( "admin/views/settings/import" , { "page" : "<?php echo $page;?>"}),
				"bindings":  {
					"{submitButton} click" : function() {
						this.importForm().submit();
					}
				}
			});
		}

		if (task == 'apply') {
			$.Joomla('submitform', [task]);
			return;
		}

		if (task == 'purgeTextAvatars') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/settings/confirmPurgeTextAvatars'),
				"bindings": {
					"{submitButton} click": function() {
						this.form().submit();
					}
				}
			})
		}

		return false;
	});

});
