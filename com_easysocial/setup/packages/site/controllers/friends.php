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

jimport('joomla.mail.helper');

class EasySocialControllerFriends extends EasySocialController
{
	/**
	 * Allows caller to invite other users
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function sendInvites()
	{
		ES::requireLogin();
		ES::checkToken();

		// We should not allow anyone to send invites if it has been disabled.
		if (!$this->config->get('friends.invites.enabled')) {
			die();
		}

		// Get the list of emails
		$emails = $this->input->get('emails', '', 'html');

		if (!$emails) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_PLEASE_ENTER_EMAILS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$emails = explode("\n", $emails);

		// Get the message
		$message = $this->input->get('message', '', 'default');

		$model = ES::model('Registration');

		foreach ($emails as $email) {

			// Ensure that the e-mail is valid
			$email = trim($email);
			$valid = JMailHelper::isEmailAddress($email);

			if (!$valid) {
				$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_INVALID_EMAIL', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			$table = ES::table('FriendInvite');

			// Check if this email has been invited by this user before
			$table->load(['email' => $email, 'user_id' => $this->my->id]);

			// Skip this if the user has already been invited before.
			if ($table->id) {
				continue;
			}

			// Check if the e-mail is already registered on the site
			$exists = $model->isEmailExists($email);

			if ($exists) {
				$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_EMAIL_EXISTS_ON_SITE', $email), ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			$table->email = $email;
			$table->user_id = $this->my->id;
			$table->message = $message;
			$table->utype = 'site';
			$table->uid = 0;

			$table->store();
		}

		$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVITE_SENT_INVITATIONS', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Creates a new friend list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function storeList()
	{
		ES::requireLogin();

		// Check for request forgeries.
		ES::checkToken();

		if (! $this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get post data.
		$data = $this->input->post->getArray();

		// Detect if this is an edited list or a new list
		$id = $this->input->get('id', 0, 'int');

		$list = ES::lists($id);
		$list->bind($data);

		if (!$list->canCreateList()) {
			return $this->view->exception('COM_EASYSOCIAL_FRIENDS_LISTS_ACCESS_NOT_ALLOWED');
		}

		// Get friends from this list
		$friendIds = $this->input->get('uid', '', 'default');

		$state = $list->save($friendIds);

		if ($state === false) {
			$this->view->setMessage($list->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_LISTS_CREATED_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $list);
	}

	/**
	 * Checks for new friend requests
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRequests()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$model = ES::model('Friends');
		$requests = $model->getPendingRequests($this->my->id);

		return $this->view->call(__FUNCTION__, $requests);
	}

	/**
	 * Retrieve the counts
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCounters()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$model = ES::model('Friends');

		$totalFriends = $model->getTotalFriends($this->my->id, array('state' => SOCIAL_FRIENDS_STATE_FRIENDS));
		$totalPendingFriends = $model->getTotalPendingFriends($this->my->id);
		$totalRequestSent = $model->getTotalRequestSent($this->my->id);
		$totalSuggest = $model->getSuggestedFriends($this->my->id, null, true);

		return $this->view->call(__FUNCTION__, $totalFriends, $totalPendingFriends, $totalRequestSent, $totalSuggest);
	}

	/**
	 * Gets all the count of the user's friend lists.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getListCounts()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$model = ES::model('Lists');
		$lists = $model->getLists(array('user_id' => $this->my->id));

		return $this->view->call(__FUNCTION__, $lists);
	}

	/**
	 * Adds a list of user into a friend list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assign()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get list of user id's.
		$ids = $this->input->get('uid', '', 'default');
		$ids = ES::makeArray($ids);

		if (!$ids) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_NO_FRIEND_SELECTED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the list
		$id = $this->input->get('listId', 0, 'int');
		$list = ES::lists($id);

		if (!$list->id || !$id) {
			return $this->view->exception('COM_EASYSOCIAL_FRIENDS_INVALID_LIST_ID');
		}

		// Add the user to the list.
		$state = $list->addPeople($ids);

		if ($state === false) {
			return $this->view->exception($list->getError());
		}

		return $this->view->call(__FUNCTION__, $ids);
	}

	/**
	 * Suggest a list of friend names for a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function filter()
	{
		ES::requireLogin();

		// Check for valid tokens.
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load friends model.
		$model = ES::model('Friends');

		// Get the filter types.
		$type = $this->input->get('filter', 'all', 'cmd');
		$userId = $this->input->get('userid', null, 'int');

		// Get the target user
		$user = ES::user($userId);

		// Ensure that the user can view the targetted user friends view
		if (!$user->isViewer() && !$this->my->canView($user, 'friends.view')) {
			$this->view->setMessage('COM_ES_NOT_ALLOWED_TO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Determine if this is a public filter
		$publicFilter = array('all', 'mutual');

		// Ensure that only the targeted person can see this data. #2755
		if (!in_array($type, $publicFilter)) {
			if (!$user->isViewer()) {

				// We cam just throw error here
				$this->view->setMessage('COM_ES_NOT_ALLOWED_TO_ACCESS', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}
		}

		// Determines the total items to show per page
		$limit = ES::getLimit('friendslimit');

		$friends = array();
		$options = array('limit' => $limit);
		$userAlias = $user->getAlias();

		if ($type == 'list') {

			// Get the list id
			$id = $this->input->get('id', 0, 'int');

			// Try to load the list.
			$list = ES::table('List');
			$state = $list->load($id);

			if (!$id || !$state || ($list->id && !$list->isOwner())) {
				$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVALID_LIST_ID', ES_ERROR);
				return $this->view->call(__FUNCTION__, $type, array(), null, $user);
			}

			// Update the options
			$options['list_id'] = $list->id;

			// Get the result
			$friends = $model->getFriends($list->user_id, $options);
		}

		if ($type == 'pending') {
			$options['state'] = SOCIAL_FRIENDS_STATE_PENDING;
			$friends = $model->getFriends($user->id, $options);
		}

		if ($type == 'all') {
			$options['state']	= SOCIAL_FRIENDS_STATE_FRIENDS;
			$friends = $model->getFriends($user->id, $options);
		}

		if ($type == 'mutual') {
			$friends = $model->getMutualFriends($this->my->id, $user->id, $limit);
		}

		if ($type == 'suggest') {
			$friends = $model->getSuggestedFriends($this->my->id, $limit);
			$userAlias = $this->my->getAlias();
		}

		if ($type == 'request') {
			$options['state'] = SOCIAL_FRIENDS_STATE_PENDING;
			$options['isRequest'] = true;

			$friends = $model->getFriends($user->id , $options);
		}

		if ($type == 'invites') {
			$friends = $model->getInvitedUsers($user->id);
		}

		// Get the pagination
		$pagination	= $model->getPagination();

		// Set additional vars for the pagination
		$itemId = ESR::getItemId('friends');

		$pagination->setVar('Itemid', $itemId);
		$pagination->setVar('view', 'friends');

		// Set additional vars for the pagination if this is a filter for friend list
		if ($type == 'list') {
			$pagination->setVar('id', $list->id);
		}

		if (!$user->isViewer()) {
			$pagination->setVar( 'userid' , $user->getAlias() );
		}

		$addUserAlias = ($type != 'suggest' && $type != 'pending' && $type != 'request' && $type != 'invites');

		if ($type == 'all' && $user->id == $this->my->id) {
			$addUserAlias = false;
		}

		if ($addUserAlias) {
			$pagination->setVar('userid', $userAlias);
		}

		if ($type != 'all') {
			$pagination->setVar('filter', $type);
		}

		return $this->view->call(__FUNCTION__, $type, $friends, $pagination, $user);
	}

	/**
	 * Suggest a list of friend names for a user in photo tagging.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggestPhotoTagging()
	{
		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}


		$this->suggest('photos.tagme');
	}

	/**
	 * Suggest a list of friend names for a user or a friend list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggestWithList($privacy = null)
	{
		ES::requireLogin();
		ES::checkToken();

		// Properties
		$search  = $this->input->get('search', '', 'default');
		$exclude = $this->input->get('exclude', '', 'default');
		$includeme = $this->input->get('includeme', 0, 'default');
		$showNonFriend = $this->input->get('showNonFriend', 0, 'int');
		$privacyRule = $this->input->get('privacyRule', '', 'default');
		$type = $this->input->get('type', '', 'default');

		// For conversation, we don't need to check for availability of friends feature
		if (!$this->config->get('friends.enabled') && $type != 'conversations') {
			return $this->view->exception('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED');
		}

		$model = ES::model('Friends');

		$type = $this->config->get( 'users.displayName' );

		//check if we need to apply privacy or not.
		$options = array();

		if ($privacy) {
			$options['privacy'] = $privacy;
		} else if ($privacyRule) {
			$options['privacy'] = $privacyRule;
		}

		if ($exclude) {
			$options['exclude'] = $exclude;
		}

		if ($includeme) {
			$options['includeme'] = $includeme;
		}

		// Always allow site admin to search any site user
		if ($showNonFriend || $this->my->isSiteAdmin()) {
			$options['everyone'] = true;
		}

		// Try to get the search result.
		$friends = $model->search($this->my->id, $search , $type, $options);

		// dump($friends);

		// Try to search a list of friends
		$listModel = ES::model('Lists');
		$friendsList = $listModel->search($this->my->id, $search);

		return $this->view->call(__FUNCTION__, $friends, $friendsList);
	}

	/**
	 * Suggest a list of friend names for a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggest()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load friends model.
		$model = ES::model('Friends');

		// Properties
		$search  = $this->input->get('search', '', 'default');
		$exclude = $this->input->get('exclude', '', 'default');
		$includeme = $this->input->get('includeme', 0, 'default');
		$showNonFriend = $this->input->get('showNonFriend', 0, 'int');
		$privacyRule = $this->input->get('privacyRule', '', 'default');
		$maxLimit = $this->input->get('maxLimit', SOCIAL_FRIENDS_SUGGEST_MAXLIMIT, 'int');

		// Determines if there is a custom privacy rule
		$privacy = $this->input->get('privacy', '', 'default');

		// Determine what type of string we should search for.
		$type = $this->config->get('users.displayName');

		//check if we need to apply privacy or not.
		$options = array();

		if ($maxLimit) {
			$options['maxLimit'] = $maxLimit;
		}

		if ($privacy) {
			$options['privacy'] = $privacy;
		} else if ($privacyRule) {
			$options['privacy'] = $privacyRule;
		}

		if ($exclude) {
			$options['exclude'] = $exclude;
		}

		if ($includeme) {
			$options['includeme'] = $includeme;
		}

		// Always allow site admin to search any site user
		if ($showNonFriend || $this->my->isSiteAdmin()) {
			$options['everyone'] = true;
		}

		// Determine if we should search all users on the site
		$searchType = $this->input->get('type', '', 'cmd');

		if ($searchType == 'invitegroup' && $this->config->get('groups.invite.nonfriends')) {
			$options['everyone'] = true;
		}

		if ($searchType == 'invitepage' && $this->config->get('pages.invite.nonfriends')) {
			$options['everyone'] = true;
		}

		if ($searchType == 'inviteevent' && $this->config->get('events.invite.nonfriends')) {
			$options['everyone'] = true;
		}

		// Force override to search everyone when friend system disabled.
		if (!$this->config->get('friends.enabled')) {
			$options['everyone'] = true;
		}

		$clusterID = $this->input->get('clusterId', '', 'int');

		if ($clusterID) {
			$table = ES::table('cluster');
			$table->load($clusterID);

			if ($table->id){
				$cluster = ES::cluster($table->cluster_type, $table->id);

				// Only search for cluster member
				if (!$cluster->isOpen()) {
					$options['clusterType'] = $cluster->getType();
					return $this->suggestClusterMembers($options);
				}

				if ($this->config->get($table->cluster_type .'s.tag.nonfriends') && $cluster->isOpen() && $cluster->isMember($this->my->id)) {
					$options['everyone'] = true;
				}
			}
		}

		// Try to get the search result.
		$result = $model->search($this->my->id , $search , $type, $options);

		return $this->view->call(__FUNCTION__, $result);
	}

	/**
	 * Suggest a list of cluster members
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function suggestClusterMembers($options = array())
	{
		// Cluster search
		$search = $this->input->get('search', '', 'default');
		$clusterId = $this->input->get('clusterId', isset($options['clusterId']) ? $options['clusterId'] : '', 'int');
		$clusterType = $this->input->get('clusterType', isset($options['clusterType']) ? $options['clusterType'] : '', 'default');
		$exclude = $this->input->get('exclude', isset($options['exclude']) ? $options['exclude'] : '', 'default');

		// Ensure that the cluster type is valid
		$clusterMap = array('group' => 'groups', 'event' => 'events', 'page' => 'pages');
		$clusterType = isset($clusterMap[$clusterType]) ? $clusterMap[$clusterType] : $clusterType;

		$model = ES::model($clusterType);
		$members = $model->getMembers($clusterId, array('state' => SOCIAL_GROUPS_MEMBER_PUBLISHED, 'search' => $search, 'users' => true));

		return $this->view->call('suggest', $members);
	}

	/**
	 * Creates a new friend request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function request()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user that is being added.
		$targetId = $this->input->get('id', 0, 'int');

		$friend = ES::friends($targetId);
		$state = $friend->request();

		if ($state === false) {
			$this->view->setMessage($friend->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $friend);
	}

	/**
	 * Allows user to approve a friend request sent to them
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve()
	{
		ES::requireLogin();

		// Check for request forgeries.
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the requester's id
		$id = $this->input->get('id', 0 ,'int');

		// Load up the friends library
		$friends = ES::friends($this->my->id, $id);
		$state = $friends->approve();

		if (!$state) {
			return $this->view->exception($friends->getError());
		}

		$callback = $this->input->get('viewCallback', __FUNCTION__, 'default');
		$allowedCallbacks = array(__FUNCTION__, 'notificationsApprove');

		if (!in_array($callback, $allowedCallbacks)) {
			$callback = __FUNCTION__;
		}

		return $this->view->call($callback, $friends);
	}

	/**
	 * Allows caller to cancel a friend request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancelRequest()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user id
		$targetId = $this->input->get('id', 0, 'int');

		// Load up our friends library
		$friends = ES::friends($targetId);
		$state = $friends->cancel();

		if ($state === false) {
			$this->setMessage($friends->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $friends);
	}

	/**
	 * Rejects a friend request
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reject()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the requester id
		$requesterId = $this->input->get('id', 0, 'int');

		// Load up the friends library
		$friends = ES::friends($this->my->id, $requesterId);
		$state = $friends->reject();

		if ($state === false) {
			return $this->view->exception($friends->getError());
		}

		return $this->view->call(__FUNCTION__, $friends);
	}

	/**
	 * Removes a user from the friend list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeFromList()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the user that's being removed from the list.
		$userId = $this->input->get('userId', 0, 'int');

		// Get the current list id.
		$listId = $this->input->get('listId', 0, 'int');

		// Try to load the list now.
		$list = ES::table('List');
		$state = $list->load($listId);

		if (!$listId || !$state) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_INVALID_LIST_ID', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Check if the list is owned by the current user.
		if (!$list->isOwner()) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_LIST_NOT_OWNER', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Try to delete the item from the list.
		$state = $list->deleteItem($userId);

		if (!$state) {
			$this->view->setMessage($list->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Unfriends a target
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unfriend()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the target user that will be removed.
		$targetId = $this->input->get('id', 0, 'int');

		$friends = ES::friends($targetId);
		$state = $friends->unfriend();

		if ($state === false) {
			$this->view->setMessage($friends->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $friends);
	}

	/**
	 * Deletes a friend list from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteList()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->config->get('friends.enabled')) {
			$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the list id.
		$id = $this->input->get('id', 0, 'int');

		// Try to load the list.
		$list = ES::table('List');
		$list->load($id);

		// Test if the id provided is valid.
		if (!$list->id || !$id) {
			return $this->view->exception('COM_EASYSOCIAL_LISTS_ERROR_LIST_INVALID');
		}

		// Test if the owner of the list matches.
		if (!$list->isOwner()) {
			return $this->view->exception('COM_EASYSOCIAL_LISTS_ERROR_LIST_IS_NOT_OWNED');
		}

		// Try to delete the list.
		$state = $list->delete();

		if (!$state) {
			return $this->view->exception($list->getError());
		}

		$this->view->setMessage('COM_EASYSOCIAL_FRIENDS_LIST_DELETE_SUCCESSFULLY', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $list);
	}

	/**
	 * Deletes a invites from the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteInvites()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the invite id.
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('FriendInvite');
		$table->load($id);

		if (!$table->id || ($table->id && $table->user_id != $this->my->id)) {
			return $this->view->exception('COM_EASYSOCIAL_INVITES_ERROR_INVITE_INVALID');
		}

		// Try to delete the invite.
		$state = $table->delete();

		if (!$state) {
			return $this->view->exception($table->getError());
		}

		return $this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Allow caller to resend invites.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function resendInvites()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the invite id.
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('FriendInvite');
		$table->load($id);

		if (!$table->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVITES_ERROR_INVITE_INVALID');
		}

		// Try to delete the invite.
		$state = $table->send();

		return $this->view->call(__FUNCTION__, $table);
	}
}
