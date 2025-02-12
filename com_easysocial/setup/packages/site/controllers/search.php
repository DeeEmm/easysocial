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

class EasySocialControllerSearch extends EasySocialController
{
	/**
	 * Routes the search query
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function query()
	{
		ES::checkToken();

		// Get the query
		$query = $this->input->get('q', '', 'default');
		$filters = $this->input->get('filtertypes', [], 'array');

		// Retrieve the original POST data
		$data = $_POST;

		// Reset this filtertype to empty array if the user already unticked all the filter checkboxes
		if (!isset($data['filtertypes'])) {
			$filters = [];
		}

		$options = array('q' => urlencode($query));

		if ($filters) {
			for($i = 0; $i < count($filters); $i++) {
				$options['filtertypes[' . $i . ']'] = $filters[$i];
			}
		}

		// Assign badge for the person that initiated the search
		ES::badges()->log('com_easysocial', 'search.create', $this->my->id, 'COM_EASYSOCIAL_SEARCH_BADGE_SEARCHED_ITEM');

		$redirect = ESR::search($options, false);

		return $this->view->call(__FUNCTION__, $redirect);
	}

	/**
	 * Allows caller to retrieve additional search results
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getItems()
	{
		ES::checkToken();

		$query = $this->input->get('q', '', 'default');
		$mini = $this->input->get('mini', false, 'bool');
		$showSuggest = $this->input->get('showSuggest', false, 'bool');

		$limitstart = $this->input->get('next_limit', '', 'default');

		// Determines if we should search by specific filters
		$filters = $this->input->get('filtertypes', array(), 'array');

		$highlight = $mini ? false : true;
		$limit = ES::getLimit('search_limit');

		// Assign logging for users searching
		$loadmore = $this->input->get('loadmore', false, 'bool');

		if (!$loadmore) {
			ES::badges()->log('com_easysocial', 'search.create', $this->my->id, JText::_('COM_EASYSOCIAL_SEARCH_BADGE_SEARCHED_ITEM'));
		}

		$lib = ES::search();
		$data = $lib->search($query, $limitstart, $limit, $filters, $highlight, $showSuggest);

		// var_dump($isSuggestTerm, $data->isSuggestion);

		if ($data->suggestion) {
			return $this->view->call('showSuggestions', $query, $data);
		}

		return $this->view->call(__FUNCTION__, $query, $data, $filters, $mini, $loadmore);
	}

	/**
	 * Allows caller to delete a filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->view->exception('Invalid filter id');
		}

		$table = ES::table('SearchFilter');
		$table->load($id);

		if (!$table->id || !$table->canDelete()) {
			return $this->view->exception('You are not allowed to delete this filter');
		}

		// Try to delete the filter now
		$table->delete();

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_DELETED');

		return $this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Allows creating of a search filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		// Ensure that the user is really allowed to create filters
		$access = $this->my->getAccess();
		if (!$access->allowed('search.create.filter')) {
			die('Disallowed');
		}

		$title = $this->input->get('title', '', 'string');
		$sitewide = $this->input->get('sitewideFilter', false, 'boolean');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'cmd');
		$data = $this->input->get('data', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		$filter = ES::table('SearchFilter');
		$filter->load($id);

		$filter->title = $title;
		$filter->uid = $type == SOCIAL_TYPE_USER ? $this->my->id : 0;
		$filter->element = $type;
		$filter->created_by = $this->my->id;
		$filter->filter = $data;
		$filter->created = ES::date()->toMySQL();
		$filter->sitewide = ($this->my->isSiteAdmin() && $sitewide) ? 1 : 0;

		$filter->store();

		$this->view->setMessage('COM_EASYSOCIAL_ADVANCED_SEARCH_FILTER_SAVED');

		return $this->view->call(__FUNCTION__, $filter);
	}

	/**
	 * Renders a preset of advanced search criteria
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFilterResults()
	{
		ES::requireLogin();
		ES::checkToken();

		$showNew = false;

		// Get the filter id
		$id = $this->input->get('id', 0, 'int');

		$filter = ES::table('SearchFilter');
		$filter->load($id);

		// Defaults
		$searchConfig = array();
		$searchConfig['criteria'] = '';
		$searchConfig['match'] = 'all';
		$searchConfig['avatarOnly'] = 0;
		$searchConfig['sort'] = $this->config->get('users.advancedsearch.sorting', 'default');
		$searchConfig['total'] = 0;
		$searchConfig['results'] = null;
		$searchConfig['nextlimit'] = null;

		$lib = ES::advancedsearch($filter->element);
		$options = array('showPlus' => true);
		$displayOptions = array();

		$searchConfig = $filter->getSearchConfig();

		$results = null;
		$total = 0;
		$nextlimit = null;

		if ($searchConfig['criterias']) {

			if ($filter->element == SOCIAL_FIELDS_GROUP_USER) {
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

			$results = $lib->search($searchConfig);
			$displayOptions = $lib->getDisplayOptions();
			$total = $lib->getTotal();
			$nextlimit = $lib->getNextLimit();
		}

		// Generate the criteria html codes
		$criteriaHTML = $lib->getCriteriaHTML($options, $searchConfig);

		// this is doing new search
		if (!$criteriaHTML) {
			$showNew = true;
		}

		$searchConfig['criteria'] = $criteriaHTML;
		$searchConfig['match'] = $searchConfig['match'];
		$searchConfig['avatarOnly'] = $searchConfig['avatarOnly'];
		$searchConfig['sort'] = $searchConfig['sort'];
		$searchConfig['total'] = $total;
		$searchConfig['results'] = $results;
		$searchConfig['nextlimit'] = $nextlimit;
		$searchConfig['displayOptions']	= $displayOptions;

		if ($showNew) {
			$criteriaHTML = $lib->getCriteriaHTML($options);
			$searchConfig['criteria'] = $criteriaHTML;
		}

		return $this->view->call(__FUNCTION__, $lib, $filter, $searchConfig);
	}

	/**
	 * Responsible to display more results from the advanced search
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function loadmore()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the data from request
		$data = $this->input->get('data', '', 'default');
		$nextlimit = $this->input->get('nextlimit', 0, 'int');

		$filter = json_decode($data);
		$groupType = isset($filter->type) ? $filter->type : SOCIAL_FIELDS_GROUP_USER;

		// Load up advanced search library
		$lib = ES::advancedsearch($groupType);

		// Get the search configuration
		$searchConfig = array();
		$searchConfig['criterias'] = ES::makeArray($filter->{'criterias[]'});
		$searchConfig['operators'] = ES::makeArray($filter->{'operators[]'});
		$searchConfig['conditions']	= ES::makeArray($filter->{'conditions[]'});
		$searchConfig['datakeys'] = ES::makeArray($filter->{'datakeys[]'});

		// perform search
		$searchConfig['match'] = $filter->matchType;
		$searchConfig['avatarOnly']	= isset($filter->avatarOnly) ? true : false;
		$searchConfig['onlineOnly']	= isset($filter->onlineOnly) ? true : false;
		$searchConfig['sort'] = isset($filter->sort) ? $filter->sort : $this->config->get('users.advancedsearch.sorting', 'default');
		$searchConfig['nextlimit'] 	= $nextlimit;

		$results = null;
		$total = 0;
		$nextlimit = null;
		$displayOptions = array();

		if ($searchConfig['criterias']) {

			if ($groupType == SOCIAL_FIELDS_GROUP_USER) {
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

			$results = $lib->search($searchConfig);
			$displayOptions = $lib->getDisplayOptions();
			$total = $lib->getTotal();
			$nextlimit = $lib->getNextLimit();
		}

		return $this->view->call(__FUNCTION__, $groupType, $results, $nextlimit, $displayOptions);
	}

	/**
	 * Retrieves the criteria options for a specific field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCriteria()
	{
		ES::checkToken();

		$key = $this->input->get('key', '', 'default');
		$element = $this->input->get('element', '', 'default');
		$datakey = $this->input->get('datakey', '', 'default');
		$type = $this->input->get('type', SOCIAL_FIELDS_GROUP_USER, 'default');

		// Set the default options
		$options = [];
		$options['fieldCode'] = $key;
		$options['fieldType'] = $element;

		// Load up advanced search library
		$lib = ES::advancedsearch($type);

		// Get the datakey's html codes
		// $hasKey = $lib->hasDataKey($element);
		$hasKey = false;
		$keys = $lib->getDataKeyHTML($options, $hasKey);
		$operators = $lib->getOperatorHTML($options);

		// Retrieve default condition
		$options['fieldOperator'] = 'equal';
		$condition = $lib->getConditionHTML($options);

		return $this->view->call(__FUNCTION__, $hasKey, $keys, $operators, $condition);
	}

	/**
	 * Given a particular data key, retrieve the correct operators and conditions
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOperators()
	{
		ES::checkToken();

		$key = $this->input->get('key', '', 'default');
		$element = $this->input->get('element', '', 'default');
		$datakey = $this->input->get('datakey', '', 'default');
		$type = $this->input->get('type', SOCIAL_FIELDS_GROUP_USER, 'default');

		// Set the default options
		$options = array();
		$options['fieldCode'] = $key;
		$options['fieldType'] = $element;

		if ($datakey) {
			$options['fieldKey'] = $datakey;
		}

		// Load up advanced search library
		$lib = ES::advancedsearch($type);
		$operator = $lib->getOperatorHTML($options);

		// now we get the default condition
		$options['fieldOperator'] = 'equal';
		$condition = $lib->getConditionHTML($options);

		return $this->view->call(__FUNCTION__, $operator, $condition);
	}


	/**
	 * Allows caller to get a list of conditions for advanced search
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getConditions()
	{
		// Check for request forgeries.
		ES::checkToken();

		$element = $this->input->get('element', '', 'default');
		$operator = $this->input->get('operator', '', 'default');
		$datakey = $this->input->get('datakey', '', 'default');
		$type = $this->input->get('type', SOCIAL_FIELDS_GROUP_USER, 'default');

		$options = array();
		$options['fieldType'] = $element;
		$options['fieldOperator'] = $operator;

		if ($datakey) {
			$options['fieldKey'] = $datakey;
		}

		$lib = ES::advancedsearch($type);
		$conditionHTML = $lib->getConditionHTML($options);

		return $this->view->call(__FUNCTION__, $conditionHTML);
	}
}
