
EasySocial.ready(function($) {

	$('[data-es-filter-form] [data-delete]').on('click', function() {

		var fid = $(this).data('id');
		var uid = $(this).data('uid');
		var utype = $(this).data('utype');

		var controllerPath = 'site/controllers/stream/deleteFilter';

		if (uid && utype != 'user') {
			var controllerPath = 'site/controllers/' + utype + 's/deleteFilter';
		}

		EasySocial.dialog({
			content		: EasySocial.ajax( 'site/views/stream/confirmFilterDelete' ),
			bindings	:
			{
				"{deleteButton} click" : function()
				{
					EasySocial.ajax( controllerPath,
					{
						"id"		: fid,
						"uid" 		: uid,
						"utype"		: utype
					})
					.done(function( html )
					{
						// close dialog box.
						EasySocial.dialog().close();
					});
				}
			}
		});
	});

	$('[data-es-filter-form] [data-save]').on('click', function() {

		var wrapper = $(this).parents('[data-es-filter-form]');
		var notice = wrapper.find('[data-notice]');

		notice.html('');
		notice.addClass('t-hidden');

		// Check the form
		var title = wrapper.find('[data-title] > input[name="title"]');

		if (title.val() == '') {
			title.parents('[data-title]').addClass('t-error');

			notice.html('<?php echo JText::_('COM_EASYSOCIAL_STREAM_FILTER_WARNING_TITLE_EMPTY', true);?>');
			notice.removeClass('t-hidden');
			
			return false;
		}

		var hashtag = wrapper.find('[data-hashtag] > input[name="hashtag"]');

		if (hashtag.val() == '') {
			hashtag.parents('[data-hashtag]').addClass('t-error');

			notice.html('<?php echo JText::_('COM_EASYSOCIAL_STREAM_FILTER_WARNING_HASHTAG_EMPTY', true);?>');
			notice.removeClass('t-hidden');
			return false;
		}

		var form = wrapper.find('form');
		form.submit();
	});

});