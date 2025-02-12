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

class EasySocialViewFriends extends EasySocialSiteView
{
	/**
	 * Default method to display a list of friends a user has.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();
		ES::setMeta();

		if (!$this->config->get('friends.enabled')) {
			return $this->exception('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED');
		}

		$helper = $this->getHelper('List');
		$user = $helper->getActiveUser();

		// If a guest is trying to view their friends, this should never happen
		if (!$user->id  || !$this->my->id) {
			return ES::requireLogin();
		}

		// If the user is blocked, they should not be accessible
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// Let's test if the current viewer is allowed to view this profile.
		if (!$user->isViewer() && !$this->my->canView($user, 'friends.view')) {
			$this->set('showProfileHeader', true);
			$this->set('user', $user);

			parent::display('site/friends/restricted');
			return;
		}

		// Lets check if this user is a ESAD user or not
		if (!$user->hasCommunityAccess()) {

			$facebook = ES::oauth('facebook');
			$return = base64_encode(ES::getURI(true));

			$this->set('return', $return);
			$this->set('facebook', $facebook);
			$this->set('user', $user);

			parent::display('site/profile/restricted');
			return;
		}

		$filters = $helper->getFilters();
		$userAlias = $helper->getActiveUserAlias();
		$title = $helper->getPageTitle();
		$filter = $helper->getActiveFilter();

		// Get the list of friends this user has.
		$model = ES::model('Friends');
		$limit = ES::getLimit('friendslimit');

		$options = array('state' => SOCIAL_FRIENDS_STATE_FRIENDS, 'limit' => $limit);


		// Determine if this is a public filter
		$publicFilter = array('all', 'mutual');

		// Ensure that the data can only be accessed by the same person that viewing the profile. #2755
		if (!in_array($filter, $publicFilter)) {
			if (!$user->isViewer()) {
				$user = $this->my;
			}
		}

		// If current view is pending, we need to only get pending friends.
		if ($filter == 'pending') {
			$options['state'] = SOCIAL_FRIENDS_STATE_PENDING;
		}

		if ($filter == 'request') {
			$options['state'] = SOCIAL_FRIENDS_STATE_PENDING;
			$options['isRequest'] = true;
		}

		$lists = $helper->getFriendLists();
		$activeList = $helper->getActiveList();

		if ($activeList) {

			// Ensure that user will be able to view their own user list. #2755
			if (!$activeList->isOwner()) {
				return $this->exception('COM_EASYSOCIAL_FRIENDS_INVALID_LIST_ID');
			}

			$options['list_id']	= $activeList->id;
		}

		$friends = array();

		if ($filter == 'mutual') {
			$limit = ES::getLimit('friendslimit');
			$friends = $model->getMutualFriends($this->my->id, $user->id, $limit);
		}

		if ($filter == 'request') {
			$options['state'] = SOCIAL_FRIENDS_STATE_PENDING;
			$options['isRequest'] = true;

			$friends = $model->getFriends($user->id, $options);
		}

		if ($filter == 'suggest') {
			$friends = $model->getSuggestedFriends($this->my->id, ES::getLimit('friendslimit'));
		}

		// Insert initial breadcrumb when user is viewing specific filters
		if ($filter == 'mutual' || $filter == 'pending' || $filter == 'request' || $filter == 'suggest') {
			$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_FRIENDS', ESR::friends());
		}

		// Ensure that invites are enabled
		if ($filter == 'invites' && !$this->config->get('friends.invites.enabled')) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_FEATURE_NOT_AVAILABLE'), 500);
		}

		if ($filter == 'invites') {
			$friends = $model->getInvitedUsers($user->id);
		}

		if ($filter == 'all' || $filter == 'pending' || $filter == 'list') {
			$friends = $model->getFriends($user->id, $options);
		}

		// Get pagination
		$pagination	= $model->getPagination();

		// Set additional params for the pagination links
		$pagination->setVar('view', 'friends');

		if (!$user->isViewer()) {
			$pagination->setVar('userid', $user->getAlias());
		}

		$this->page->title($title);
		$this->page->breadcrumb($title);

		// canonical links
		$options = array('external' => true);

		if (!$user->isViewer()) {
			$options['userid'] = $user->getAlias();
		}

		if ($filter && $filter == 'list' && $activeList) {
			$options['listId'] = $activeList->id;
		}

		if ($filter && $filter != 'all') {
			$options['userid'] = $userAlias;
			$options['filter'] = $filter;

			if ($filter == 'list') {
				$options['listId'] = $filter;
			}
		}

		$this->page->canonical(ESR::friends($options));

		$this->set('filters', $filters);
		$this->set('user', $user);
		$this->set('userAlias', $userAlias);
		$this->set('pagination', $pagination);
		$this->set('filter', $filter);
		$this->set('activeList', $activeList);
		$this->set('friends', $friends);
		$this->set('lists', $lists);

		return parent::display('site/friends/default/default');
	}

	/**
	 * Displays the list form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function listForm()
	{
		// Ensure that user is logged in.
		ES::requireLogin();

		// Check for user profile completeness
		ES::checkCompleteProfile();

		if (!$this->config->get('friends.enabled')) {
			return $this->exception('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED');
		}

		$this->info->set($this->getMessage());

		// Get the list id.
		$id = $this->input->get('id', 0, 'int');

		$list = ES::table('List');
		$list->load($id);

		if (!ES::lists()->canCreateList()) {
			return $this->exception('COM_EASYSOCIAL_FRIENDS_LISTS_ACCESS_NOT_ALLOWED');
		}

		// Check if this list is being edited.
		if ($id && !$list->id) {
			$this->setMessage('COM_EASYSOCIAL_FRIENDS_INVALID_LIST_ID_PROVIDED', SOCIAL_MSG_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::friends(array(), false));
		}

		// Set the page title
		$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_LIST_FORM';

		if ($list->id) {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_FRIENDS_EDIT_LIST_FORM';
		}

		// Get list of users from this list.
		$result = $list->getMembers();
		$members = array();

		if ($result) {
			$members = ES::user($result);
		}

		$this->set('members', $members);
		$this->set('list', $list);
		$this->set('id', $id);

		// Load theme files.
		echo parent::display('site/friends/listform/default');
	}

	/**
	 * Displays the invite friends form
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function invite()
	{
		// Requires user to be logged into the site
		ES::requireLogin();

		$clusterId = $this->input->get('cluster_id', 0, 'int');

		if ($clusterId) {
			return $this->inviteCluster($clusterId);
		}

		// Ensure that invites and friends are enabled
		if (!$this->config->get('friends.invites.enabled') || !$this->config->get('friends.enabled')) {
			return $this->redirect(ESR::dashboard([], false));
		}

		ES::setMeta();

		$defaultEditor = JFactory::getConfig()->get('editor');
		$editor = ES::editor()->getEditor($defaultEditor);

		$inviteUrl = ESR::registration(['rid' => $this->my->getAffiliationId(true), 'external' => true]);

		$this->set('inviteUrl', $inviteUrl);
		$this->set('editor', $editor);

		parent::display('site/friends/invite/default');
	}

	/**
	 * Invite friends via email in cluster
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function inviteCluster($clusterId)
	{
		$cluster = ES::cluster($clusterId);

		// Ensure that invites are enabled
		if (!$cluster->canInvite()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_FEATURE_NOT_AVAILABLE'), 500);
		}

		$defaultEditor = JFactory::getConfig()->get('editor');
		$editor = ES::editor()->getEditor($defaultEditor);

		$this->set('editor', $editor);
		$this->set('cluster', $cluster);
		parent::display('site/clusters/invite/default');
	}

	/**
	 * Post processing after inviting a friend
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function sendInvites()
	{
		ES::info()->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->redirect(ESR::friends(array('layout' => 'invite'), false));
		}

		return $this->redirect(ESR::friends(array('filter' => 'invites'), false));
	}

	/**
	 * Perform redirection after the list is created.
	 *
	 * @since	1.0
	 * @access	public
	 **/
	public function storeList($list)
	{
		if (! $this->config->get('friends.enabled')) {
			return $this->exception('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED');
		}

		$this->info->set($this->getMessage());

		$this->redirect(ESR::friends(array(), false));
	}

	/**
	 * This view is responsible to approve pending friend requests.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approve()
	{
		if (! $this->config->get('friends.enabled')) {
			return $this->exception('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED');
		}

		// Get the return url.
		$return = $this->input->get('return', null, 'base64');

		$info	= ES::info();

		// Set the message data
		$info->set($this->getMessage());

		return $this->redirect(ESR::friends(array(), false));
	}

	/**
	 * Post processing of delete list item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteList()
	{
		if (! $this->config->get('friends.enabled')) {
			return $this->exception('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED');
		}


		$this->info->set($this->getMessage());

		$redirect = ESR::friends(array(), false);

		return $this->redirect($redirect);
	}
}
