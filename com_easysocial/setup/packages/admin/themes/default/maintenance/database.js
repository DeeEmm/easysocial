
EasySocial.ready(function($) {

$(document)
.on('click', '[data-start]', function() {
	var button = $(this);
	var self = this;

	this.progress = $('[data-progress]');
	this.progressBar = $('[data-progress-bar]');
	this.progressBox = $('[data-progress-box]');
	this.progressPercentage = $('[data-progress-percentage]');

	button.hide();
	self.progress.show();

	this.counter = 0;
	this.versions = [];
	

	EasySocial.ajax('admin/controllers/maintenance/getDatabaseStats')
	.done(function(versions) {
		self.versions = versions;
		self.execute();
	});

	this.execute = function() {
		if (self.versions[self.counter] === undefined) {
			return self.completed();
		}

		EasySocial.ajax('admin/controllers/maintenance/synchronizeDatabase', {
			version: self.versions[self.counter]
		}).done(function() {
			self.counter++;

			var percentage = Math.floor((self.counter/self.versions.length) * 100) + '%';

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
	};
});


});