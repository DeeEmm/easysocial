
EasySocial.require()
.done(function($) {

EasySocial.Controller( 'Albums.All.Browser', {

	defaultOptions: {
		"{sortItem}": "[data-sorting]",
		"{sorting}": "input[name='sorting']",
		"{contents}": "[data-contents]",

		"{wrapper}" : "[data-wrapper]"
	}
}, function(self, opts) {
	return{
		init: function() {
		},

		"{sortItem} click" : function(sortItem, event) {

			// Get the sort type
			var type = sortItem.data('type');

			var filter = sortItem.data('filter');

			if (filter != 'favourite') {
				filter = 'all';
			}

			// Route the item so that we can update the url
			sortItem.route();

			// self.result().addClass('is-loading');
			// self.list().html('');

			// Set loading state for the content
			self.wrapper().addClass('is-loading');
			self.contents().html('&nbsp;');

			// Run the ajax call now
			EasySocial.ajax('site/controllers/albums/getAlbums', {
				"sort": type,
				"filter": filter
			}).done(function(contents) {
				self.wrapper().removeClass('is-loading');
				self.contents().html(contents);
			});
		}
	}
});


$('[data-albums]').implement(EasySocial.Controller.Albums.All.Browser);


});
