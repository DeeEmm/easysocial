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

class EasySocialViewUsers extends EasySocialSiteView
{
	/**
	 * Displays a list of users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();
		ES::setMeta();

		$helper = $this->getHelper('List');
		$filter = $helper->getActiveFilter();
		$sort = $helper->getActiveSort(true);
		$admin = $helper->shouldIncludeAdmins();
		$fid = $helper->getActiveId();

		// Default snackbar title
		$snackbarTitle = 'COM_EASYSOCIAL_USERS';

		$options = array('includeAdmin' => $admin, 'exclusion' => $this->my->id, 'excludeUserListing' => true);

		// Get the limit of total users to be displayed
		$limit = ES::getLimit('userslimit');
		$options['limit'] = $limit;

		$model = ES::model('Users');

		// Do not display profile by default
		$profile = false;

		// Set the sorting options
		$prefix = $filter == 'search' ? '' : 'a.';

		if ($sort == 'alphabetical') {
			$nameField = $this->config->get('users.displayName') == 'username' ? $prefix . 'username' : $prefix . 'name';

			$options['ordering'] = $nameField;
			$options['direction'] = 'ASC';

			$title = 'COM_ES_PAGE_TITLE_USERS_SORTED_BY_NAME';
		} else if ($sort == 'latest') {
			$options['ordering'] = $prefix . 'id';
			$options['direction'] = 'DESC';

			$title = 'COM_ES_PAGE_TITLE_USERS_SORTED_BY_RECENTLY_REGISTERED';
		} elseif ($sort == 'lastlogin') {
			$options['ordering'] = $prefix . 'lastvisitDate';
			$options['direction'] = 'DESC';

			$title = 'COM_ES_PAGE_TITLE_USERS_SORTED_BY_RECENTLY_LOGGED_IN';
		}

		$title = JText::_($title);

		$searchFilter = '';
		$displayOptions = '';
		$pagination = false;
		$result = array();

		// Filter users by search id
		if ($filter == 'search') {
			$searchFilter = $helper->getActiveSearchFilter();

			// Retrieve the users
			$result = $model->getUsersByFilter($fid, $options);
			$pagination = $model->getPagination(true);

			$displayOptions = $model->getDisplayOptions();
		}

		if ($filter == 'profiletype') {
			$profile = $helper->getActiveProfile();

			if (!$fid || !$profile->id) {
				return ES::raiseError(404, JText::_('COM_EASYSOCIAL_404_PROFILE_NOT_FOUND'));
			}

			$options['profile']	= $fid;

			// we only want published user.
			$options['published'] = 1;

			// exclude users who blocked the current logged in user.
			$options['excludeblocked'] = 1;

			$values = array();
			$values['criterias'] = $this->input->getVar('criterias');
			$values['datakeys'] = $this->input->getVar('datakeys');
			$values['operators'] = $this->input->getVar('operators');
			$values['conditions'] = $this->input->getVar('conditions');

			if ($values['criterias']) {

				// lets do some clean up here.
				for ($i = 0; $i < count($values['criterias']); $i++) {
					$criteria = $values['criterias'][$i];
					$condition = $values['conditions'][$i];
					$datakey = $values['datakeys'][$i];
					$operator = $values['operators'][$i];

					if (trim($condition)) {
						$searchOptions['criterias'][] = $criteria;
						$searchOptions['datakeys'][] = $datakey;
						$searchOptions['operators'][] = $operator;

						$field = explode('|', $criteria);

						$fieldCode = $field[0];
						$fieldType = $field[1];

						if ($fieldType == 'birthday') {

							// currently the value from form is in age format. we need to convert it into date time.
							$ages = explode('|', $condition);

							// this happen when start has value and end has no value
							if (!isset($ages[1])) {
								$ages[1] = $ages[0];
							}

							//this happen when start is empty and end has value
							if ($ages[1] && !$ages[0]) {
								$ages[0] = $ages[1];
							}

							$startdate = '';
							$enddate = '';

							$currentTimeStamp = ES::date()->toUnix();

							if ($ages[0] == $ages[1]) {
								$start = strtotime('-' . $ages[0] . ' years', $currentTimeStamp);

								$year = ES::date($start)->toFormat('Y');
								$startdate = $year . '-01-01 00:00:01';
								$enddate = ES::date($start)->toFormat('Y-m-d') . ' 23:59:59';
							} else {

								if ($ages[0]) {
									$start = strtotime('-' . $ages[0] . ' years', $currentTimeStamp);
									$year = ES::date($start)->toFormat('Y');
									$enddate = $year . '-12-31 23:59:59';
								}

								if ($ages[1]) {
									$end = strtotime('-' . $ages[1] . ' years', $currentTimeStamp);
									$year = ES::date($end)->toFormat('Y');
									$startdate = $year . '-01-01 00:00:01';
								}
							}

							$condition = $startdate . '|' . $enddate;
						}

						$searchOptions['conditions'][] = $condition;
					}

				}

				$searchOptions['match'] = 'and';
				$searchOptions['avatarOnly'] = false;

				if ($fid) {
					$searchOptions['profile'] = $fid;
				}

				$result = $model->getUsersByFilter('0', $options, $searchOptions);

			} else {
				// Retrieve the users
				$result = $model->getUsers($options);
			}

			$pagination = $model->getPagination(true);
		}

		$otherFilters = array('online', 'photos', 'blocked', 'all', 'verified', 'friends', 'followers');

		if ($filter != 'search' && $filter != 'profiletype' && in_array($filter, $otherFilters)) {

			// Need to exclude the current logged in user.
			$option['exclusion'] = $this->my->id;

			if ($filter == 'online') {
				$options['login'] = true;
			}

			if ($filter == 'photos') {
				$options['picture'] = true;
			}

			if ($filter == 'blocked') {
				$options['blocked'] = true;
			}

			if ($filter == 'verified') {
				$options['verified'] = true;
			}

			if ($filter == 'friends') {
				$options['friendOnly'] = true;
			}

			if ($filter == 'followers') {
				$options['followersOnly'] = true;
			}

			// we only want published user.
			$options['published'] = 1;

			// exclude users who blocked the current logged in user.
			$options['excludeblocked'] = 1;

			// Retrieve the users
			$result = $model->getUsers($options);
			$pagination = $model->getPagination(true);

			$snackbarTitle = 'COM_ES_USERS_FILTER_USERS_' . strtoupper($filter);
		}

		// run privacy checking here.
		if ($result && $this->config->get('privacy.enabled')) {
			$tmp = array();

			$privacy = $this->my->getPrivacy();

			foreach ($result as $user) {

				if ($privacy->validate('profiles.search', $user->id, SOCIAL_TYPE_USER)) {
					$tmp[] = $user;
				}
			}

			// reset the results.
			$result = $tmp;
		}

		$title = $helper->getPageTitle();

		$this->page->title($title);
		$this->page->breadcrumb($title);

		// normalize the canonical link
		$canonicalOptions = ES::normalizeCanonicalOptions(true);

		// Add canonical tags for users page
		$this->page->canonical(ESR::users($canonicalOptions));

		// Define those query strings here
		if ($pagination && $filter != 'profiletype' && $filter != 'search') {
			$pagination->setVar('filter', $filter);
			$pagination->setVar('sort', $sort);
		}

		$users = $helper->cache($result);
		$profiles = $helper->getProfileTypes();
		$searchFilters = $helper->getSearchFilters();
		$sortItems = $helper->getSortables();

		$this->set('snackbarTitle', $snackbarTitle);
		$this->set('sortItems', $sortItems);
		$this->set('showSort', true);
		$this->set('profiles', $profiles);
		$this->set('activeProfile', $profile);
		$this->set('activeTitle', $title);
		$this->set('pagination', $pagination);
		$this->set('filter', $filter);
		$this->set('sort', $sort);
		$this->set('users', $users);
		$this->set('fid', $fid);
		$this->set('searchFilters', $searchFilters);
		$this->set('searchFilter', $searchFilter);
		$this->set('displayOptions', $displayOptions);

		return parent::display('site/users/default/default');
	}

	/**
	 * Displays a list of users on the site from dating search module
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function search($tpl = null)
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Default snackbar title
		$snackbarTitle = 'COM_EASYSOCIAL_USERS';

		// Retrieve the users model
		$model = ES::model('Users');

		$config = ES::config();
		$admin = $config->get('users.listings.admin') ? true : false;
		$options = array('includeAdmin' => $admin);

		$limit = ES::getLimit('userslimit');
		$options['limit'] = $limit;

		// Default title
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_USERS');

		$sort = $this->input->get('sort', '', 'default');

		// Get values from posted data
		$values = [];
		$values['criterias'] = $this->input->get('criterias', '', 'default');
		$values['datakeys'] = $this->input->get('datakeys', '', 'default');
		$values['operators'] = $this->input->get('operators', '', 'default');
		$values['conditions'] = $this->input->get('conditions', '', 'default');

		$avatarOnly = $this->input->get('avatarOnly', false, 'bool');
		$onlineOnly = $this->input->get('onlineOnly', false, 'bool');

		$searchOptions = array();

		// lets do some clean up here.
		for ($i = 0; $i < count($values['criterias']); $i++) {
			$criteria = $values['criterias'][$i];
			$condition = trim($values['conditions'][$i]);
			$datakey = $values['datakeys'][$i];
			$operator = $values['operators'][$i];

			$field = explode('|', $criteria);

			$fieldCode = $field[0];
			$fieldType = $field[1];

			if ($datakey == 'name' && $this->config->get('search.minimum')) {
				$length = ESJString::strlen($condition);

				if ($length < $this->config->get('search.characters')) {
					ES::info()->set(null, JText::sprintf('COM_ES_MIN_CHARACTERS_SEARCH', $this->config->get('search.characters')), SOCIAL_MSG_ERROR);

					$pagination = null;
					$result = null;
					$users = array();

					$displayOptions = $model->getDisplayOptions();

					$this->page->title($title);
					$this->page->breadcrumb($title);

					$filter = 'search';

					$createCustomFilter = false;

					if ($this->my->isSiteAdmin()) {
						$createCustomFilter = array('link' => ESR::search(array('layout' => 'advanced')), 'icon' => 'fa-plus');
					}

					$this->set('createCustomFilter', $createCustomFilter);
					$this->set('showSort', false);
					$this->set('issearch', true);
					$this->set('profiles', '');
					$this->set('activeProfile', '');
					$this->set('profile', '');
					$this->set('activeTitle', $title);
					$this->set('pagination', $pagination);
					$this->set('filter', $filter);
					$this->set('sort', $sort);
					$this->set('users', $users);
					$this->set('fid', '');
					$this->set('searchFilters', '');
					$this->set('searchFilter', '');
					$this->set('displayOptions', $displayOptions);
					$this->set('snackbarTitle', $snackbarTitle);

					return parent::display('site/users/default/default');
				}
			}

			if ($condition || ($fieldType == 'gender' && $condition === '0')) {

				if ($fieldType == 'gender' && $condition === '0') {
					// we need to switch the operator to 'notblank' if user search for 'all ' gender
					$operator = 'notblank';
				}

				if ($fieldType == 'address') {
					$loc = explode('|', $condition);

					$distance = isset($loc[0]) && $loc[0] ? $loc[0] : '';
					$latitude = isset($loc[1]) && $loc[1] ? $loc[1] : '';
					$longitude = isset($loc[2]) && $loc[2] ? $loc[2] : '';
					$address = isset($loc[3]) && $loc[3] ? $loc[3] : '';

					if (!$distance || !$latitude || !$longitude) {
						// reset address criteria.
						$condition = '';
						continue;
					}
				}

				if ($fieldType == 'birthday') {
					// currently the value from form is in age format. we need to convert it into date time.
					$ages = explode('|', $condition);

					$startdate = '';
					$enddate = '';

					$currentTimeStamp = ES::date()->toUnix();

					$startAge = isset($ages[0]) && $ages[0] ? $ages[0] : '';
					$endAge = isset($ages[1]) && $ages[1] ? $ages[1] : '';

					// if no values, lets ignore age search
					if (!$startAge && !$endAge) {
						$condition = '';
						continue;
					}

					// if either one has value
					if (isset($startAge) && isset($endAge) && $startAge == $endAge) {
						$start = strtotime('-' . $startAge . ' years', $currentTimeStamp);

						$year = ES::date($start)->toFormat('Y');
						$startdate = $year . '-01-01 00:00:01';
						$enddate = ES::date($start)->toFormat('Y-m-d') . ' 23:59:59';
					} else {

						if (isset($startAge) && $startAge) {
							$start = strtotime('-' . $startAge . ' years', $currentTimeStamp);

							$year = ES::date($start)->toFormat('Y');
							$enddate = $year . '-12-31 23:59:59';
						}

						if (isset($endAge) && $endAge) {
							$end = strtotime('-' . $endAge . ' years', $currentTimeStamp);

							$year = ES::date($end)->toFormat('Y');
							$startdate = $year . '-01-01 00:00:01';
						}
					}

					$condition = $startdate . '|' . $enddate;
				}

				$searchOptions['criterias'][] = $criteria;
				$searchOptions['datakeys'][] = $datakey;
				$searchOptions['operators'][] = $operator;
				$searchOptions['conditions'][] = $condition;
			}
		}

		$pagination = null;
		$result = null;
		$users = array();

		if ($searchOptions) {
			$searchOptions['match'] = 'all';
			$searchOptions['avatarOnly'] = $avatarOnly;
			$searchOptions['onlineOnly'] = $onlineOnly;
			$searchOptions['sort'] = $sort;

			// Retrieve the users
			$result = $model->getUsersByFilter('0', $options, $searchOptions);
			$pagination = $model->getPagination(true);

			$itemId = $this->input->get('Itemid', ESR::getItemId('users'), 'int');

			$pagination->setVar('Itemid', $itemId);
			$pagination->setVar('view', 'users');
			$pagination->setVar('layout', 'search');
			$pagination->setVar('filter', 'search');
			$pagination->setVar('option', 'com_easysocial');

			if ($avatarOnly) {
				$pagination->setVar('avatarOnly', $avatarOnly);
			}

			if ($onlineOnly) {
				$pagination->setVar('onlineOnly', $onlineOnly);
			}

			for ($i = 0; $i < count($values['criterias']); $i++) {

				$criteria = $values['criterias'][$i];
				$condition = $values['conditions'][$i];
				$datakey = $values['datakeys'][$i];
				$operator = $values['operators'][$i];

				$field = explode('|', $criteria);

				$fieldCode = $field[0];
				$fieldType = $field[1];

				$pagination->setVar('criterias[' . $i . ']' , $criteria);
				$pagination->setVar('datakeys[' . $i . ']' , $datakey);
				$pagination->setVar('operators[' . $i . ']' , $operator);
				$pagination->setVar('conditions[' . $i . ']' , $condition);
			}

			if ($result) {

				// run privacy checking here.
				if ($this->config->get('privacy.enabled')) {
					$tmp = array();

					$privacy = $this->my->getPrivacy();

					foreach ($result as $user) {
						if ($privacy->validate('profiles.search', $user->id, SOCIAL_TYPE_USER)) {
							$tmp[] = $user;
						}
					}

					// reset the results.
					$result = $tmp;
				}

				foreach ($result as $obj) {
					$users[] = ES::user($obj->id);
				}
			}
		}

		$displayOptions = $model->getDisplayOptions();

		// Set the page title
		$this->page->title($title);

		// Set the page breadcrumb
		$this->page->breadcrumb($title);

		$filter = 'search';

		$createCustomFilter = false;

		if ($this->my->isSiteAdmin()) {
			$createCustomFilter = array('link' => ESR::search(array('layout' => 'advanced')), 'icon' => 'fa-plus');
		}

		$this->set('createCustomFilter', $createCustomFilter);
		$this->set('showSort', false);
		$this->set('issearch', true);
		$this->set('profiles', '');
		$this->set('activeProfile', '');
		$this->set('profile', '');
		$this->set('activeTitle', $title);
		$this->set('pagination', $pagination);
		$this->set('filter', $filter);
		$this->set('sort', $sort);
		$this->set('users', $users);
		$this->set('fid', '');
		$this->set('searchFilters', '');
		$this->set('searchFilter', '');
		$this->set('displayOptions', $displayOptions);
		$this->set('snackbarTitle', $snackbarTitle);

		return parent::display('site/users/default/default');
	}
}
