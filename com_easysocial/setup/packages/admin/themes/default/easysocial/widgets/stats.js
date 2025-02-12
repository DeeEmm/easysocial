EasySocial.require()
.done(function($) {

	// Get the current version of EasySocial
	var installedVersion = "<?php echo ES::getLocalVersion();?>";

	$.ajax({
		url: "<?php echo SOCIAL_SERVICE_VERSION;?>",
		jsonp: "callback",
		dataType: "jsonp",
		data: {
			"current": installedVersion
		},
		success: function(data) {

			if (data.error) {
				$('#es.es-backend').prepend('<div style="margin-bottom: 0;padding: 15px 24px;font-size: 12px;" class="app-alert o-alert o-alert--danger"><div class="row-table"><div class="col-cell cell-tight"><i class="app-alert__icon fa fa-bolt"></i></div><div class="col-cell alert-message">' + data.error + '</div></div></div>');
			}

			// Update the latest version
			var notificationsWidget = $('[data-notifications-widget]');
			var outdatedWidget = $('[data-version-widget=outdated]');
			var updatedWidget = $('[data-version-widget=updated]')
			var versionSection = $('[data-version-status]');
			var latestVersion = $('[data-latest-version]');
			var installedSection = $('[data-version-installed]');

			var version = {
				"latest": data.version,
				"installed": installedVersion
			};

			latestVersion.html(version.latest);

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
