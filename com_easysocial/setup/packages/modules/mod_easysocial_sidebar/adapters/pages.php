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

class SocialSidebarPages extends SocialSidebarAbstract
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

		$page = ES::page($id);

		$path = $this->getTemplatePath('page_about');

		require($path);
	}

	public function renderItem()
	{
		$appId = $this->input->get('appId', 0, 'int');

		// Determine whether this is about page or not
		$isTimelinePage = $this->isTimelinePage(SOCIAL_TYPE_PAGES);

		if (!$isTimelinePage && !$appId) {
			return $this->renderInfo();
		}

		// As we know, profile views must have an id,
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return;
		}

		$page = ES::page($id);
		$helper = ES::viewHelper('Pages', 'Item');

		// check if this is an app view or not.
		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($app, $page, 'pages');
		}

		$path = $this->getTemplatePath('page_item');
		require($path);
	}

	/**
	 * Render edit page layout
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderEdit()
	{
		$helper = ES::viewHelper('Pages', 'Edit');
		$page = $helper->getActivePage();
		$steps = $helper->getPageSteps();
		
		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		$path = $this->getTemplatePath('page_edit');
		require($path);
	}

	public function renderListing()
	{
		$helper = ES::viewHelper('Pages', 'List');

		$filters = $helper->getFilterLinks();
		$filter = $helper->getCurrentFilter();
		$browseView = $helper->isBrowseView();
		$titles = $helper->getPageTitles();
		$counters = $helper->getCounters();
		$activeCategory = $helper->getActiveCategory();
		$user = $helper->getActiveUser();

		// Additional filters
		$showMyPages = $helper->showMyPages();
		$showInvites = $helper->showInvites();
		$showPendingPages = $helper->showPendingPages();

		$path = $this->getTemplatePath('pages');
		require($path);
	}

	/**
	 * Render single page category page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function renderCategory()
	{
		$helper = ES::viewHelper('Pages', 'Category');

		// Validate for the current page category id
		$category = $helper->getActivePageCategory();

		// Retrieve a list of events under this category
		$pages = $helper->getPages();

		// Get random followers from this category
		$randomMembers = $helper->getRandomCategoryFollowers();

		// Get total pages within a category
		$totalPages = $helper->getTotalPages();

		// Get total albums within a category
		$totalAlbums = $helper->getTotalAlbums();

		// Get random albums for pages in this category
		$randomAlbums = $helper->getRandomAlbums();

		// Retrieve stream item
		$stream = $helper->getStreamData();

		$path = $this->getTemplatePath('page_category');
		require($path);
	}
}
