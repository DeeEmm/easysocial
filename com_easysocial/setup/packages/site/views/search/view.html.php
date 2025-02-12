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

class EasySocialViewSearch extends EasySocialSiteView
{
	/**
	 * Renders the standard search layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();

		// Set Meta data
		ES::setMeta();

		// Get the current logged in user.
		$query = $this->input->get('q', '', 'default');

		// Get the selected filters
		$selectedFilters = $this->input->get('filtertypes', array(), 'default');

		// Load up the model
		$indexerModel = ES::model('Indexer');

		// Retrieve a list of supported types
		$allowedTypes = $indexerModel->getSupportedType();

		// Options
		$limit = ES::getLimit('search_limit');

		// Try to search for the result now
		$lib = ES::search();
		$data = $lib->search($query, 0, $limit, $selectedFilters);

		// Get filters
		$filters = $lib->getFilters();

		$length = ESJString::strlen($query);

		// Set page attributes
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_SEARCH');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_SEARCH');

		$this->set('length', $length);
		$this->set('selectedFilters', $selectedFilters);
		$this->set('result', $data->result);
		$this->set('query', $query);
		$this->set('total', $data->total);
		$this->set('next_limit', $data->next_limit);
		$this->set('filters', $filters);

		echo parent::display('site/search/default/default');
	}

	/**
	 * Renders the advanced search layout
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function advanced($tpl = null)
	{
		ES::checkCompleteProfile();
		ES::setMeta();

		$helper = $this->getHelper('Advanced');
		$type = $helper->getActiveType();

		if (!$helper->isSupportedType()) {
			return $this->redirectToView('dashboard');
		}

		// clear the search configs
		if ($type == SOCIAL_FIELDS_GROUP_USER) {
			$session = JFactory::getSession();
			$session->set('advancedsearch.configs', null, SOCIAL_SESSION_NAMESPACE);
		}

		$uid = $this->input->get('uid', 0, 'int');

		// Set page attributes
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_ADVANCED_SEARCH');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_ADVANCED_SEARCH');

		// Get values from posted data
		$match = $this->input->get('matchType', 'all', 'default');
		$avatarOnly = $this->input->get('avatarOnly', 0, 'int');
		$onlineOnly = $this->input->get('onlineOnly', 0, 'int');
		$profile = $this->input->get('profile', 0, 'int');
		$clusterCategory = $this->input->get('clusterCategory', 0, 'int');
		$sort = $this->input->get('sort', '', 'default');

		// Get values from posted data
		$searchConfig = [];
		$searchConfig['criterias'] = $helper->getActiveCriterias();
		$searchConfig['datakeys'] = $this->input->get('datakeys', '', 'default');
		$searchConfig['operators'] = $this->input->get('operators', '', 'default');
		$searchConfig['conditions'] = $this->input->get('conditions', '', 'default');
		$searchConfig['match'] = $match;
		$searchConfig['avatarOnly'] = $avatarOnly;
		$searchConfig['onlineOnly'] = $onlineOnly;

		// these two are used in custom field search module.
		$searchConfig['profile'] = $profile;
		$searchConfig['clusterCategoryIds'] = $clusterCategory;

		// normalize user search data.
		$searchConfig = $helper->normalizeUserData($searchConfig);

		$routerSegment = [];
		$routerSegment['layout'] = 'advanced';

		$activeFilter = $helper->getActiveFilter();

		// we need to load the data from db and do the search based on the saved filter.
		if ($activeFilter) {
			$routerSegment['fid'] = $activeFilter->getAlias();

			// Get the search configuration
			$searchConfig = $activeFilter->getSearchConfig();

			$match = $searchConfig['match'];
			$avatarOnly = $searchConfig['avatarOnly'];

			// Only fallback to the sorting from the config if there is no sorting request.
			if (!$sort) {
				$sort = $searchConfig['sort'];
			}
		}

		// Get default sorting
		if (!$sort) {
			$sort = $this->config->get('users.advancedsearch.sorting');
		}

		// Finalize the sorting
		$searchConfig['sort'] = $sort;

		// Set the type for router
		$routerSegment['type'] = $type;

		$filters = $helper->getFilters();

		// Default values
		$results = null;
		$total = 0;
		$nextlimit = null;
		$criteriaHTML = '';

		$displayOptions = array();

		$lib = $helper->getLibrary();

		// If there are criterias, we know the user is making a post request to search
		if ($searchConfig['criterias']) {

			if ($type == SOCIAL_FIELDS_GROUP_USER) {
				// check if we need to ignore admin users or not.
				$includeAdmin = $this->config->get('users.listings.admin') ? true : false;
				if (!$includeAdmin) {
					$userModel = ES::model('Users');
					$admins = $userModel->getSiteAdmins(true);

					if ($admins) {
						foreach ($admins as $adminId) {
							$searchConfig['ignoreUserIds'][] = $adminId;
						}
					}
				}
			}

			// before search, lets store the search params into session
			// so that other module such as advanced search profiles map module can
			// reuse.
			if ($type == SOCIAL_FIELDS_GROUP_USER) {
				$session = JFactory::getSession();
				$session->set('advancedsearch.configs', $searchConfig, SOCIAL_SESSION_NAMESPACE);
			}

			$results = $lib->search($searchConfig);

			$displayOptions = $lib->getDisplayOptions();
			$total = $lib->getTotal();
			$nextlimit = $lib->getNextLimit();
		}

		// Get search criteria output
		$criteriaHTML = $lib->getCriteriaHTML(array(), $searchConfig);

		if (!$criteriaHTML) {
			$criteriaHTML = $lib->getCriteriaHTML(array());
		}

		// Get the criteria template
		$criteriaTemplate = $lib->getCriteriaHTML(array('isTemplate' => true));

		$adapters = $helper->getAdapters();
		$newSearchLink = $helper->getCreateLink();

		$access = ES::access();
		$hasAccessCreateFilter = $access->allowed('search.create.filter');

		$this->set('newSearchLink', $newSearchLink);
		$this->set('adapters', $adapters);
		$this->set('lib', $lib);
		$this->set('type', $type);
		$this->set('routerSegment', $routerSegment);
		$this->set('criteriaHTML', $criteriaHTML);
		$this->set('criteriaTemplate', $criteriaTemplate);
		$this->set('match', $match);
		$this->set('avatarOnly', $avatarOnly);
		$this->set('onlineOnly', $onlineOnly);
		$this->set('sort', $sort);
		$this->set('results', $results);
		$this->set('total', $total);
		$this->set('nextlimit', $nextlimit);
		$this->set('filters', $filters);
		$this->set('activeFilter', $activeFilter);
		$this->set('displayOptions', $displayOptions);
		$this->set('hasAccessCreateFilter', $hasAccessCreateFilter);

		return parent::display('site/search/advanced/default');
	}

	/**
	 * Post processing after a filter is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFilter(SocialTableSearchFilter $table)
	{
		$this->info->set($this->getMessage());

		$redirect = ESR::search(array('layout' => 'advanced', 'type' => $table->element), false);

		return $this->redirect($redirect);
	}

	/**
	 * Post processing after adding a new filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addFilter($filter)
	{
		$this->info->set($this->getMessage());

		$redirect = $filter->getPermalink(true, false);

		return $this->redirect($redirect);
	}


	/**
	 * Post processing after searching
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function query($redirect)
	{
		return $this->redirect($redirect);
	}
}
