EasySocial.ready(function($) {
$(document)
	.off('click.notes.create')
	.on('click.notes.create', '[data-notes-create]', function() {
		var wrapper = $(this).parents('[data-profile-user-apps-notes]');
		var appId = '<?php echo $app->id; ?>';
		var contentWrapper = $('[data-notes-list]');

		EasySocial.dialog({
			"content": EasySocial.ajax('apps/user/notes/controllers/notes/form' , {}),
			"bindings": {
				"{createButton} click" : function() {
					EasySocial.ajax('apps/user/notes/controllers/notes/store', {
						"title": this.noteTitle().val(),
						"content": this.content().val(),
						"appId": appId,
						"stream": this.stream().is( ':checked' ) ? '1' : '0'
					}).done(function(item) {

						contentWrapper.removeClass('is-empty');
						$.buildHTML(item).prependTo(contentWrapper);

						EasySocial.dialog().close();
					});
				}
			}
		});
	});

$(document)
	.off('click.notes.edit')
	.on('click.notes.edit', '[data-notes-item] [data-edit]', function() {
		var button = $(this);
		var wrapper = button.parents('[data-profile-user-apps-notes]');
		var item = button.parents('[data-notes-item]');
		var id = item.data('id');
		var appId = '<?php echo $app->id; ?>';

		EasySocial.dialog({
			"content": EasySocial.ajax('apps/user/notes/controllers/notes/form', { "id": id }),
			"bindings": {

				"{createButton} click" : function() {
					EasySocial.ajax('apps/user/notes/controllers/notes/store', {
						"id": id,
						"title": this.noteTitle().val(),
						"content": this.content().val(),
						"appId": appId,
						"stream": this.stream().is( ':checked' ) ? '1' : '0'
					}) .done(function(newItem) {

						item.replaceWith(newItem);

						EasySocial.dialog().close();
					});
				}
			}
		});
	});

$(document)
	.off('click.notes.delete')
	.on('click.notes.delete', '[data-notes-item] [data-delete]', function() {
		var button = $(this);
		var item = button.parents('[data-notes-item]');
		var id = item.data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax( 'apps/user/notes/controllers/notes/confirmDelete' ),
			"bindings": {
				"{deleteButton} click" : function() {
					EasySocial.ajax( 'apps/user/notes/controllers/notes/delete', {
						"id": id
					}).done(function() {
						item.remove();

						EasySocial.dialog().close();
					});
				}
			}
		});
	});
});
