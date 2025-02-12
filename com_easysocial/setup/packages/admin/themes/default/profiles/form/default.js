EasySocial.require()
.script('admin/profiles/form')
.done(function($){
	var profileId = <?php echo !empty( $profile->id ) ? $profile->id : 0; ?>;

	$('[data-profile-form]').addController('EasySocial.Controller.Profiles.Profile', {
		id: profileId
	});

	$('[data-label-font]').on('change', function() {
		var checked = $(this).is(':checked');
		var wrapper = $('[data-label-font-wrapper]');

		if (checked) {
			wrapper.removeClass('t-hidden');
			return;
		}

		wrapper.addClass('t-hidden');
		return;
	});

	$('[data-label-background]').on('change', function() {
		var checked = $(this).is(':checked');
		var wrapper = $('[data-label-background-wrapper]');

		if (checked) {
			wrapper.removeClass('t-hidden');
			return;
		}

		wrapper.addClass('t-hidden');
		return;
	});

	$('[data-profile-badge-type]').on('change', function(){
		var type = $(this).val();
		var badgeImage = $('[data-profile-badge-image-wrapper]');
		var badgeIcon = $('[data-profile-badge-icon-wrapper]');

		badgeImage.addClass('t-hidden');
		badgeIcon.addClass('t-hidden');

		if (type == 'image') {
			badgeImage.removeClass('t-hidden');
		}

		if (type == 'icon') {
			badgeIcon.removeClass('t-hidden');
		}
	});

	$('[data-profile-badge-image-remove-button]').on('click', function(){
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/profiles/confirmRestoreProfileBadgeImage'),
			bindings: {
				"{restoreButton} click": function() {
					EasySocial.ajax('admin/controllers/profiles/restoreProfileBadgeImage', {"id": profileId})
					.done(function() {
						EasySocial.dialog().close();

						var redirectUrl = 'index.php?option=com_easysocial&view=profiles&layout=form&id=1';

						if (profileId) {
							redirectUrl + '&id=' + profileId;
						}

						window.location = redirectUrl;
					});
				}
			}
		});
	});

	$.Joomla('submitbutton', function(task) {

		<?php if ($profile->id) { ?>
		var performSave = function(id) {
			$.Joomla('submitform', [task]);
			return;
		}

		var validateUploadSize = function() {

			var hasError = false;

			$('[data-maxupload-check]').each(function(idx, ele) {

				var maxvalue = $(this).data('maxupload');
				var key = $(this).data('maxupload-key');
				var curvalue = $(this).val();

				if (curvalue > maxvalue) {

					hasError = true;

					EasySocial.dialog({
						content: EasySocial.ajax('admin/views/profiles/getAclErrorDialog', {"key": key})
					});
				}
			});

			if (hasError) {
				return false;
			}

			return true;
		}

		if (task == 'save' || task == 'savenew' || task == 'apply') {
			if (validateUploadSize()) {
				performSave(<?php echo $profile->id; ?>);
			}

			return false;
		}

		if (task == 'savecopy') {
			// Make ajax call to create copy of profile
			EasySocial.ajax('admin/controllers/profiles/createBlankProfile')
				.done(function(id) {

					// lets update the form element cid value.
					var input = $('input[name="cid"]');
					input.attr( 'value', id );
					performSave(id);
				});

			return false;
		}
		<?php } ?>

		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=profiles';
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
