<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewFriendsListHelper extends EasySocial
{
	private function createFilterLink($title, $pageTitle, $link)
	{
		$obj = new stdClass();
		$obj->label = JText::_($title);
		$obj->page_title = JText::_($pageTitle, true);
		$obj->link = $link;

		return $obj;
	}

	/**
	 * Determines the current active filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			// By default the view is "All Friends"
			$filter = $this->input->get('filter', 'all', 'cmd');

			$activeList = $this->getActiveList();

			if ($activeList) {
				$filter = 'list';
			}
		}

		return $filter;
	}

	/**
	 * Determines if the current request is filtering friends by friend list
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveList()
	{
		static $list = null;

		if (is_null($list)) {
			$list = false;
			$id = $this->input->get('listId', 0, 'int');

			// Get the active list
			if ($id) {
				$list = ES::table('List');
				$list->load($id);
			}
		}

		return $list;
	}

	/**
	 * Retrieves the active user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUser()
	{
		static $user = null;

		if (is_null($user)) {

			// Check if there's an id.
			// Okay, we need to use getInt to prevent someone inject invalid data from the url. just do another checking later.
			$id = $this->input->get('userid', null, 'int');

			// This is to ensure that the id != 0. if 0, we set to null to get the current user.
			if (empty($id)) {
				$id = null;
			}

			// Get the user.
			$user = ES::user($id);
		}

		return $user;
	}

	/**
	 * Retrieves the user alias if the viewer is viewing another person's profile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUserAlias()
	{
		static $alias = null;

		if (is_null($alias)) {
			$user = $this->getActiveUser();
			$alias = $user->isViewer() ? '' : $user->getAlias();
		}

		return $alias;
	}

	/**
	 * Retrieves the counters of the filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCounters()
	{
		static $counter = null;

		if (is_null($counter)) {
			$counter = new stdClass();
			$user = $this->getActiveUser();

			$model = ES::model('Friends');
			$counter->pending = $model->getTotalPendingFriends($user->id);
			$counter->sent = $model->getTotalRequestSent($user->id);
			$counter->lists = $user->getTotalFriendsList();
			$counter->friends = $model->getTotalFriends($user->id);
			$counter->invites = $model->getTotalInvites($user->id);
			$counter->suggestions = $model->getSuggestedFriends($this->my->id, null, true);

			$counter->mutual = 0;

			// We only want to run the query if the user is not the viewer
			if (!$user->isViewer()) {
				$counter->mutual = $model->getMutualFriendCount($this->my->id, $user->id);
			}
		}

		return $counter;
	}

	/**
	 * Generates the hyperlinks for filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilters()
	{
		static $links = null;

		if (is_null($links)) {
			$userAlias = $this->getActiveUserAlias();
			$filters = new stdClass();

			$filters->all = $this->createFilterLink('COM_EASYSOCIAL_FRIENDS_ALL_FRIENDS_FILTER', 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS', ESR::friends(array('userid' => $userAlias)));
			$filters->mutual = $this->createFilterLink('COM_EASYSOCIAL_FRIENDS_MUTUAL_FRIENDS_FILTER', 'COM_EASYSOCIAL_PAGE_TITLE_MUTUAL_FRIENDS', ESR::friends(array('filter' => 'mutual', 'userid' => $userAlias)));
			$filters->suggestion = $this->createFilterLink('COM_EASYSOCIAL_FRIENDS_SUGGEST_FRIENDS_FILTER', 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_SUGGESTIONS', ESR::friends(array('filter' => 'suggest', 'userid' => $userAlias)));
			$filters->pending = $this->createFilterLink('COM_EASYSOCIAL_FRIENDS_PENDING_APPROVAL_FILTER', 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_PENDING_APPROVAL', ESR::friends(array('filter' => 'pending', 'userid' => $userAlias)));
			$filters->sent = $this->createFilterLink('COM_EASYSOCIAL_FRIENDS_REQUEST_SENT_FILTER', 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_REQUESTS', ESR::friends(array('filter' => 'request', 'userid' => $userAlias)));
			$filters->invites = $this->createFilterLink('COM_EASYSOCIAL_FRIENDS_INVITED_FRIENDS', 'COM_EASYSOCIAL_FRIENDS_INVITED_FRIENDS', ESR::friends(array('filter' => 'invites', 'userid' => $userAlias)));
		}

		return $filters;
	}

	/**
	 * Retrieves a list of friend lists
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFriendLists()
	{
		static $lists = null;

		if (is_null($lists)) {
			$model = ES::model('Lists');
			$user = $this->getActiveUser();

			$lists = $model->getLists(array('user_id' => $user->id));
		}

		return $lists;
	}

	/**
	 * Generates the correct page title
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageTitle()
	{
		static $title = null;

		if (is_null($title)) {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS';
			$filter = $this->getActiveFilter();

			if ($filter == 'mutual') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_MUTUAL_FRIENDS';
			}

			if ($filter == 'pending') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_PENDING_APPROVAL';
			}

			if ($filter == 'request') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_REQUESTS';
			}

			if ($filter == 'suggest') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_SUGGESTIONS';
			}

			if ($filter == 'invites') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_INVITES';
			}

			$activeList = $this->getActiveList();

			if ($activeList) {
				$title = $activeList->get('title');
			}

			$user = $this->getActiveUser();

			if (!$user->isViewer()) {
				$title = $user->getName() . ' - ' . JText::_($title);
			}
		}

		return $title;
	}
}
