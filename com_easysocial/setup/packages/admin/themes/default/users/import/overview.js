EasySocial.require()
.done(function($) {

	var successCounter = 0;
	var failedCounter = 0;

	var importButton = $('[data-user-import-begin]');
	var tableFilter = $('[data-table-filter]');

	function start() {
		EasySocial.ajax('admin/controllers/users/importUser', {
			total: '<?php echo $total; ?>',
			field_ids: '<?php echo addslashes(json_encode($fieldIds)); ?>',
			profile_id: '<?php echo $profile->id; ?>',
			importOptions: '<?php echo addslashes(json_encode($importOptions)); ?>',
			limit: 20
		}).done(function(status, complete) {
			// Add warning if user trying to leave the page during the import process.
			$(window).on('beforeunload', function(event) {
				event.preventDefault();
				return false;
			});

			percentage = status.progress + '%';

			// Update progress bar
			$('[data-progress-text]').html(percentage);
			$('[data-progress-bar]').css('width', percentage);

			// update counter
			successCounter = successCounter + status.totalSuccess;
			failedCounter = failedCounter + status.totalFailure;

			$('[data-import-counter-success]').html(successCounter);
			$('[data-import-counter-failed]').html(failedCounter);

			var contentSuccess = $('[data-table-import][data-type="success"]').find('[data-table-content]');
			var contentFailed = $('[data-table-import][data-type="failed"]').find('[data-table-content]');

			if (status.success) {
				$.each(status.success, function(idx, item) {
					$.buildHTML(item.content).appendTo(contentSuccess);
				});
			}

			if (status.failed) {
				$.each(status.failed, function(idx, item) {
					$.buildHTML(item.content).appendTo(contentFailed);
				});
			}

			// Re-start cycle
			if (!complete) {
				start();
			}

			if (complete) {
				// unbind the window event so that it will not prompt user
				// to choose 'stay' or leave.
				$(window).off('beforeunload');
				$(window).unbind('beforeunload');

				$('[data-import-progress]').addClass('t-hidden');
				$('[data-import-success]').removeClass('t-hidden');
			}
		});
	}

	importButton.on('click', function() {
		$('[data-import-overview]').addClass('t-hidden');
		$('[data-import-processing]').removeClass('t-hidden');
		$('[data-import-summary]').removeClass('t-hidden');

		start();
	});

	tableFilter.on('click', function(ev) {
		var type = $(this).data('type');

		tableFilter.removeClass('active');
		$(this).addClass('active');

		$('[data-table-import]').removeClass().addClass('t-hidden');
		$('[data-table-import][data-type="' + type + '"]').removeClass('t-hidden');
	});
});
