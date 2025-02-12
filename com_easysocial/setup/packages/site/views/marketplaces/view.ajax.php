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

class EasySocialViewMarketplaces extends EasySocialSiteView
{
	/**
	 * Post processing after filtering listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function filter($filter, $listings, $pagination, $activeCategory, $featuredListings, $browseView, $activeUserId)
	{
		$ordering = $this->input->get('ordering', 'start', 'word');
		$user = ES::user($activeUserId);

		// Determines the current filter being viewed
		$helper = ES::viewHelper('Marketplaces', 'List');
		$sortItems = $helper->getSortables($activeCategory, true);
		$sort = $this->input->get('sort', 'latest', 'word');
		$from = $this->input->get('from', '', 'string');

		$title = 'COM_ES_PAGE_TITLE_MARKETPLACE';
		$clusterId = $this->input->get('clusterId', '', 'string');
		$clusterType = false;

		// Set the route options so that filter can add extra parameters
		$routeOptions = ['option' => SOCIAL_COMPONENT_NAME, 'view' => 'marketplaces'];

		// Default properties
		$showSorting = true;
		$cluster = false;
		$showDistanceSorting = false;
		$showDistance = false;
		$distance = $this->config->get('marketplaces.nearby.radius');

		if ($clusterId) {
			$cluster = ES::cluster($clusterId);
			$clusterType = $cluster->getType();
		}

		if ($cluster) {
			$routeOptions['uid'] = $cluster->getAlias();
			$routeOptions['type'] = $cluster->cluster_type;
		}

		if ($filter != 'category') {
			$routeOptions['filter'] = $filter;
		}

		// We want to set a different title for non "all" or "category" filter
		if ($filter != 'all' && $filter != 'category') {
			$title = 'COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_' . strtoupper($filter);
		}

		// Get the active category alias
		if ($activeCategory && $activeCategory->id) {
			$routeOptions['categoryid'] = $activeCategory->getAlias();
		}

		$sortingUrls = [];

		// If the user is viewing others' listing, we should respect that
		if (!$browseView & !$cluster) {
			$routeOptions['userid'] = $user->getAlias();
		}

		$routeCurrent = $routeOptions;

		if ($ordering) {
			$routeCurrent['ordering'] = $ordering;
		}

		$emptyText = 'COM_ES_MARKETPLACES_EMPTY_' . strtoupper($filter);

		// If this is viewing profile's event, we display a different empty text
		if (!$browseView) {
			$emptyText = 'COM_ES_MARKETPLACES_EMPTY_' . strtoupper($filter);

			if (!$user->isViewer()) {
				$emptyText = 'COM_ES_MARKETPLACES_USER_EMPTY_' . strtoupper($filter);
			}
		}

		// Filter by near by events
		if ($filter === 'nearby') {
			$showSorting = false;
			$showDistance = true;
			$showDistanceSorting = true;
			$defaultDistanceRadius = $distance;
			$distance = $this->input->get('distance', $defaultDistanceRadius, 'string');

			if (!empty($distance) && $distance != $defaultDistanceRadius) {
				$routeOptions['distance'] = $distance;
			}

			$title = JText::sprintf('COM_ES_MARKETPLACES_IN_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));
		}

		$theme = ES::themes();
		$theme->set('showSorting', $showSorting);
		$theme->set('sortingUrls', $sortingUrls);
		$theme->set('ordering', $ordering);
		$theme->set('routeOptions', $routeOptions);
		$theme->set('browseView', $browseView);

		// Since ajax requests to filter only occurds when sidebar is enabled, we should enable by default
		$theme->set('showSidebar', true);

		// Content attributes
		$theme->set('title', $title);
		$theme->set('filter', $filter);
		$theme->set('featuredListings', $featuredListings);
		$theme->set('listings', $listings);
		$theme->set('pagination', $pagination);
		$theme->set('activeCategory', $activeCategory);
		$theme->set('emptyText', $emptyText);
		$theme->set('helper', $helper);
		$theme->set('from', $from);
		$theme->set('sort', $ordering);
		$theme->set('sortItems', $sortItems);

		$theme->set('showDistance', $showDistance);
		$theme->set('showDistanceSorting', $showDistanceSorting);

		// Distance options
		$theme->set('distance', $distance);
		$theme->set('distanceUnit', $this->config->get('general.location.proximity.unit'));

		// determine whether this is coming from ajax call
		$theme->set('fromAjax', true);

		$namespace = 'wrapper';

		$sort = $this->input->get('sort', false, 'bool');

		if ($sort) {
			$namespace = 'items';
		}

		$output = $theme->output('site/marketplaces/default/' . $namespace);

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to feature events
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmFeature()
	{
		ES::requireLogin();

		// Get the listing
		$id = $this->input->getInt('id', 0);
		$type = $this->input->get('type', '');

		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		$checkAccess = $type == 'feature' ? 'canFeature' : 'canUnfeature';

		// Ensure that the user can really unpublish the event
		if (!$listing->$checkAccess($this->my->id)) {
			return $this->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		$returnUrl = $this->input->get('return', '', 'default');
		$action = $type == 'feature' ? 'setFeatured' : 'removeFeatured';

		$theme = ES::themes();
		$theme->set('listing', $listing);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('type', $type);
		$theme->set('action', $action);

		$output = $theme->output('site/marketplaces/dialogs/feature');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation to publish a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function confirmPublish()
	{
		ES::requireLogin();

		// Get the listing
		$id = $this->input->getInt('id', 0);
		$type = $this->input->get('type', '');
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		$theme = ES::themes();
		$theme->set('listing', $listing);
		$theme->set('type', $type);
		$output = $theme->output('site/marketplaces/dialogs/publish');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the delete listing dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function confirmDelete()
	{
		ES::requireLogin();

		$id = $this->input->getInt('id', 0);
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		// Ensure that the user can really unpublish the event
		if (!$listing->canDelete($this->my->id)) {
			return $this->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		$theme = ES::themes();
		$theme->set('listing', $listing);

		$contents = $theme->output('site/marketplaces/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the mark sold dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function confirmChangeAvailability()
	{
		ES::requireLogin();

		$id = $this->input->getInt('id', 0);
		$type = $this->input->get('type', 'sold');
		$task = $type == 'sold' ? 'markAsSold' : 'markAvailable';
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		$isAllowed = 'can' . ucfirst($task);

		// Ensure that the user can really unpublish the event
		if (!$listing->$isAllowed($this->my->id)) {
			return $this->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		$theme = ES::themes();
		$theme->set('listing', $listing);
		$theme->set('task', $task);
		$theme->set('type', $type);

		$contents = $theme->output('site/marketplaces/dialogs/availability');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the mark available dialog
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function confirmAvailable()
	{
		ES::requireLogin();

		$id = $this->input->getInt('id', 0);
		$listing = ES::marketplace($id);

		if (!$listing || !$listing->id) {
			return $this->exception('COM_ES_MARKETPLACES_INVALID_LISTING_ID');
		}

		// Ensure that the user can really unpublish the event
		if (!$listing->canMarkAsSold($this->my->id)) {
			return $this->exception('COM_ES_MARKETPLACES_NO_ACCESS_TO_LISTING');
		}

		$theme = ES::themes();
		$theme->set('listing', $listing);

		$contents = $theme->output('site/marketplaces/dialogs/sold');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Output for getting subcategories
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getSubcategories($subcategories, $groupId, $pageId, $backId)
	{
		// Retrieve current logged in user profile type id
		$profileId = $this->my->getProfile()->id;

		$theme = ES::themes();
		$theme->set('backId', $backId);
		$theme->set('profileId', $profileId);

		$html = '';

		$categoryRouteBaseOptions = ['controller' => 'marketplaces' , 'task' => 'selectCategory'];

		if ($groupId) {
			$categoryRouteBaseOptions['group_id'] = $groupId;
		}

		if ($pageId) {
			$categoryRouteBaseOptions['page_id'] = $pageId;
		}

		foreach ($subcategories as $category) {
			$table = ES::table('MarketplaceCategory');
			$table->load($category->id);

			$theme->set('category', $table);
			$theme->set('categoryRouteBaseOptions', $categoryRouteBaseOptions);
			$html .= $theme->output('site/marketplaces/create/category.item');
		}

		return $this->ajax->resolve($html);
	}
}
