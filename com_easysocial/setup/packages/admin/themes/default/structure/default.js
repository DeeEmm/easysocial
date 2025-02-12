EasySocial.ready(function($) {

	var joomlaClass = "<?php echo ES::isJoomla4() ? 'is-joomla-4' : 'is-joomla-3'; ?>";

	$('body').addClass('com_easysocial ' + joomlaClass);

	// Fix the header for mobile view
	$('.container-nav').appendTo($('.header'));

	// If the page has tabs, we need to add into the app-head
	var hasTabs = $('#es .nav.nav-tabs').length > 0;

	if (hasTabs) {
		$('#es.es-backend ').addClass('has-app-head-tabbar');
	}

	$(window).scroll(function () {
		if ($(this).scrollTop() > 50) {
			$('.header').addClass('header-stick');
		} else if ($(this).scrollTop() < 50) {
			$('.header').removeClass('header-stick');
		}
	});

	$('.nav-sidebar-toggle').click(function(){
		$('html').toggleClass('show-easysocial-sidebar');
		$('.subhead-collapse').removeClass('in').css('height', 0);
	});

	$('.nav-subhead-toggle').click(function(){
		$('html').removeClass('show-easysocial-sidebar');
		$('.subhead-collapse').toggleClass('in').css('height', 'auto');
	});

	// Append help button
	var helpButton = $('#help-button-template');

	if (helpButton.length > 0) {
		helpButton.children().appendTo('#toolbar');
	}

	// Hide joomla's sidebar wrapper
	var sidebar = $('#es [data-sidebar]');
	var sidebarHtml = sidebar.html();

	var joomlaSidebar = $('#sidebarmenu');
	var joomlaSidebarNav = joomlaSidebar.find('> nav');

	var joomlaMenu = joomlaSidebarNav.find('ul.main-nav');

	joomlaMenu.hide();

	var joomlaSidebarTemplate = $('[data-j4-sidebar]').html();

	joomlaMenu.prepend(joomlaSidebarTemplate);

	// Append our own sidebar
	joomlaSidebarNav.append(sidebarHtml);

	var esMenu = joomlaSidebarNav.find('ul.app-sidebar-nav');

	$(document).on('click.back.joomla', '[data-back-joomla]', function() {
		joomlaMenu.show();
		esMenu.hide();
	});

	$(document).on('click.back.easysocial', '[data-back-easysocial]', function() {
		joomlaMenu.hide();
		esMenu.show();
	});

	<?php if (ES::isJoomla4()) { ?>
	$(document).on('click', '[data-toggle="dropdown"]', function() {
		$(this).removeAttr('data-toggle').attr('data-bs-toggle', 'dropdown').trigger('click');
	});
	<?php } ?>
});
