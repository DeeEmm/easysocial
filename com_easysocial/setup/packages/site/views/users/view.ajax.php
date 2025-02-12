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

ES::import('site:/views/views');

class EasySocialViewUsers extends EasySocialSiteView
{
	/**
	 * Post processing when filtering users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter($result)
	{
		// Default sorting
		$sort = $this->config->get('users.listings.sorting');
		$id = $this->input->get('id', 0, 'default');

		$filter = $this->input->get('type', '', 'word');
		$activeProfile = false;

		if ($id && ($id == 'friends' || $id == 'followers' || $id == 'verified')) {
			$filter = $id;

			$id = 0;
		}

		if (isset($result->profile) && $result->profile) {
			$activeProfile = $result->profile;
		}

		$users = $result->users;
		$pagination = $result->pagination;
		$displayOptions = isset($result->displayOptions) ? $result->displayOptions : false;
		$searchFilter = isset($result->searchFilter) ? $result->searchFilter : false;

		$actualFilter = $this->input->get('id', 'all', 'word');
		$sortItems = new stdClass();
		$sortingTypes = array('latest', 'lastlogin', 'alphabetical');

		// Default snackbar titles
		$snackbarTitle = 'COM_EASYSOCIAL_USERS';

		if ($filter == 'profiles') {
			$actualFilter = 'profiles';
		} elseif ($actualFilter) {
			$snackbarTitle = 'COM_ES_USERS_FILTER_USERS_' . strtoupper($actualFilter);
		}

		// Fixed messed up filter wording. #1680
		$filterRewording = array(
					'profiles' => 'profiletype',
					'all' => 'all',
					'photos' => 'photos',
					'online' => 'online',
					'blocked' => 'blocked'
				);

		if (isset($filterRewording[$actualFilter])) {
			$filter = $filterRewording[$actualFilter];
		}

		$helper = ES::viewHelper('Users', 'List');

		// display the proper sorting name for the page title.
		$displaySortingName = $helper->getPageTitle(true);

		if ($filter == 'search' && $id) {
			$searchFilter = ES::table('SearchFilter');
			$searchFilter->load($id);

			$displaySortingName = $searchFilter->get('title');
		}

		foreach ($sortingTypes as $sortingType) {

			$sortItems->{$sortingType} = new stdClass();

			$sortingTitle = $helper->getSortingTitle($sortingType);
			$displayPageTitle = $displaySortingName . ' - ' . $sortingTitle;

			// attributes
			// some of the filter type is referring the id instead of the sort name
			// data-filterId attribute use to determine 'search' and 'profiles' filter type data id
			$sortAttributes = array(
				'data-sort',
				'data-filter="' . $filter . '"',
				'data-type="' . $sortingType . '"',
				'data-filterid="' . $id . '"',
				'title="' . $displayPageTitle . '"'
			);

			//url
			$urlOptions = array();
			$urlOptions['filter'] = $filter;
			$urlOptions['sort'] = $sortingType;

			if (isset($id) && $id) {

				if ($filter == 'profiletype') {
					$profile = ES::table('Profile');
					$profile->load($id);

					$id = $profile->getAlias();
				}

				$urlOptions['id'] = $id;
			}

			$sortUrl = ESR::users($urlOptions);

			$sortItems->{$sortingType}->attributes = $sortAttributes;
			$sortItems->{$sortingType}->url = $sortUrl;
		}

		// Set the active sort item
		if ($result->sortRequest && $result->activeSort) {
			$sort = $result->activeSort;
		}

		$theme = ES::themes();
		$theme->set('searchFilter', $searchFilter);
		$theme->set('activeProfile', $activeProfile);
		$theme->set('displayOptions', $displayOptions);
		$theme->set('pagination', $pagination);
		$theme->set('users', $users);
		$theme->set('showSort', true);
		$theme->set('filter', $result->filter);
		$theme->set('sort', $sort);
		$theme->set('sortItems', $sortItems);
		$theme->set('snackbarTitle', $snackbarTitle);

		$namespace = 'wrapper';

		$contents = $theme->output('site/users/default/wrapper');

		return $this->ajax->resolve($contents, $result->hasSorting);
	}

	/**
	 * Render upcoming birthday for user's friends
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUpcomingBirthday()
	{
		$moduleId = $this->input->get('moduleId', 0, 'int');

		if (!$moduleId) {
			die();
		}

		$module = JTable::getInstance('Module');
		$module->load($moduleId);

		// Ensure that we are only rendering our type of module
		if ($module->module != 'mod_easysocial_upcoming_birthday') {
			die();
		}

		$params = new JRegistry($module->params);
		$fieldKey = $params->get('fieldkey', 'BIRTHDAY');
		$limit = (int) $params->get('limit', 10);
		$showFriendOnlyWithoutPrivacy = $params->get('show_friends_only', false);
		$options = ['showFriendOnlyWithoutPrivacy' => $showFriendOnlyWithoutPrivacy];

		$model = ES::model('Users');
		$birthdays = $model->getUpcomingBirthdays($fieldKey, $this->my->id, $limit, null, null, $options);
		$pagination = $model->getPagination();
		$paginationData = $pagination->getData();

		require_once(JPATH_ROOT . '/modules/mod_easysocial_upcoming_birthday/helper.php');

		$birthdays = EasySocialModBirthdaysHelper::format($birthdays, $params);

		$theme = ES::themes();
		$theme->set('birthdays', $birthdays);

		$output = $theme->output('site/modules/mod_easysocial_upcoming_birthday/default');

		return $this->ajax->resolve($output, $pagination->pagesCurrent, $paginationData->previous, $paginationData->next);
	}

	/**
	 * Responsible to render a popbox containing a list of users
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function popbox()
	{
		ES::language()->loadSite();

		$ids = $this->input->get('ids', '', 'default');

		if (!$ids) {
			return $this->ajax->reject();
		}

		$ids = explode('|', $ids);
		$users = ES::user($ids);

		$theme = ES::themes();
		$theme->set('users', $users);
		$output = $theme->output('site/users/popbox.users');

		return $this->ajax->resolve($html);
	}
}
