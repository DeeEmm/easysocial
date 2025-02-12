<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSidebarMarketplaces extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function render()
	{
		$layout = $this->input->get('layout', '', 'cmd');

		// We do not want to render anything on the item layout
		if ($layout == 'item') {
			return;
		}

		$allowedLayouts = array('edit');

		if ($layout && in_array($layout, $allowedLayouts)) {
			$method = 'render' . ucfirst($layout);
			return call_user_func_array(array($this, $method), array());
		}

		// Default layout
		return $this->renderListing();
	}

	/**
	 * Render edit listing layout
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function renderEdit()
	{
		$helper = ES::viewHelper('Marketplaces', 'Edit');
		$listing = $helper->getActiveListing();
		$steps = $helper->getListingSteps();

		// Determines if there are any active step in the query
		$activeStep = $helper->getActiveStep();

		$path = $this->getTemplatePath('marketplace_edit');
		require($path);
	}

	public function renderListing()
	{
		$helper = ES::viewHelper('Marketplaces', 'List');

		$filter = $helper->getCurrentFilter();
		$user = $helper->getActiveUser();

		$uid = $helper->getUid();
		$type = $helper->getType();

		$cluster = $helper->getCluster();
		$titles = $helper->getPageTitle();

		$createUrl = $helper->getCreateUrl();

		$counters = $helper->getCounters();

		$browseView = $helper->isBrowseView();
		$isCluster = $helper->isCluster();
		$isUserProfileView = $helper->isUserProfileView();
		$showPending = $helper->getShowPending();

		// Custom filters
		$customFilters = $helper->getCustomFilters();
		$canCreateFilter = $helper->canCreateFilter();
		$createCustomFilterLink = $helper->getCreateCustomFilterLink();
		$activeCustomFilter = $helper->getActiveCustomFilter();

		$filtersLink = $helper->getFiltersLink();

		// Filter acl
		$filtersAcl = $helper->getFiltersAcl();

		// Get a list of marketplace categories on the site
		$activeCategory = $helper->getActiveCategory();

		$path = $this->getTemplatePath('marketplaces');

		require($path);
	}
}
