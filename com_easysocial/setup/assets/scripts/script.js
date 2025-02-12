$(document).ready(function(){
	loading = $('[data-installation-loading]'),
	submit = $('[data-installation-submit]'),
	retry = $('[data-installation-retry]'),
	form = $('[data-installation-form]'),
	completed = $('[data-installation-completed]'),
	source = $('[data-source]'),
	steps = $('[data-installation-steps]');
});

var es = {
	ajaxUrl: "<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&ajax=1",
	installation: {
		path: null,
		ajaxCall: function(task, properties, callback) {

			var prop = $.extend({
							"apikey": "<?php echo $input->get('apikey', '');?>",
							"path": es.installation.path
				}, properties);

			$.ajax({
				type: "POST",
				url: es.ajaxUrl + "&controller=installation&task=" + task,
				data: prop
			}).done(function(result) {
				callback.apply(this, [result]);
			});
		},

		showRetry: function(step) {

			retry
				.data('retry-step', step)
				.removeClass('d-none');

			// Hide the submit
			submit.addClass('d-none');

			// Hide the loading
			loading.addClass('d-none');
		},

		extract: function() {

			es.installation.setActive('data-progress-extract');

			es.installation.ajaxCall('extract', {}, function(result) {

				es.installation.update('data-progress-extract', result, '10%');

				if (!result.state) {
					return false;
				}

				es.installation.path = result.path;

				es.installation.runSQL();
			});
		},

		download: function() {
			var logSelector = 'data-progress-download';
			var task = 'download';

			es.installation.setActive(logSelector);

			es.installation.ajaxCall(task, {}, function(result) {

				// Set the progress
				es.installation.update(logSelector, result);

				if (!result.state) {
					es.installation.showRetry(task);

					return false;
				}

				es.installation.path = result.path;
				es.installation.runSQL();
			});
		},

		runSQL: function() {
			// Install the SQL stuffs
			es.installation.setActive('data-progress-sql');

			es.installation.ajaxCall('sql', {}, function(result) {
				es.installation.update('data-progress-sql', result, '15%');

				if (!result.state) {
					es.installation.showRetry('runSQL');
					return false;
				}

				es.installation.installFiles();
			});
		},

		installFiles: function () {
			es.installation.setActive('data-progress-files');
			es.installation.installAdmin();
		},

		installAdmin: function() {

			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('copy', {"type" : "admin"} , function(result) {
				// Set the progress
				es.installation.update('data-progress-files', result, false);

				if (!result.state) {
					es.installation.showRetry('installAdmin');
					return false;
				}

				es.installation.installSite();
			});
		},

		installSite: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('copy', {"type" : "site"}, function(result) {
				es.installation.update('data-progress-files', result, false);

				if (!result.state) {
					es.installation.showRetry('installSite');
					return false;
				}

				es.installation.installLanguages();
			});
		},

		installLanguages: function() {
			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('copy', {"type" : "languages"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-files', result, false);

				if (!result.state) {
					es.installation.showRetry('installLanguages');
					return false;
				}

				es.installation.installMedia();
			});
		},

		installMedia: function() {
			es.installation.setActive('data-progress-files');

			es.installation.ajaxCall('copy', {"type" : "media"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-files', result, true);

				if (!result.state) {
					es.installation.showRetry('installMedia');
					return false;
				}

				es.installation.syncDB();
			});
		},

		syncDB: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-syncdb');

			es.installation.ajaxCall('sync', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-syncdb', result, '35%');

				if (!result.state) {
					es.installation.showRetry('syncDB');
					return false;
				}

				es.installation.installApps();
			});
		},

		installApps: function() {

			es.installation.setActive('data-progress-apps');
			es.installation.installUserApps();
		},

		installUserApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('apps', {"group" : "user"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-apps', result, false);

				if (!result.state) {
					es.installation.showRetry('installUserApps');
					return false;
				}

				es.installation.installGroupApps();
			});
		},

		installGroupApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('apps', {"group" : "group"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-apps', result, false);

				if (!result.state) {
					es.installation.showRetry('installGroupApps');
					return false;
				}

				es.installation.installPageApps();
			});
		},

		installPageApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('apps', {"group" : "page" }, function(result) {

				// Set the progress
				es.installation.update('data-progress-apps', result, false);

				if (!result.state) {
					es.installation.showRetry('installPageApps');
					return false;
				}

				es.installation.installEventApps();
			});
		},

		installEventApps: function() {

			// this so that if user retry, it will set this to active.
			es.installation.setActive('data-progress-apps');

			es.installation.ajaxCall('apps', {"group" : "event"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-apps', result, true);

				if (!result.state) {
					es.installation.showRetry('installEventApps');
					return false;
				}

				es.installation.installFields();
			});
		},

		installFields: function() {
			es.installation.setActive('data-progress-fields');
			es.installation.installUserFields();
		},

		installUserFields: function() {

			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('fields', {"group" : "user"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-fields', result, false);

				if (!result.state) {
					es.installation.showRetry('installUserFields');
					return false;
				}

				es.installation.installGroupFields();
			});
		},

		installGroupFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('fields', {"group" : "group"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-fields', result, false);

				if (!result.state) {
					es.installation.showRetry('installGroupFields');
					return false;
				}

				es.installation.installPageFields();
			});
		},

		installPageFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('fields', {"group" : "page"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-fields' , result , false);

				if (!result.state) {
					es.installation.showRetry('installPageFields');
					return false;
				}

				es.installation.installEventFields();
			});
		},

		installEventFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('fields', { "group" : "event" } , function(result) {
				// Set the progress
				es.installation.update('data-progress-fields' , result , true);

				if (!result.state) {
					es.installation.showRetry('installEventFields');
					return false;
				}

				es.installation.installMarketplaceFields();
			});
		},

		installMarketplaceFields: function() {
			es.installation.setActive('data-progress-fields');

			es.installation.ajaxCall('fields', { "group" : "marketplace" } , function(result) {
				// Set the progress
				es.installation.update('data-progress-fields' , result , true);

				if (!result.state) {
					es.installation.showRetry('installMarketplaceFields');
					return false;
				}

				es.installation.installJoomlaExtensions();
			});
		},

		installJoomlaExtensions: function() {
			es.installation.setActive('data-progress-plugins');
			es.installation.installPlugins();
		},

		installPlugins: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('plugins', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins', result, false);

				if (!result.state) {
					es.installation.showRetry('installPlugins');
					return false;
				}

				es.installation.installModules();
			});
		},

		installModules: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('modules', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins', result , false);

				if (!result.state) {
					es.installation.showRetry('installModules');
					return false;
				}

				es.installation.installAdminModules();
			});
		},

		installAdminModules: function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-plugins');

			es.installation.ajaxCall('adminModules', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-plugins', result, true);

				if (!result.state) {
					es.installation.showRetry('installAdminModules');
					return false;
				}

				es.installation.installCores();
			});
		},

		installCores: function () {
			es.installation.setActive('data-progress-cores');
			es.installation.installBadges();
		},

		installBadges : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('badges', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores' , result , false);

				if (!result.state) {
					es.installation.showRetry('installBadges');
					return false;
				}

				es.installation.installPoints();
			});
		},

		installPoints : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('points', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores', result, false);

				if (!result.state) {
					es.installation.showRetry('installPoints');
					return false;
				}

				es.installation.installAccess();
			});
		},

		installAccess : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('access', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores', result, false);

				if (!result.state) {
					es.installation.showRetry('installAccess');
					return false;
				}

				es.installation.installPrivacy();
			});
		},

		installPrivacy : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('privacy' , {} , function(result) {
				// Set the progress
				es.installation.update('data-progress-cores', result, false);

				if (!result.state) {
					es.installation.showRetry('installPrivacy');
					return false;
				}

				es.installation.installWorkflows();
			});
		},

		installWorkflows : function() {
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('workflows', {}, function(result) {

				es.installation.update('data-progress-cores', result, false);

				if (!result.state) {
					es.installation.showRetry('installWorkflows');
					return false;
				}

				es.installation.installProfiles();
			})
		},

		installProfiles : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('profiles', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores', result, false);

				if (!result.state) {
					es.installation.showRetry('installProfiles');
					return false;
				}

				es.installation.installAlerts();
			});
		},

		installAlerts : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-cores');

			es.installation.ajaxCall('alerts', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-cores', result, true);

				if (!result.state) {
					es.installation.showRetry('installAlerts');
					return false;
				}

				es.installation.installCategories();
			});
		},

		installCategories: function() {
			es.installation.setActive('data-progress-categories');
			es.installation.installGroupCategories();
		},

		installGroupCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('categories', {"type": "group"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-categories', result, false);

				if (!result.state) {
					es.installation.showRetry('installGroupCategories');
					return false;
				}

				es.installation.installPageCategories();
			});
		},

		installPageCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('categories', {"type": "page"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-categories', result, false);

				if (!result.state) {
					es.installation.showRetry('installPageCategories');
					return false;
				}

				es.installation.installEventCategories();
			});
		},

		installEventCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('categories' , {"type": "event"} , function(result) {
				// Set the progress
				es.installation.update('data-progress-categories', result, false);

				if (!result.state) {
					es.installation.showRetry('installEventCategories');
					return false;
				}

				es.installation.installVideoCategories();
			});
		},

		installVideoCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('categories', {"type": "video"} , function(result) {
				// Set the progress
				es.installation.update('data-progress-categories', result, false);

				if (!result.state) {
					es.installation.showRetry('installVideoCategories');
					return false;
				}

				es.installation.installAudioGenres();
			});
		},

		installAudioGenres : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('categories', {"type": "audio"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-categories', result, true);

				if (!result.state) {
					es.installation.showRetry('installAudioGenres');
					return false;
				}

				es.installation.installMarketplaceCategories();
			});
		},

		installMarketplaceCategories : function() {
			// Install the admin stuffs
			es.installation.setActive('data-progress-categories');

			es.installation.ajaxCall('categories', {"type": "marketplace"}, function(result) {
				// Set the progress
				es.installation.update('data-progress-categories', result, true);

				if (!result.state) {
					es.installation.showRetry('installMarketplaceCategories');
					return false;
				}

				es.installation.installSocialElements();
			});
		},

		installSocialElements: function () {
			es.installation.setActive('data-progress-reactions');
			es.installation.installReactions();
		},

		installReactions : function() {
			es.installation.setActive('data-progress-reactions');

			es.installation.ajaxCall('reactions', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-reactions', result, false);

				if (!result.state) {
					es.installation.showRetry('installReactions');
					return false;
				}

				es.installation.installEmoticons();
			});
		},

		installEmoticons : function() {
			es.installation.setActive('data-progress-reactions');

			es.installation.ajaxCall('emoticons', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-reactions', result, true);

				if (!result.state) {
					es.installation.showRetry('installEmoticons');
					return false;
				}

				es.installation.installToolbar();
			});
		},

		installToolbar : function() {
			es.installation.setActive('data-progress-toolbar');

			es.installation.ajaxCall('toolbar', {}, function(result) {
				// Set the progress
				es.installation.update('data-progress-toolbar', result, true);

				if (!result.state) {
					es.installation.showRetry('installToolbar');
					return false;
				}

				es.installation.postInstall();
			});
		},

		postInstall: function() {

			// Install the admin stuffs
			es.installation.setActive('data-progress-postinstall');

			es.installation.ajaxCall('post' , {} , function(result) {

				// Set the progress
				es.installation.update('data-progress-postinstall', result);

				if (!result.state) {
					es.installation.showRetry('postInstall');
					return false;
				}

				$('[data-installation-completed]').show();

				$('[data-installation-loading]').hide();

				$('[data-installation-submit]').removeClass('d-none');
				$('[data-installation-submit]').show();

				$('[data-installation-submit]').bind('click' , function(){
					$('[data-installation-form]').submit();
				});

			});
		},

		update: function(element, obj, updateState) {
			var logItem = $('[' + element + ']');
			var updateState = updateState !== undefined ? updateState : true;

			if (updateState) {
				logItem.removeClass("is-loading")
					.addClass(obj.state ? 'is-complete' : 'is-error');
			}
		},

		/**
		 * Sets an active log item
		 */
		setActive: function(item) {
			var logItem = $('[' + item + ']');

			logItem
				.removeClass('is-error is-complete')
				.addClass('is-loading');
		}
	},

	maintenance: {
		totalSyncUsers: 0,
		totalProfileUsers: 0,

		numUsers: 0,
		numProfiles: 0,

		init: function() {
			es.maintenance.processUsers();
		},

		updateProgress: function(element, value) {

			var progressBar = $('[' + element + ']').find('[data-progress-bar]');
			var progressBarResult = $('[' + element + ']').find('[data-progress-bar-result]');

			var currentWidth = value;

			if (currentWidth == undefined) {
				// update the progress bar here
				currentWidth = parseInt(progressBar[0].style.width);
				currentWidth++;
			}

			var percentage = Math.round(currentWidth);

			progressBar.css('width', percentage + '%');
			progressBarResult.html(percentage + '%');

		},

		processUsers: function() {

			$.ajax({
				'type': 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=users&method=getTotal'
			})
			.done(function(result) {
				es.maintenance.totalSyncUsers = result;
				es.maintenance.syncUsers();
			});
		},

		processProfiles: function() {
			$.ajax({
				'type': 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=profiles&method=getTotal'
			})
			.done(function(result) {
				es.maintenance.totalProfileUsers = result;
				es.maintenance.syncProfiles();
			});
		},

		syncUsers : function() {

			var progress = $('[data-users-progress]');
			var progressBar = $('[data-users-progress]').find('[data-progress-bar]');
			var progressActiveMsg = $('[data-users-progress]').find('[data-progress-active-message]');
			var progressCompleteMsg = $('[data-users-progress]').find('[data-progress-complete-message]');

			progress.removeClass('d-none');

			if (es.maintenance.totalSyncUsers == 0) {

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-users-progress', 100);

				progressActiveMsg
					.addClass('d-none');

				progressCompleteMsg
					.removeClass('d-none');

				return es.maintenance.processProfiles();
			}

			$.ajax({
				type : "POST",
				url : es.ajaxUrl + "&controller=maintenance&task=users&method=sync"
			})
			.done(function(result) {

				es.maintenance.numUsers++;
				var progressUnit = es.maintenance.totalSyncUsers - (es.maintenance.totalSyncUsers - es.maintenance.numUsers);

				// convert into percentage
				progressUnit = (progressUnit * 100) / es.maintenance.totalSyncUsers;
				var percentage = Math.round(progressUnit);

				if (percentage <= 0) {
					// lets set it atlease 1%
					percentage = 1;
				}

				es.maintenance.updateProgress('data-users-progress', percentage);

				// If there are more items to process, call itself again.
				if (result.state == 2) {
					return es.maintenance.syncUsers();
				}

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-users-progress', 100);

				progressActiveMsg
					.addClass('d-none');

				progressCompleteMsg
					.removeClass('d-none');

				es.maintenance.processProfiles();
			});
		},

		syncProfiles : function() {
			var progress = $('[data-profiles-progress]');
			var progressActiveMsg = $('[data-profiles-progress]').find('[data-progress-active-message]');
			var progressCompleteMsg = $('[data-profiles-progress]').find('[data-progress-complete-message]');

			progress.removeClass('d-none');

			if (es.maintenance.totalProfileUsers == 0) {

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-profiles-progress', 100);

				progressActiveMsg
					.addClass('d-none');

				progressCompleteMsg
					.removeClass('d-none');

				return es.maintenance.execMaintenance();
			}

			$.ajax({
				type : "POST",
				url : es.ajaxUrl + "&controller=maintenance&task=profiles&method=syncProfiles"
			})
			.done(function(result) {

				es.maintenance.numProfiles++;

				var progressUnit = es.maintenance.totalProfileUsers - (es.maintenance.totalProfileUsers - es.maintenance.numProfiles);

				// convert into percentage
				progressUnit = (progressUnit * 100) / es.maintenance.totalProfileUsers;
				var percentage = Math.round(progressUnit);

				if (percentage < 1) {
					// lets set it atlease 1%
					percentage = 1;
				}

				es.maintenance.updateProgress('data-profiles-progress', percentage);

				// If there are more items to process, call itself again.
				if (result.state == 2) {
					return es.maintenance.syncProfiles();
				}

				// If there are nothing more to do here, switch out
				es.maintenance.updateProgress('data-profiles-progress', 100);

				progressActiveMsg
					.addClass('d-none');

				progressCompleteMsg
					.removeClass('d-none');

				es.maintenance.execMaintenance();
			});
		},

		execMaintenance: function() {
			var frame = $('[data-progress-execscript]');

			frame.addClass('active').removeClass('pending');

			var progress = $('[data-sync-progress]');
			progress.removeClass('d-none');

			$.ajax({
				type: 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=scripts&method=getScripts'
			})
			.done(function(result) {

				var item = $('<li>');
				item.html(result.message);

				$('[data-script-logs]').append(item);

				es.maintenance.runScript(result.scripts, 0);
			});
		},

		runScript: function(scripts, index) {

			if (scripts[index] === undefined) {

				// run script completed. lets update the scriptversion
				$.ajax({
					type: 'POST',
					url: es.ajaxUrl + '&controller=maintenance&task=scripts&method=complete'
				}).done(function(result) {

					var item = $('<li>');
					item.html(result.message);
					$('[data-script-logs]').append(item);

					var progress = $('[data-sync-progress]');
					var progressActiveMsg = $('[data-sync-progress]').find('[data-active-message]');
					var progressCompleteMsg = $('[data-sync-progress]').find('[data-completed-message]');

					progressActiveMsg
						.addClass('d-none');

					progressCompleteMsg
						.removeClass('d-none');

					es.maintenance.updateProgress('data-sync-progress', 100);

					es.maintenance.complete();
				});

				return true;
			}

			// Get the current percentage
			var totalScripts = scripts.length;
			var percentage = (100 / totalScripts) * index;

			$.ajax({
				type: 'POST',
				url: es.ajaxUrl + '&controller=maintenance&task=scripts&method=run',
				data: {
					script: scripts[index]
				}
			})
			.always(function(result) {

				var item = $('<li>');

				item.html(result.message);

				$('[data-script-logs]').append(item);

				es.maintenance.updateProgress('data-sync-progress', percentage);
				es.maintenance.runScript(scripts, ++index);
			});
		},

		complete: function() {
			$('[data-installation-loading]').hide();
			$('[data-installation-submit]').show();

			$('[data-installation-submit]').on('click', function() {
				$('[data-installation-form]').submit();
			});
		}
	}
}
