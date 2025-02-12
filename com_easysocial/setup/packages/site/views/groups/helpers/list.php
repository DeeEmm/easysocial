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

class EasySocialViewGroupsListHelper extends EasySocial
{
	public function getActiveUserId()
	{
		static $userId = false;

		if ($userId === false) {
			// Determines if we should render groups created by a specific user
			$userId = $this->input->get('userid', 0, 'int');
			$userId = !$userId ? null : $userId;
		}

		return $userId;
	}

	public function getActiveUser()
	{
		static $user = null;

		if (is_null($user)) {
			$userId = $this->getActiveUserId();
			$user = ES::user($userId);
		}

		return $user;
	}

	/**
	 * Determine if view can access user's groups page or not.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canUserView($user)
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->my->id && $this->my->id == $user->id) {
			return true;
		}

		// since this is checking against user's groups and
		// there is no privacy for user groups. we will
		// check against user's profile viewing privacy.
		// #3111
		if ($this->my->canView($user)) {
			return true;
		}

		return false;
	}

	/**
	 * If the current page being viewed is for a specific group category, it should return the
	 * category id
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCategoryId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('categoryid', 0, 'int');
		}

		return $id;
	}

	/**
	 * Retrieve the current active group category that is being viewed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveCategory()
	{
		static $category = null;

		if (is_null($category)) {
			$id = $this->getActiveCategoryId();

			if (!$id) {
				$category = false;
				return $category;
			}

			$category = ES::table('GroupCategory');
			$state = $category->load($id);

			if (!$state) {
				$category = false;
				return $category;
			}
		}

		return $category;
	}

	/**
	 * Retrieves the current filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCurrentFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', $this->getDefaultFilter(), 'cmd');
		}

		return $filter;
	}

	/**
	 * Generates the counters for the listing view
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCounters()
	{
		static $counter = null;

		if (is_null($counter)) {
			$model = ES::model('Groups');

			$isBrowseView = $this->isBrowseView();

			$counter = new stdClass();

			// Get total number of featured groups on the site
			$counter->totalFeaturedGroups = $model->getTotalGroups(array('featured' => true));

			// Get total number of groups on the site
			$counter->totalGroups = $model->getTotalGroups(array('types' => $this->my->isSiteAdmin() ? 'all' : 'user'));

			if ($isBrowseView && $this->my->id != 0) {
				// Get the total number of groups the user created
				$counter->totalCreatedGroups = $this->my->getTotalGroupsCreated();

				// Get the total number of groups the user participated
				$counter->totalParticipatedGroups = $this->my->getTotalGroups();

				// Get the total number of groups the user created but required review
				$counter->totalPendingGroups = $this->my->getTotalPendingReview(SOCIAL_TYPE_GROUP);

				// Get total number of invitations
				$counter->totalInvites = $model->getTotalInvites($this->my->id);

				// Get total number of unpublished group
				$counter->totalUnpublished = $this->my->getTotalGroupsUnpublished();
			}

			if (!$isBrowseView) {
				$user = $this->getActiveUser();
				$counter->totalCreatedGroups = $user->getTotalGroupsCreated();
				$counter->totalParticipatedGroups = $model->getTotalParticipatedGroups($user->id);
				// Get total number of invitations
				$counter->totalInvites = $model->getTotalInvites($user->id);
			}
		}

		return $counter;
	}

	/**
	 * Retrieves the default filter that should be used on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getDefaultFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			// If we are viewing profile's page listing
			// Default the $filter to 'created'
			if ($this->getActiveUser()->canCreateGroups()) {
				$filter = $this->isBrowseView() ? 'all' : 'created';
			} else {
				$filter = $this->isBrowseView() ? 'all' : 'participated';
			}
		}

		return $filter;
	}

	/**
	 * Generates the empty text that should be used based on the filters crtieria
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getEmptyText()
	{
		static $text = null;

		if (is_null($text)) {
			$browseView = $this->isBrowseView();
			$filter = $this->getCurrentFilter();

			$text = 'COM_ES_GROUPS_EMPTY_' . strtoupper($filter);

			// If this is viewing profile's event, we display a different empty text
			if (!$browseView) {
				$text = 'COM_ES_GROUPS_EMPTY_' . strtoupper($filter);
				$user = $this->getActiveUser();

				if (!$user->isViewer()) {
					$text = 'COM_ES_GROUPS_USER_EMPTY_' . strtoupper($filter);
				}
			}
		}

		return $text;
	}

	/**
	 * Determines which filters are viewable by the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFiltersAcl()
	{
		static $acl = null;

		if (is_null($acl)) {
			$acl = new stdClass();
			$acl->mine = $this->showMyGroupsFilter();
			$acl->pending = $this->showPendingFilter();
			$acl->invites = $this->showInvitesFilter();
			$acl->nearby = $this->isBrowseView();
			$acl->unpublished = $this->isBrowseView();
		}

		return $acl;
	}

	/**
	 * Retrieve the filters hyperlinks
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			// Generate links for filters
			$filters = new stdClass();
			$options = array();

			// If the user is viewing others' listing, we should respect that
			if ($this->getActiveUserId()) {
				$user = $this->getActiveUser();
				$options['userid'] = $user->getAlias();
			}

			$filters->all = ESR::groups($options);
			$filters->featured = ESR::groups(array_merge(array('filter' => 'featured'), $options));
			$filters->pending = ESR::groups(array_merge(array('filter' => 'pending'), $options));
			$filters->invited = ESR::groups(array_merge(array('filter' => 'invited'), $options));
			$filters->created = ESR::groups(array_merge(array('filter' => 'created'), $options));
			$filters->participated = ESR::groups(array_merge(array('filter' => 'participated'), $options));
			$filters->mine = ESR::groups(array('filter' => 'mine'));
			$filters->nearby = ESR::groups(array('filter' => 'nearby'));
			$filters->unpublished = ESR::groups(array('filter' => 'unpublished'));
		}

		return $filters;
	}

	/**
	 * Generates the heading text that should be used
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getHeadingText()
	{
		$text = null;

		if (is_null($text)) {
			$text = 'COM_EASYSOCIAL_GROUPS';

			$filter = $this->getCurrentFilter();

			if ($filter == 'nearby') {
				$heading = JText::sprintf('COM_ES_GROUPS_IN_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));
			}
		}

		return $text;
	}

	/**
	 * Determines the current sorting type from the listing page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getOrdering()
	{
		static $ordering = null;

		if (is_null($ordering)) {
			$ordering = $this->input->get('ordering', '', 'word');
		}

		return $ordering;
	}

	/**
	 * Determines the current category filter description
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function getPageCategoryDesc($reload = false)
	{
		static $description = null;

		if (is_null($description) || $reload) {

			$description = '';
			$category = $this->getActiveCategory();

			if ($category) {
				$description = $category->getDescription();
			}
		}

		return $description;
	}

	/**
	 * Determines the current page title
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageTitle($reload = false)
	{
		static $title = null;

		if (is_null($title) || $reload) {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS';

			$ordering = $this->getOrdering();
			$category = $this->getActiveCategory();

			if ($category) {
				$title = $category->getTitle();
			}

			$filter = $this->getCurrentFilter();

			if ($filter == 'invited') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_INVITED';
			}

			if ($filter == 'mine') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_MY_GROUPS';
			}

			if ($filter == 'pending') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_PENDING';
			}

			if ($filter == 'featured') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_FEATURED';
			}

			if ($filter == 'created') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_MY_CREATED_GROUPS';
			}

			if ($filter == 'nearby') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_NEARBY';
			}

			if ($filter == 'participated') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_GROUPS_FILTER_PARTICIPATED';
			}

			// Not handle for the ajax call sorting part
			if ($ordering && !$reload) {

				$ordering = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($ordering));
				$title = JText::_($title) . ' - ' . $ordering;
			}
		}

		return $title;
	}

	/**
	 * Generate sortable items on the listing view
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getSortables()
	{
		static $items = null;

		if (is_null($items)) {
			$filter = $this->getCurrentFilter();
			$activeCategory = $this->getActiveCategory();

			$items = new stdClass();
			$items->latest = new stdClass();
			$items->name = new stdClass();
			$items->popular = new stdClass();

            $attributes = array('data-sorting', 'data-filter="' . $filter . '"');
			$urlOptions = array('filter' => $filter);

			if ($activeCategory) {
				$attributes[] = 'data-id="' . $activeCategory->id . '"';
				$urlOptions['categoryid'] = $activeCategory->getAlias();
			}

			// Render the sorting title attribute
			$latestSort = $this->renderTitleAttribute($filter, $activeCategory, 'latest');
			$alphabeticalSort = $this->renderTitleAttribute($filter, $activeCategory, 'alphabetical');
			$popularSort = $this->renderTitleAttribute($filter, $activeCategory, 'popular');

			$items->latest->attributes = array_merge($attributes, array('data-type="latest"', $latestSort));
			$items->latest->url = ESR::groups(array_merge($urlOptions, array('ordering' => 'latest')));

			$items->name->attributes = array_merge($attributes, array('data-type="name"', $alphabeticalSort));
			$items->name->url = ESR::groups(array_merge($urlOptions, array('ordering' => 'name')));

			$items->popular->attributes = array_merge($attributes, array('data-type="popular"', $popularSort));
			$items->popular->url = ESR::groups(array_merge($urlOptions, array('ordering' => 'popular')));
		}

		return $items;
	}

	/**
	 * Include the title data attribute in cluster sorting option
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function renderTitleAttribute($filter = '', $activeCategory = '', $sort = '')
	{
		// display the proper filter name for the page title.
		$displaySortingName = JText::_($this->getPageTitle(true));

		// Add into the data attribute for this title
		$title = 'title="' . $displaySortingName . '"';

		// sorting page title
		if ($filter || $activeCategory) {
			$title = 'title="' . $displaySortingName . ' - ' . JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sort)) . '"';
		}

		return $title;
	}

	/**
	 * Determines if the user is currently browsing thorugh all groups on the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isBrowseView()
	{
		static $browse = null;

		if (is_null($browse)) {
			$activeUserId = $this->getActiveUserId();
			$user = $this->getActiveUser();
			$browse = true;

			if ($activeUserId && $user->id) {
				$browse = false;
			}
		}

		return $browse;
	}

	/**
	 * Determines if the my groups filter should be visible
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function showMyGroupsFilter()
	{
		static $show = null;

		if (is_null($show)) {
			$show = true;

			if (!$this->my->id || !$this->isBrowseView()) {
				$show = false;
			}
		}

		return $show;
	}

	/**
	 * Determines if the pending filter should be visible
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function showPendingFilter()
	{
		static $show = null;

		if (is_null($show)) {
			$user = $this->getActiveUser();

			if (!$this->isBrowseView() && !$user->isViewer()) {
				$show = false;
				return $show;
			}

			$show = false;

			if ($this->my->id) {
				$show = $this->my->getTotalPendingReview(SOCIAL_TYPE_GROUP) > 0;
			}
		}

		return $show;
	}

	/**
	 * Determines if the invite filter should be visible
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function showInvitesFilter()
	{
		static $show = null;

		if (is_null($show)) {
			$user = $this->getActiveUser();

			if (!$this->isBrowseView() && !$user->isViewer()) {
				$show = false;
				return $show;
			}

			$show = false;

			if ($this->my->id) {
				$model = ES::model('Groups');
				$show = $model->getTotalInvites($this->my->id) > 0;
			}
		}

		return $show;
	}
}
