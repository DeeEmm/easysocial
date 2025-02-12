EasySocial.ready(function($) {
	var gifsTab = $('[data-giphy-gifs-tab]');
	var stickersTab = $('[data-giphy-stickers-tab]');
	var searchInput = $('[data-giphy-search]');
	let isLoadMore = false;

	var placeholderGIF = '<?php echo JText::_('COM_ES_GIPHY_GIFS_SEARCH'); ?>';
	var placeholderSticker = '<?php echo JText::_('COM_ES_GIPHY_STICKERS_SEARCH'); ?>';

	// The first view will always be GIFs
	var currentView = 'gifs';

	// By default, trending will show on both first
	var showTrending = [];
	showTrending['gifs'] = true;
	showTrending['stickers'] = true;

	var currentQuery = [];
	currentQuery['gifs'] = '';
	currentQuery['stickers'] = '';

	// By default, loadmore will show on both first
	var showLoadmore = [];
	showLoadmore['gifs'] = true;
	showLoadmore['stickers'] = true;

	var stickersLoaded = false;

	var show = function(el, type) {
		var browser = el.closest('[data-giphy-browser]');
		var list = browser.find('[data-' + type + '-list]');

		if (showTrending[type]) {
			browser.find('[data-giphy-trending]').removeClass('t-hidden');
		}

		el.addClass('active');
		list.removeClass('t-hidden');
	};

	var hide = function(el, type) {
		var browser = el.closest('[data-giphy-browser]');
		var tab = browser.find('[data-giphy-' + type + '-tab]');
		var list = browser.find('[data-' + type + '-list]');

		browser.find('[data-giphy-trending]').addClass('t-hidden');
		browser.find('[data-giphy-loading]').removeClass('t-hidden');
		tab.removeClass('active');
		list.addClass('t-hidden');
	};

	var loadGiphy = function(query, type, list, popboxWrapper, browser) {
		browser.find('[data-giphy-empty]').addClass('t-hidden');
		browser.find('[data-giphy-loadmore-wrapper]').addClass('t-hidden');

		EasySocial.ajax('site/controllers/giphy/search', {
			"query": query,
			"type": type,
			"offset": parseInt(list.attr('data-offset')),
			"from": "<?php echo $story ? 'story' : 'comment'; ?>"
		}).done(function(html, data, hasLoadMore, offset) {
			// Remove the loader
			browser.find('[data-giphy-loading]').removeClass('is-active');

			// Make sure that the current view is match to the type
			// Then only show
			if (currentView === type) {
				list.removeClass('t-hidden');
			}

			// Append the result to the list
			list.find('ul').append(html);

			// Update it to the new offset
			list.attr('data-offset', offset);

			if (!data) {
				if (!isLoadMore) {
					list.find('ul').html('');

					browser.find('[data-giphy-empty]').removeClass('t-hidden');
				}

				browser.find('[data-giphy-loadmore-wrapper]').addClass('t-hidden');
			}

			if (query === '') {
				browser.find('[data-giphy-trending]').removeClass('t-hidden');
			}

			browser.find('[data-giphy-loadmore-loading]').addClass('t-hidden');

			if (hasLoadMore && data) {
				browser.find('[data-giphy-loadmore-wrapper]').removeClass('t-hidden');
			}

			showLoadmore[type] = hasLoadMore;

			isLoadMore = false;

			popboxWrapper.trigger('giphyAfterSearch', [query]);
		});
	};

	$(document).off('click.giphy.gifs.tab').on('click.giphy.gifs.tab',  '[data-giphy-gifs-tab]', function() {
		var browser = $(this).closest('[data-giphy-browser]');
		var search = browser.find('[data-giphy-search]');

		hide($(this), 'stickers');

		currentView = 'gifs';

		// Show back the query that the user left before changing to another tab
		searchInput.val(currentQuery['gifs']);

		browser.find('[data-giphy-loadmore-wrapper]').toggleClass('t-hidden', !showLoadmore['gifs']);

		// Update to its own placeholder
		search.attr('placeholder', placeholderGIF);

		show($(this), 'gifs');
	});

	$(document).off('click.giphy.stickers.tab').on('click.giphy.stickers.tab', '[data-giphy-stickers-tab]', function() {
		var browser = $(this).closest('[data-giphy-browser]');
		var search = browser.find('[data-giphy-search]');

		currentView = 'stickers';

		browser.find('[data-giphy-loadmore-wrapper]').toggleClass('t-hidden', !showLoadmore['stickers']);

		// Initialize the stickers once if haven't load
		if (!stickersLoaded) {
			stickersLoaded = true;

			var popboxWrapper = $(this).parents('[data-popbox-content]');
			var list = browser.find('[data-stickers-list]');

			// Show the loader
			browser.find('[data-giphy-loading]').addClass('is-active');

			// Hide the trending
			browser.find('[data-giphy-trending]').addClass('t-hidden');

			loadGiphy('', 'stickers', list, popboxWrapper, browser);
		}

		hide($(this), 'gifs');

		// Update to its own placeholder
		search.attr('placeholder', placeholderSticker);

		// Show back the query that the user left before changing to another tab
		searchInput.val(currentQuery['stickers']);

		show($(this), 'stickers');
	});

	$(document).off('keyup.giphy.search').on('keyup.giphy.search', '[data-giphy-search]', $.debounce(function() {
		var browser = $(this).closest('[data-giphy-browser]');
		var popboxWrapper = $(this).parents('[data-popbox-content]');
		var loading = browser.find('[data-giphy-loading]');

		// By default the type is gifs
		var type = 'gifs';

		if (browser.find('[data-giphy-stickers-tab]').hasClass('active')) {
			type = 'stickers';
		}

		// Retrieve the search query
		var query = $(this).val();

		if (query === currentQuery[type] && !isLoadMore) {
			return;
		}

		var giphyList = browser.find('[data-' + type + '-list]');
		var trending = browser.find('[data-giphy-trending]');

		if (isLoadMore) {
			browser.find('[data-giphy-loadmore-loading]').removeClass('t-hidden');
		}

		if (!isLoadMore) {
			trending.addClass('t-hidden');
			giphyList.addClass('t-hidden');
			loading.addClass('is-active');
		}

		// If the new query is different from the previous
		if (query !== currentQuery[type]) {
			// Reset the list
			giphyList.find('ul').html('');

			// Reset the offset
			giphyList.attr('data-offset', 0);
		}

		// Store the current query so that we can show it back after switching back the tab
		currentQuery[type] = query;

		showTrending[currentView] = true;

		if (query !== '') {
			showTrending[currentView] = false;
		}

		loadGiphy(query, type, giphyList, popboxWrapper, browser);
	}, 300));

	$(document).on('click', '[data-giphy-loadmore]', function() {
		const el = $(this);
		const loadmoreLoader = $('[data-giphy-loadmore-loading]');
		const wrapper = el.closest('[data-giphy-loadmore-wrapper]');

		wrapper.addClass('t-hidden');
		loadmoreLoader.removeClass('t-hidden');

		isLoadMore = true;

		searchInput.trigger('keyup');
	});
});