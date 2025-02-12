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

class SocialSidebarGroups extends SocialSidebarAbstract
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

	/**
	 * Render single group item
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderItem()
	{
		$appId = $this->input->get('appId', 0, 'int');

		// Determine whether that is about page or not
		$isTimelinePage = $this->isTimelinePage(SOCIAL_TYPE_GROUPS);

		if (!$isTimelinePage && !$appId) {
			return $this->renderInfo();
		}

		// As we know, profile views must have an id,
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return;
		}

		$group = ES::group($id);
		$helper = ES::viewHelper('Groups', 'Item');

		// check if this is an app view or not.
		if ($appId) {
			$app = ES::table('App');
			$app->load($appId);

			return $this->app($app, $group, 'groups');
		}

		$path = $this->getTemplatePath('group_item');
		require($path);
	}

	/**
	 * Render edit group layout
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderEdit()
	{
		$helper = ES::viewHelper('Groups', 'Edit');
		$group = $helper->getActiveGroup();
		$steps = $helper->getGroupSteps();

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		$path = $this->getTemplatePath('group_edit');
		require($path);
	}

	/**
	 * Render group info
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderInfo()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return;
		}

		$group = ES::group($id);

		$path = $this->getTemplatePath('group_about');
		require($path);
	}

	/**
	 * Render group listings
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderListing()
	{
		// Standard listing
		$helper = ES::viewHelper('Groups', 'List');
		$user = $helper->getActiveUser();

		// Determines if viewer is viewing all groups from the site
		$browseView = $helper->isBrowseView();

		// Only allow filters that we know.
		$filter = $helper->getCurrentFilter();

		// Determine if this is filtering groups by category
		$activeCategory = $helper->getActiveCategory();

		// Prepare the filter links
		$filters = $helper->getFilters();

		// Prepare the counters
		$counter = $helper->getCounters();

		// Determines if the "showMyGroups" filter link should appear
		$filtersAcl = $helper->getFiltersAcl();

		$path = $this->getTemplatePath('groups');
		require($path);
	}

	/**
	 * Render single group category page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function renderCategory()
	{
		$helper = ES::viewHelper('Groups', 'Category');

		// Validate for the current group category id
		$category = $helper->getActiveGroupCategory();

		// Retrieve a list of groups under this category
		$groups = $helper->getGroups();

		// Get random members from this category
		$randomMembers = $helper->getRandomCategoryMembers();

		// Get total groups within a category
		$totalGroups = $helper->getTotalGroups();

		// Get total albums within a category
		$totalAlbums = $helper->getTotalAlbums();

		// Get random albums for groups in this category
		$randomAlbums = $helper->getRandomAlbums();

		// Get the stream for this group
		$stream = $helper->getStreamData();

		$path = $this->getTemplatePath('group_category');
		require($path);
	}
}
