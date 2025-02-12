EasySocial.require()
.script('site/story/story')
.done(function($) {

	var isMobile = false;

	<?php if ($this->isMobile() || $this->isTablet()) { ?>
	isMobile = true;
	<?php } ?>

	// The initial story id rendered
	var storyId = "<?php echo $story->id; ?>";

	// Story selector
	$(document)
		.off('click.story.selection')
		.on('click.story.selection', '[data-story-selector]', function() {
			var element = $(this);
			var uid = element.data('uid');
			var type = element.data('type');
			var popbox = $('[data-story-selection-button]').popbox('widget');
			var currentForm = $('[data-story=' + storyId + ']');
			currentForm.addClass('is-loading');

			// If there is text already, let's keep and put it back into the new form
			var text = $('[data-story-textfield]').val();

			popbox.hide();

			EasySocial.ajax('site/views/story/getForm', {
				'type': type,
				'uid': uid
			}).done(function(html) {

				var tmp = $('<div>').html(html);

				// Get the latest story id
				var newStoryId = tmp.find('[data-story-form]').data('story');

				storyId = newStoryId;

				tmp.find('[data-story-form]')
					.removeClass('is-collapsed')
					.addClass('is-expanded')

				currentForm.replaceWith(tmp.html());

				if (text) {
					$('[data-story-textfield]').val(text);
				}
			});
		});

	EasySocial.module("story/selection", function($) {
		this.resolve(function(popbox) {
			var view = popbox.button.data('popbox-view');

			if (view == undefined) {
				view = '';
			}

			return {
				content: EasySocial.ajax("site/views/story/getSelection"),
				id: "es",
				cache: true
			};
		});
	});

	// define the global variable so that story controller and this js script can reference this varaible.
	window.currentStoryController = '';
	var mainStory = $("[data-story=<?php echo $story->id; ?>]");

	// Story controller
	var controller = mainStory.addController("EasySocial.Controller.Story", {

			"errors": {
				"empty": "<?php echo JText::_('COM_EASYSOCIAL_STORY_CONTENT_EMPTY', true);?>",
				"filter": "<?php echo JText::_('COM_EASYSOCIAL_STORY_NOT_ON_STREAM_FILTER', true);?>",
				"standard": "<?php echo JText::_('COM_EASYSOCIAL_STORY_SUBMIT_ERROR', true);?>",
				"scheduled": "<?php echo JText::_('COM_ES_STORY_SCHEDULED_DATE_EMPTY', true);?>",
				"invalidScheduled": "<?php echo JText::_('COM_ES_STORY_SCHEDULED_DATE_INVALID', true);?>"
			},

			"moodText": "<?php echo JText::_('COM_EASYSOCIAL_MOOD_VERB_FEELING', true);?>",
			"scheduledPost": "<?php echo JText::_('COM_ES_STORY_SCHEDULED_A_POST', true);?>",

			"flood": {
				"enabled": <?php echo !$this->my->isSiteAdmin() && $this->access->get('story.flood.user') ? 'true' : 'false' ; ?>,
				"interval": '<?php echo $this->access->get('story.flood.interval', '90'); ?>',
				"submit": <?php echo $this->my->canSubmitStory() ? 'true' : 'false'; ?>
			},

			<?php
			if ($story->plugins) {
				$length = count($story->plugins);
				$i = 0;
			?>
				plugin: {
					<?php foreach($story->plugins as $plugin) { ?>
					<?php echo $plugin->name; ?>: {
						id: '<?php echo $plugin->id; ?>',
						type: '<?php echo $plugin->type; ?>',
						name: '<?php echo $plugin->name; ?>'
					}<?php if (++$i < $length) { echo ','; }; ?>
					<?php } ?>
				},
			<?php } ?>
				enterToSubmit: false,
				sourceView: "<?php echo $this->input->get('view', '', 'cmd'); ?>",
				hashtagEditable: "<?php echo $story->hashtagEditable; ?>",
				singlePanel: "<?php echo $singlePanel; ?>",
				emoticons: '<?php echo $emoticons; ?>',
				mapIntegration: "<?php echo $this->config->get('location.provider'); ?>",
				mapElementId: "map-<?php echo $story->id; ?>"
			}
	);

	$(document)
		.off('click', '[data-story-panel-button-more] [data-favourite]')
		.on('click', '[data-story-panel-button-more] [data-favourite]', function(event) {

		event.stopPropagation();
		event.preventDefault();

		var panelButtons = $('[data-story-panel-buttons]');
		var element = $(this).parent('[data-story-panel-button-more]');
		var id = element.data('id');
		var selected = element.hasClass('is-selected');

		// If it was already selected, we need to remove the button now
		if (selected) {
			element.removeClass('is-selected');

			var button = panelButtons.find('[data-story-plugin-name=' + id + ']');
			button.remove();

			EasySocial.ajax('site/controllers/story/removeFavourite', {
				"element": id
			});

			// Reload slider in mobile
			if (isMobile) {
				swiperReload();
			}

			return;
		}

		element.addClass('is-selected');

		// Duplicate the DOM element
		var duplicate = element.clone();
		duplicate.find('[data-favourite]').remove();

		var newButton = $(duplicate.html());
		newButton.attr('data-story-panel-button', '')
			.attr('data-story-plugin-name', id);

		// Append the new button
		if (isMobile) {
			var mobilePanel = $('[data-story-panel-buttons-wrapper]');
			mobilePanel.append(newButton);

			// Reload the slider
			swiperReload();
		} else {
			panelButtons.append(newButton);
		}

		EasySocial.ajax('site/controllers/story/addFavourite', {
			"element": id
		});
	});

	// Initialize the buttons in popbox
	$(document)
		.off('click', '[data-story-panel-button-more]')
		.on('click', '[data-story-panel-button-more]', function(event) {

		event.preventDefault();
		event.stopPropagation();

		var element = $(this);
		var id = element.data('id');
		var button = element.children('.es-story-panel-button');
		var buttons = $('[data-story-panel-button-more] .es-story-panel-button');

		buttons.removeClass('active');
		button.addClass('active');

		// current active story form controller.
		buttonController = window.currentStoryController;

		// active the correct panel in correct story form
		buttonController.activatePanel(id);

		// close the popup.
		buttonController.plusButton().click();
	});

	// Story plugins
	$.module("<?php echo $story->moduleId; ?>")
		.done(function(story) {
			<?php foreach($story->plugins as $plugin) { ?>
				<?php echo $plugin->script; ?>
			<?php } ?>
		});

	if (isMobile) {
		var navigationBar = mainStory.find('[data-story-swiper-nav]');
		var navItem = mainStory.find('[data-story-panel-button]');
		var panelButtons = mainStory.find('[data-story-panel-buttons-mobile]');

		var activeIdx = 0;

		$.each(navItem, function(idx, item) {
			if ($(item).hasClass('active')) {
				activeIdx = idx;
			}
		})


		var swiperLib = EasySocial.require().library('swiper');

		swiperLib.done(function() {

			var swiper = new Swiper(panelButtons, {
				init: false,
				// width: 600,
				slidesPerView: 'auto',
				spaceBetween: 0,
				initialSlide: activeIdx, //slide number which you want to show-- 0 by default
				freeMode: true,
				freeModeSticky: false,
				freeModeMomentum: false,
				freeModeMomentumBounce: false,
				centeredSlides: false
			});

			swiper.on('touchMove', function() {
				navigationBar.removeClass('is-end-left is-end-right');

				if (swiper.isBeginning) {
					navigationBar.addClass('is-end-left');
				}

				if (swiper.isEnd) {
					navigationBar.addClass('is-end-right');
				}
			});

			swiper.on('tap', function() {
				var index = swiper.clickedIndex;
				var slide = swiper.clickedSlide;

				// Simulate centeredSlides #3492#note_114545
				swiper.slideTo(index - 1);
			});

			function swiperReload() {
				// Add some delay
				setTimeout(function() {
					swiper.update();

					// Do double reload to ensure the sly is loaded correctly with the correct DOM. #3045
					setTimeout(function() {
						swiper.update();
					}, 4);
				}, 4);
			}

			swiper.init();
			// swiper.snapGrid[swiper.snapGrid.length - 1] = swiper.slidesGrid[swiper.slidesGrid.length - 1];

			// // Reload once to re-calculate the width
			swiperReload();
		});
	}
});
