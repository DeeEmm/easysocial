EasySocial
.require()
.script('admin/users/users')
.done(function($){

	<?php if($this->tmpl != 'component') { ?>

	$('[data-activate-user]').on('click' , function() {
		$(this).parents('[data-user-item]').find('[data-table-grid-id]').prop('checked' , 'checked');

		// Submit the form.
		$.Joomla('submitform' , ['activate']);
	});

	$.Joomla('submitbutton' , function(task) {
		var selected = new Array;

		if (task == 'add') {

			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/users/newUserForm'),
				bindings: {
					'{continueButton} click' : function() {
						var selectedProfile = this.profile().val();

						window.location.href = 'index.php?option=com_easysocial&view=users&layout=form&profileId=' + selectedProfile;
					}
				}
			});

			return false;
		}

		$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i , el ){
			selected.push($(el).val());
		});

		if (task == 'switchProfile') {
			EasySocial.dialog(
			{
				content: EasySocial.ajax('admin/views/users/switchProfileForm' , { 'ids' : selected }),
				bindings: {
					'{submitButton} click' : function()
					{
						this.form().submit();
					}
				}
			});

			return false;
		}

		if (task == 'assign') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/users/assign' , { 'ids' : selected }),
				bindings: {
					'{assignButton} click' : function()
					{
						this.assignForm().submit();
					}
				}
			});

			return false;
		}

		if (task == 'assignPoints') {
			// Ask if the admin wants to assign a custom message for this badge
			EasySocial.dialog({
				content	: EasySocial.ajax('admin/views/users/assignPoints', {"uid": selected}),
				bindings: {
					'{doneButton} click' : function() {
						this.form().submit();
					}
				}
			});

			return false;
		}

		if (task == 'assignBadge') {

			window.assignBadge = function(obj , uids) {

				// Ask if the admin wants to assign a custom message for this badge
				EasySocial.dialog({
					"content": EasySocial.ajax('admin/views/users/assignBadgeMessage', {"id": obj.id, "uid": uids}),
					bindings: {
						'{doneButton} click' : function() {
							this.assignForm().submit();
						}
					}
				});
			}

			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/users/assignBadge', {"ids": selected})
			});

			return false;
		}

		if (task == 'remove') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/users/confirmDelete' , { 'id' : selected })
			});

			return false;
		}

		if (task == 'add') {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=users&layout=form';
			return false;
		}

		// Submit the form.
		$.Joomla('submitform' , [task]);
	});

	<?php } else { ?>

		<?php if ($multiple) { ?>
			$('[data-user-insert]').on('click', function(event) {
				event.preventDefault();
			});

			var container = $('[data-users]'),
				checkAll = container.find('[data-table-grid-checkall]'),
				checks = container.find('[data-table-grid-id]');

			checkAll.on('change', function(event) {
				checks.trigger('change');
			});

			checks.on('change', function(event) {
				var checkbox = $(this);

				// Set timeout here to wait for Joomla to change the value of the checkbox first
				// This is to cater for the "checkAll"
				setTimeout(function() {
					var item = checkbox.parents('[data-user-item]'),
						obj = {
							'id': item.data('id'),
							'avatar': item.data('avatar'),
							'alias': item.data('alias'),
							'title': item.data('title'),
							'name': item.data('name'),
							'state': checkbox.is(':checked')
						},
						args = [ obj <?php echo $this->input->get('callbackParams', '', 'default') != '' ? ',' . ES::json()->encode($this->input->get('callbackParams', '', 'default')) : '';?>];

					window.parent['<?php echo $this->input->get('jscallback', '', 'cmd'); ?>'].apply(null, args);
				}, 1);
			});

			// $('[data-users]').on('populate', function() {
			// 	console.log($(this).find('data-table-grid-id:checked').length);
			// });
		<?php } else { ?>
			$('[data-user-insert]').on('click', function(event) {
				event.preventDefault();

				var item = $(this),

					// Supply all the necessary info to the caller
					obj = {
						'id'	: item.data('id'),
						'name'	: item.data('name'),
						'title'	: item.data('title'),
						'avatar': item.data('avatar'),
						'alias'	: item.data('alias')
					},

					args = [ obj <?php echo $this->input->get('callbackParams', '', 'default') != '' ? ',' . ES::json()->encode($this->input->get('callbackParams', '', 'default')) : '';?>];

				window.parent['<?php echo $this->input->get('jscallback', '', 'cmd');?>'].apply(null, args);
			});
		<?php } ?>

	<?php } ?>
});
