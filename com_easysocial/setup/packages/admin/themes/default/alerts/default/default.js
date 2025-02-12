EasySocial
.require()
.done(function($){

	$.Joomla('submitbutton' , function(task) {
		var selected 	= new Array;

		$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i , el ){
			selected.push($(el).val());
		});

		if (task == 'toggleEmailPublish' || task == 'toggleSystemPublish') {
			EasySocial.dialog(
			{
				content: EasySocial.ajax('admin/views/alerts/resetUserConfirmation' , { 'ids' : selected, 'task' : task }),
				bindings: {
					'{yesButton} click' : function()
					{
						this.resetInput().val('1');
						this.form().submit();
					},
					'{noButton} click' : function()
					{
						this.resetInput().val('0');
						this.form().submit();
					}
				}
			});

			return false;
		}

		// Submit the form.
		$.Joomla('submitform' , [task]);
	});

});
