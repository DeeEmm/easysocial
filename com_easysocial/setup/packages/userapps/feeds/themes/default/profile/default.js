EasySocial.ready(function($) {

	$('[data-feeds-create]').on('click', function() {
		EasySocial.dialog({
			"content": EasySocial.ajax("apps/user/feeds/views/feeds/form" , { 'id' : '<?php echo $app->id;?>' }),
			"bindings": {
				"{saveButton} click": function() {
					
					var title = this.title().val();
					var url = this.url().val();
					var notice = this.notice();

					notice.removeClass('alert alert-error').addClass('t-hidden');

					if (title.trim().length == 0) {
						notice.text('<?php echo JText::_('APP_FEEDS_TITLE_EMPTY', true);?>');
						notice.addClass('alert alert-error');
						notice.removeClass('hide');
						return;
					}

					if (url.trim().length == 0) {
						notice.text('<?php echo JText::_('APP_FEEDS_URL_EMPTY', true);?>');
						notice.addClass('alert alert-error');
						notice.removeClass('hide');
						return;
					}

					EasySocial.ajax('apps/user/feeds/controllers/feeds/save', {
						"title": title,
						"url": url
					}).done(function(contents) {
						
						// Close dialog
						EasySocial.dialog().close();

						$('[data-feeds]').removeClass('is-empty');

						$('[data-feeds-list]').append(contents);
					});
				}
			}
		});
	});

	$(document)
		.on('click', '[data-feeds-item-remove]', function() {
			var button = $(this);
			var item = button.parents('[data-item]');
			var id = item.data('id');

			EasySocial.dialog({
				content	: EasySocial.ajax("apps/user/feeds/views/feeds/confirmDelete" , { 'id' : '<?php echo $app->id;?>' } ),
				bindings : {
					"{deleteButton} click" : function() {
						EasySocial.ajax( 'apps/user/feeds/controllers/feeds/delete', {
							"id": "<?php echo $app->id;?>",
							"feedId": id
						}).done(function() {
							EasySocial.dialog().close();

							item.remove();

							var totalItems = $('[data-feeds-lists]').children().length;

							if (totalItems == 0) {
								$('[data-feeds]').addClass('is-empty');
							}
						});
					}
				}
			});
		});
});