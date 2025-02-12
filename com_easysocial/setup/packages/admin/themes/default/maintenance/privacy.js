
EasySocial.ready(function($) {

$(document)
.on('click', '[data-start]', function() {
	var button = $(this);
	var self = this;

	this.progress = $('[data-progress]');
	this.progressBar = $('[data-progress-bar]');
	this.progressBox = $('[data-progress-box]');
	this.progressPercentage = $('[data-progress-percentage]');
	this.completeButton = $('[data-complete]');

	button.hide();
	self.progress.show();

	this.counter = 0;
	this.total = 0;
	

	EasySocial.ajax('admin/controllers/maintenance/getPrivacyStats')
	.done(function(counts) {
		self.counter = counts;
		self.total = counts;
		self.execute();
	});

	this.execute = function() {
		if (self.counter === 0) {
			return self.completed();
		}

		EasySocial.ajax('admin/controllers/maintenance/synchronizePrivacy', {
			current: self.counter
		}).done(function(counts) {

			self.counter = counts;

			var percentage = Math.floor((self.counter/self.total) * 100) + '%';

			self.progressBar.css('width', percentage);
			self.progressPercentage.text(percentage);
			self.execute();
		});
	};

	this.completed = function() {
		self.progressBar.css('width', '100%');
		self.progressPercentage.text('100%');

		self.progressBox
			.removeClass('progress-info')
			.addClass('progress-success');

		self.completeButton.removeClass('t-hidden');
	};
});


});