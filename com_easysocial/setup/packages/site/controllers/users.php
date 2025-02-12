<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerUsers extends EasySocialController
{
	/**
	 * Filter users on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter()
	{
		ES::checkToken();

		$allowed = array('users', 'search', 'profiles');
		$filter = $this->input->get('type', '', 'word');

		// Only allow known filters
		if (!in_array($filter, $allowed)) {
			die('Invalid filter type');
		}

		$method = 'filter' . ucfirst($filter);

		$sorting = $this->input->get('sorting', $this->config->get('users.listings.sorting'), 'word');

		// Load up the method
		$result = $this->$method($sorting);

		if ($result->users && $this->config->get('privacy.enabled')) {
			$tmp = array();

			$privacy = $this->my->getPrivacy();

			foreach ($result->users as $user) {

				if ($privacy->validate('profiles.search', $user->id, SOCIAL_TYPE_USER)) {
					$tmp[] = $user;
				}
			}

			// reset the results.
			$result->users = $tmp;
		}

		$result->filter = $filter;
		$result->sortRequest = $this->input->get('sortRequest', false, 'bool');

		return $this->view->call(__FUNCTION__, $result);
	}

	/**
	 * Retrieves the list of users on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function filterUsers($sort)
	{
		// Get the filter type
		$filter = $this->input->get('id', 0, 'word');
		$showPagination = $this->input->get('pagination', 0, 'int');

		$model = ES::model('Users');
		$options = array('exclusion' => $this->my->id, 'excludeUserListing' => true, 'limitstart' => 0);

		if ($sort == 'alphabetical') {
			$nameField = $this->config->get('users.displayName') == 'username' ? 'a.username' : 'a.name';

			$options['ordering'] = $nameField;
			$options['direction'] = 'ASC';
		} elseif($sort == 'latest') {

			$options['ordering'] = 'a.id';
			$options['direction'] = 'DESC';
		} elseif($sort == 'lastlogin') {

			$options['ordering'] = 'a.lastvisitDate';
			$options['direction'] = 'DESC';
		}

		if ($filter == 'online') {
			$options['login'] = true;
		}

		if ($filter == 'photos') {
			$options['picture']	= true;
		}

		if ($filter == 'blocked') {
			$options['blocked']	= true;
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

		// setup the limit
		$limit = ES::getLimit('userslimit');
		$options['limit'] = $limit;

		// Determine if we should display admins
		$admin = $this->config->get('users.listings.admin') ? true : false;

		$options['includeAdmin'] = $admin;

		// we only want published user.
		$options['published'] = 1;

		// exclude users who blocked the current logged in user.
		$options['excludeblocked'] = 1;

		$tmp = $model->getUsers($options);

		// Preload users
		$users = ES::user($tmp);

		$pagination = null;

		if ($showPagination) {
			$pagination	= $model->getPagination();

			// Define those query strings here
			$pagination->setVar('Itemid', ESR::getItemId('users'));
			$pagination->setVar('view', 'users');
			$pagination->setVar('filter' , $filter);
			$pagination->setVar('sort', $sort);
		}

		$result = new stdClass();
		$result->users = $users;
		$result->pagination = $pagination;
		$result->hasSorting = true;
		$result->activeSort = $sort;

		return $result;
	}

	/**
	 * Retrieves a list of users by specific profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function filterProfiles($sort)
	{
		// Get the profile id
		$id = $this->input->get('id', 0, 'int');

		$profile = ES::table('Profile');
		$profile->load($id);

		$model = ES::model('Users');
		$options = array('profile' => $id, 'ordering' => 'a.id', 'direction' => 'DESC', 'excludeUserListing' => true, 'limitstart' => 0);

		if ($sort == 'alphabetical') {
			$nameField = $this->config->get('users.displayName') == 'username' ? 'a.username' : 'a.name';

			$options['ordering'] = $nameField;
			$options['direction'] = 'ASC';

		} elseif($sort == 'latest') {

			$options['ordering'] = 'a.id';
			$options['direction'] = 'DESC';

		} elseif($sort == 'lastlogin') {

			$options['ordering'] = 'a.lastvisitDate';
			$options['direction'] = 'DESC';
		}

		// setup the limit
		$limit = ES::getLimit('userslimit');
		$options['limit'] = $limit;

		// we only want published user.
		$options['published'] = 1;

		// exclude users who blocked the current logged in user.
		$options['excludeblocked'] = 1;
		$options['includeAdmin'] = $this->config->get('users.listings.admin') ? true : false;

		// Exclude current logged in user
		if ($this->my->id) {
			$options['exclusion'] = $this->my->id;
		}

		// Get users
		$tmp = $model->getUsers($options);
		$users = ES::user($tmp);
		$pagination	= $model->getPagination();

		// Define those query strings here
		$pagination->setVar('Itemid', ESR::getItemId('users'));
		$pagination->setVar('view', 'users');
		$pagination->setVar('filter', 'profiletype');
		$pagination->setVar('id', $profile->getAlias());


		if ($sort) {
			$pagination->setVar('sort', $sort);
		}

		$result = new stdClass();
		$result->users = $users;
		$result->pagination = $pagination;
		$result->profile = $profile;
		$result->hasSorting = true;
		$result->activeSort = $sort;

		return $result;
	}

	/**
	 * Retrieves a list of users by sitewide search filter
	 *
	 * @since	1.3
	 * @access	public
	 */
	private function filterSearch()
	{
		// Get the search filter id
		$id = $this->input->get('id', 0, 'int');
		$sort = $this->input->get('sorting', '', 'word');

		$options = array('limit' => ES::getLimit('userslimit'), 'excludeUserListing' => true);

		if ($sort == 'alphabetical') {
			$nameField = $this->config->get('users.displayName') == 'username' ? 'username' : 'name';

			$options['ordering'] = $nameField;
			$options['direction'] = 'ASC';
		} elseif($sort == 'latest') {

			$options['ordering'] = 'id';
			$options['direction'] = 'DESC';
		} elseif($sort == 'lastlogin') {

			$options['ordering'] = 'lastvisitDate';
			$options['direction'] = 'DESC';
		}

		$search = ES::table('SearchFilter');
		$search->load($id);

		$model = ES::model('Users');
		$tmp = $model->getUsersByFilter($id, $options);

		// // For some reason using this method will break the sorting as stored user from the cache will always be at the top. #1362
		// // Commented out for now.
		// $users = ES::user($tmp, true);

		// Alt method
		$users = array();

		foreach ($tmp as $user) {
			$users[] = ES::user($user->id);
		}

		// Define those query strings here
		$pagination	= $model->getPagination();
		$pagination->setVar('Itemid', ESR::getItemId('users'));
		$pagination->setVar('view', 'users');
		$pagination->setVar('filter', 'search');
		$pagination->setVar('id', $id);

		if ($sort) {
			$pagination->setVar('sort', $sort);
		}

		$result = new stdClass();
		$result->users = $users;
		$result->pagination = $model->getPagination();
		$result->displayOptions = $model->getDisplayOptions();
		$result->searchFilter = $search;
		$result->hasSorting = true;
		$result->activeSort = $sort;

		return $result;
	}

	/**
	 * Service Hook for explorer
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function explorer()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->getint('uid');
		$user = ES::user($id);

		// Determine if the viewer can really view items
		if (!$user->isViewer()) {
			return $this->view->call(__FUNCTION__);
		}

		// Load up the explorer library
		$explorer = ES::explorer($user->id, SOCIAL_TYPE_USER);
		$hook = $this->input->get('hook', '', 'cmd');
		$result = $explorer->hook($hook);

		$exception = ES::response('Folder retrieval successful', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $exception, $result);
	}
}
