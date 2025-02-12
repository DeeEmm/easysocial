EasySocial.ready(function($) {

	EasySocial.compareVersion = function(version1, version2) {
		var nRes = 0;
		var parts1 = version1.split('.');
		var parts2 = version2.split('.');
		var nLen = Math.max(parts1.length, parts2.length);

		for (var i = 0; i < nLen; i++) {
			var nP1 = (i < parts1.length) ? parseInt(parts1[i], 10) : 0;
			var nP2 = (i < parts2.length) ? parseInt(parts2[i], 10) : 0;

			if (isNaN(nP1)) {
				nP1 = 0;
			}

			if (isNaN(nP2)) {
				nP2 = 0;
			}

			if (nP1 != nP2) {
				nRes = (nP1 > nP2) ? 1 : -1;
				break;
			}
		}

		return nRes;
	}

	// Get the current version of EasySocial
	var installedVersion = $('[data-es-version]').val();

	$.ajax({
		url: "<?php echo SOCIAL_SERVICE_VERSION;?>",
		jsonp: "callback",
		dataType: "jsonp",
		data: {
			"current": installedVersion
		},
		success: function(data) {

			if (data.error) {
				$('#es.es-backend').prepend('<div style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" class="app-alert o-alert o-alert--danger"><div class="row-table"><div class="col-cell cell-tight"><i class="app-alert__icon fa fa-exclamation-circle"></i></div><div class="col-cell alert-message">' + data.error + '</div></div></div>');
			}

			// Update the latest version
			var notificationsWidget = $('[data-notifications-widget]');
			var outdatedWidget = $('[data-version-widget=outdated]');
			var updatedWidget = $('[data-version-widget=updated]')
			var version = {
				"latest": data.version,
				"installed": installedVersion
			};

			$('[data-latest-version]').html(version.latest);

			var outdated = EasySocial.compareVersion(version.installed, version.latest) === -1;

			if (outdated) {
				outdatedWidget.removeClass('t-hidden');
			}

			if (!outdated) {
				updatedWidget.removeClass('t-hidden');
			}

			// Update with banner
			var banner = $('[data-outdated-banner]');

			if (banner.length > 0 && outdated) {
				banner.removeClass('t-hidden');
			}
		}
	});
});
