<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSidebarEvents extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$layout = $this->input->get('layout', '', 'cmd');
		$allowedLayouts = array('edit', 'item', 'category');

		if ($layout && in_array($layout, $allowedLayouts)) {
			$method = 'render' . ucfirst($layout);
			return call_user_func_array(array($this, $method), array());
		}

		// Default layout
		return $this->renderListing();
	}

	public function renderInfo()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return;
		}

		$event = ES::event($id);

		$path = $this->getTemplatePath('event_about');

		require($path);
	}

	public function renderItem()
	{
		$appId = $this->input->get('appId', 0, 'int');

		// Determine whether this is about page or not
		$isTimelinePage = $this->isTimelinePage(SOCIAL_TYPE_EVENTS);

		if (!$isTimelinePage && !$appId) {
			return $this->renderInfo();
		}

		// As we know, profile views must have an id,
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return;
		}

		$event = ES::event($id);
		$helper = ES::viewHelper('Events', 'Item');

		// check if this is an app view or not.
		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($app, $event, 'events');
		}

		$path = $this->getTemplatePath('event_item');
		require($path);
	}

	/**
	 * Render edit event layout
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderEdit()
	{
		$helper = ES::viewHelper('Events', 'Edit');
		$event = $helper->getActiveEvent();
		$steps = $helper->getEventSteps();

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		$path = $this->getTemplatePath('event_edit');
		require($path);
	}

	public function renderListing()
	{
		// Get helper proxy for events view
		$helper = ES::viewHelper('events', 'list');
		$user = $helper->getActiveUser();

		// Retrieve known filter from events view
		$cluster = $helper->getCluster();
		$browseView = $helper->getBrowseView();
		$filter = $helper->getCurrentFilter();
		$createUrl = $helper->getCreateUrl();
		$filtersLink = $helper->getFiltersLink();
		$counters = $helper->getCounters();
		$showMyEvents = $helper->getShowMyEvents();
		$showPendingEvents = $helper->getShowPendingEvents();
		$showTotalInvites = $helper->getShowTotalInvites();
		$dateLinks = $helper->getDateLinks();
		$activeCategory = $helper->getActiveCategory();
		$activeDateFilter = $helper->getActiveDateFilter();

		$path = $this->getTemplatePath('events');

		require($path);
	}

	/**
	 * Render single event category page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function renderCategory()
	{
		$helper = ES::viewHelper('Events', 'Category');

		// Validate for the current group category id
		$category = $helper->getActiveEventCategory();

		// Retrieve a list of events under this category
		$events = $helper->getEvents();

		// Retrieve a list of feature event under this category
		$featuredEvents = $helper->getFeatureEvents();

		// Retrieve a list of random event category members
		$randomGuests = $helper->getRandomCategoryGuests();

		// Retrieve a list of random event albums
		$randomAlbums = $helper->getRandomCategoryAlbums();

		// Retrieve total of events
		$totalEvents = $helper->getTotalEvents();

		// Retrieve total of album under this category
		$totalAlbums = $helper->getTotalAlbums();

		// Retrieve stream item
		$stream = $helper->getStreamData();

		$path = $this->getTemplatePath('event_category');
		require($path);
	}
}
