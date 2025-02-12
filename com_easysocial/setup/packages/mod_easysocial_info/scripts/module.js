EasySocial.ready(function($) {
	var wrapper = $('[data-es-info]');
	var servicesUrl = wrapper.data('service-url');

	$(document).on('click.approve.user', '[data-approve]', function() {
		var id = $(this).data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('admin/views/users/confirmApprove', {"id": id}),
			"bindings": {

				"{approveButton} click" : function() {
					this.approveUserForm().submit();
				}
			}
		});
	});


	$(document).on('click.reject.user', '[data-reject]', function() {
		var id = $(this).data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('admin/views/users/confirmReject', {"id" : id})
		});
	});

	$.ajax({
		url: servicesUrl,
		jsonp: "callback",
		dataType: "jsonp",
		success: function(data) {

			var outdatedWidget = $('[data-version-widget=outdated]');
			var updatedWidget = $('[data-version-widget=updated]')
			var newVersion = $('[data-latest-version]');

			var version = {
				"latest": data.version,
				"installed": $('[data-es-version]').val()
			};

			var outdated = EasySocial.compareVersion(version.installed, version.latest) === -1;

			if (outdated) {
				outdatedWidget.removeClass('t-hidden');
				newVersion.html(version.latest);
			}

			if (!outdated) {
				updatedWidget.removeClass('t-hidden');
			}


			// Populate news
			var news = data.news || [];

			if (news) {
				$.each(news, function(i, article) {
					var wrapper = $('[data-news-template]').clone();
					var tmpl = $(wrapper.html());

					// Hide loading
					$('[data-news-loading]').addClass('t-hidden');

					tmpl.removeClass('t-hidden');
					tmpl.find('[data-date]').html(article.date);
					tmpl.find('[data-image]').attr('src', article.image);
					tmpl.find('[data-permalink]').attr('href', article.permalink);
					tmpl.find('[data-title]').html(article.title);
					tmpl.find('[data-content]').html(article.content);

					$('[data-news-result]').append(tmpl);
				});
			}
		}
	});
});
