EasySocial
.require()
.script('admin/api/toolbar')
.done(function($) {

	<?php if ($this->tmpl != 'component') { ?>
	$.Joomla('submitbutton', function(task) {
		var selected = [];

		$('[data-table-grid]').find('[data-table-grid-id]:checked').each(function(i, el) {
			selected.push($(el).val());
		});

		if (task === 'create') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/events/createDialog'),
				bindings: {
					'{continueButton} click': function() {
						var categoryId = this.category().val();

						window.location = 'index.php?option=com_easysocial&view=events&layout=form&category_id=' + categoryId;
					}
				}
			});

			return false;
		}

		if (task == 'makeFeatured' || task == 'removeFeatured') {
			$('[data-table-grid-task]').val(task);

			$('[data-table-grid]').submit();

			return false;
		}

		if (task === 'delete') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/events/deleteDialog'),
				bindings: {
					'{deleteButton} click': function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		if (task === 'switchOwner') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/events/switchOwner', {
					ids: selected
				})
			});

			return false;
		}

		if (task === 'switchCategory') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/events/switchCategory', {
					ids: selected
				})
			});

			return false;
		}

		if (task === 'deletePastEvents') {


			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/easysocial/renderConfirmationDialog', {
					title: "COM_ES_PURGE_PAST_EVENTS",
					message: "COM_ES_CONFIRM_DELETE_PAST_EVENTS"
				}),
				bindings: {
					"{submitButton} click": function() {
						EasySocial.dialog({
							content: EasySocial.ajax('admin/views/events/deletePastEventsDialog'),
							bindings: {
								"{submitButton} click"	: function() {

									if (this.complete) {
										this.parent.close();

										location.reload();
										return;
									}

									this.cancelButton().addClass('t-hidden');
									this.submitButton().attr('disabled', 'disabled');
									this.submitButton()
										.html('')
										.addClass('is-loading');

									this.info().addClass('t-hidden');
									this.deleting().removeClass('t-hidden');
									this.progress().removeClass('t-hidden');

									this.deleteEvents();
								},

								updateProgressBar: function(remaining) {
									var processed = (this.total - remaining);
									var width = Math.round((processed / this.total) * 100);
									console.log('width', processed, width);

									this.processed().html(processed);
									this.progressBar().css('width', width + '%');
								},

								deleteEvents: function() {
									var self = this;

									EasySocial
										.ajax('admin/controllers/events/deletePastEvents', {
											'totalToDelete': 40
										})
										.done(function(remaining) {
											var remaining = parseInt(remaining);

											self.updateProgressBar(remaining);

											if (remaining > 0) {
												self.deleteEvents();

												return;
											}

											self.complete = true;
											self.submitButton()
												.removeAttr('disabled')
												.removeClass('is-loading')
												.html('<?php echo JText::_('COM_EASYSOCIAL_CLOSE_BUTTON', true);?>');
										});
								}
							}
						});
					}
				}
			});

			return false;
		}
		$.Joomla('submitform', [task]);
	});

	window.switchOwner = function(user, eventIds) {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/events/confirmSwitchOwner', {
				ids: eventIds,
				userId: user.id
			})
		});
	}
	<?php } else { ?>

		$('[data-event-insert]').on('click', function(event){
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id = $(this).data('id'),
				avatar = $(this).data('avatar'),
				title = $(this).data('title'),
				alias = $(this).data('alias');

				obj     = {
							"id"    : id,
							"title" : title,
							"avatar" : avatar,
							"alias" : alias
						  };

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ]( obj );
		});

	<?php } ?>
});
