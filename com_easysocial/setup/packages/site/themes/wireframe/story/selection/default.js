EasySocial.ready(function($) {

var selector = $('[data-story-selection] [data-selection]');
var searchInput = $('[data-story-selection] [data-selection-search]');
var tab = $('[data-story-selection] [data-selection-tab]');
var firstTab = selector.first().data('selection');

var getActiveSelectionType = function() {
	var activeSelector = selector.filter('.active');

	return activeSelector.data('selection');
};

// Always get the first tab as active tab for the first load
selector.first().addClass('active');
tab.filter('[data-selection-tab=' + firstTab + ']').removeClass('t-hidden');

var getActiveSelectionTab = function(type) {
	var type = !type ? getActiveSelectionType() : type;
	var activeTab = tab.filter('[data-selection-tab=' + type + ']');

	return activeTab;
};

searchInput.on('keyup', $.debounce(function() {

	var value = $(this).val();
	var type = getActiveSelectionType();
	var activeTab = getActiveSelectionTab();

	activeTab.addClass('is-loading');

	EasySocial.ajax('site/views/story/searchSelection', {
		'query': value,
		'type': type
	}).done(function(output) {
		activeTab.html(output);
	}).always(function() {
		activeTab.removeClass('is-loading');
	});

}, 300));

selector.on('click', function() {
	var activeSelector = $(this);
	var type = activeSelector.data('selection');
	var activeTab = getActiveSelectionTab(type);

	// Remove any search input
	searchInput.val('');

	// Add active state to the tab selector
	selector.removeClass('active');
	activeSelector.addClass('active');

	// Add active state to the tab contents
	tab.addClass('t-hidden');
	activeTab.removeClass('t-hidden');
});


});
