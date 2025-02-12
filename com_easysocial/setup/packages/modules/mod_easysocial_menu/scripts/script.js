EasySocial
.require()
.done(function($) {
	$(window).on('notification.updates', function(event, data) {
		if (data) {
			var notiObj = $('.mod-es-menu');
			var conversations = notiObj.find('[data-notifications][data-type=conversations]');
			var friends = notiObj.find('[data-notifications][data-type=friends]');
			var notifications = notiObj.find('[data-notifications][data-type=notifications]');

			if (data.conversation != undefined && data.conversation.total > 0) {
				var total = data.conversation.total > 99 ? '99+' : data.conversation.total;
				conversations.find('[data-counter]').text(total);
				conversations.addClass('has-new');
			} else {
				conversations.removeClass('has-new');
			}

			if (data.friend != undefined && data.friend.total > 0) {
				var total = data.friend.total > 99 ? '99+' : data.friend.total;
				friends.find('[data-counter]').text(total);
				friends.addClass('has-new');
			} else {
				friends.removeClass('has-new');
			}

			if (data.system != undefined && data.system.total > 0) {
				var total = data.system.total > 99 ? '99+' : data.system.total;
				notifications.find('[data-counter]').text(total);
				notifications.addClass('has-new');
			} else {
				notifications.removeClass('has-new');
			}
		}
	});
});
