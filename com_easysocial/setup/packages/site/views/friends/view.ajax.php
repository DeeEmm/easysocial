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
	 * This returns the html block for items generated via the data api
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function popboxRequest($friendId = null)
	{
		if (is_object($friendId)) {
			$friendId = $friendId->id;
		}

		$theme = ES::themes();
		$theme->set('friendId', $friendId);
		$contents = $theme->output('site/friends/request.popbox');

		return $this->ajax->resolve($contents);
	}

	/**
	 * This returns the html dialog because the user exceeded their friend request limit.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function exceeded($friend = null)
	{
		$theme = ES::themes();
		$contents = $theme->output('site/friends/dialog.exceeded');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation to delete a friend list
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assignList()
	{
		// Only registered users allowed here
		ES::requireLogin();

		// Get the target id.
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$list = ES::table('List');
		$list->load($id);

		// Get a list of users that are already in this list.
		$users = $list->getMembers();
		$users = json_encode($users);

		$theme = ES::themes();
		$theme->set('list', $list);
		$theme->set('users', $users);

		$contents = $theme->output('site/friends/dialogs/list.assign');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation to delete a friend list
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDeleteList()
	{
		// Only registered users allowed here
		ES::requireLogin();

		// Get the target id.
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$list = ES::table('List');
		$list->load($id);

		$theme = ES::themes();
		$theme->set('list', $list);

		$contents = $theme->output('site/friends/dialogs/delete.list');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation to reject a friend request
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmReject()
	{
		// Only registered users allowed here
		ES::requireLogin();

		// Get the target id.
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$user = ES::user($id);

		$theme = ES::themes();
		$theme->set('user', $user);

		$contents = $theme->output('site/friends/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a friend request is rejected
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reject(SocialFriends $friends)
	{
		$button = ES::themes()->html('user.friends', $friends->getRequester());

		return $this->ajax->resolve($button);
	}

	/**
	 * Display confirmation message before cancelling the friend request.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmCancel()
	{
		// Require user to be logged in.
		ES::requireLogin();

		$theme = ES::themes();
		$output = $theme->output('site/profile/dialogs/friends.cancel');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a friend request is cancelled
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancelRequest($friends)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		// Get the new button that should be applied
		$button = ES::themes()->html('user.friends', $friends->getTarget());

		return $this->ajax->resolve($button);
	}


	/**
	 * Post processing after invitation is removed
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteInvites($invitation)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		return $this->ajax->resolve();
	}

	/**
	 * Post processing after invitation is resent
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function resendInvites($invitation)
	{
		$info = JText::_('COM_EASYSOCIAL_FRIENDS_RESENT_INVITE_SUCCESS');

		return $this->ajax->resolve($info);
	}

	/**
	 * Cancels a friend request.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getListCounts($lists = array())
	{
		$result = array();

		if (!$lists) {
			return $this->ajax->resolve($result);
		}

		foreach ($lists as $list) {

			$data = new stdClass();
			$data->id = $list->id;
			$data->count = $list->getCount();

			$result[] = $data;
		}

		return $this->ajax->resolve($result);
	}

	/**
	 * Executes when a user is removed from the list.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeFromList()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Assigns a user into a friend list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function assign($users = array())
	{
		$users = ES::user($users);
		$contents = array();

		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$theme = ES::themes();

		foreach ($users as $user) {
			$contents[] = $theme->html('listing.user', $user, array('showRemoveFromList' => true));
		}

		return $this->ajax->resolve($contents);
	}

	/**
	 * Responsible to return html codes to the ajax calls.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function filter($filter, $friends, $pagination, $user)
	{
		$list = false;

		// Default file
		$file = 'items';

		if ($filter == 'invites') {
			$file = 'invites';
		}

		if ($filter == 'list') {
			$id = $this->input->get('id', 0, 'int');

			$list = ES::table('List');
			$list->load($id);
		}

		$theme = ES::themes();

		$theme->set('pagination', $pagination);
		$theme->set('friends', $friends);
		$theme->set('filter', $filter);
		$theme->set('user', $user);
		$theme->set('activeList', $list);

		$output = $theme->output('site/friends/default/' . $file);

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a friend request is initiated
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function request(SocialFriends $friends, $html = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}
		// Get the new button that should be applied
		$button = ES::themes()->html('user.friends', $friends->getTarget());

		return $this->ajax->resolve($button);
	}

	/**
	 * Retrieves a list of friend requests
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRequests($requests)
	{
		$theme = ES::themes();
		$theme->set('requests', $requests);
		$output = $theme->output('site/friends/popbox/notifications');

		return $this->ajax->resolve($output);
	}

	/**
	 * Retrieve counters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCounters($totalFriends, $totalPendingFriends, $totalRequestSent, $totalSuggestedFriends)
	{
		return $this->ajax->resolve($totalFriends, $totalPendingFriends, $totalRequestSent, $totalSuggestedFriends);
	}

	/**
	 * Post processing after a user approves the friend request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve(SocialFriends $friends)
	{
		// Get the initiator's information
		$user = $friends->getRequester();

		$output = ES::themes()->html('user.friends', $user);

		$message = JText::sprintf('COM_EASYSOCIAL_FRIENDS_USER_IS_NOW_YOUR_FRIEND', $user->getName());

		return $this->ajax->resolve($output, $message);
	}

	/**
	 * Post processing after a user is being unfriend
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unfriend(SocialFriends $friends)
	{
		$button = ES::themes()->html('user.friends', $friends->getTarget());

		return $this->ajax->resolve($button);
	}

	/**
	 * Suggest a mixin between users and friend lists.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggestWithList($friends, $friendLists)
	{
		// Format result to SocialUser object.
		$result = array();

		// If there's nothing, just return the empty object.
		if (!$friends && !$friendLists) {
			return $this->ajax->resolve($result);
		}

		// Load through the result list.
		if ($friendLists) {

			$inputName = $this->input->getVar('friendListName');

			foreach ($friendLists as $list) {
				$obj = new stdClass();

				$obj->id = 'list-' . $list->id;
				$obj->title = $list->title;

				// Get the item's html output
				$theme = ES::themes();
				$theme->set('icon', 'fa-users');
				$theme->set('list', $list);
				$theme->set('inputName', $inputName);
				$obj->html = $theme->output('site/suggest/list.item');

				$obj->menuHtml = $list->title;
				$obj->className = 'list';

				$result[] = $obj;
			}
		}

		// Load through the result list.
		if ($friends) {

			$inputName = $this->input->getVar('inputName');

			foreach ($friends as $user) {
				$obj = new stdClass();

				$obj->id = 'user-' . $user->id;
				$obj->title = $user->getName();

				// Get the item's html output
				$theme = ES::themes();
				$theme->set('user', $user);
				$theme->set('inputName', $inputName);
				$obj->html = $theme->output('site/friends/suggest/friend.item');

				// Get the item's dropdown output
				$obj->menuHtml = $user->getName();

				$obj->className = 'user';

				$result[] = $obj;
			}
		}

		return $this->ajax->resolve($result);
	}

	/**
	 * Responsible to output the JSON object of a result when searched.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function suggest($result)
	{
		// If there's nothing, just return the empty object.
		if (!$result) {
			return $this->ajax->resolve(array());
		}

		$items = array();
		$objects = array();

		// Determines if we should use a specific input name
		$inputName = $this->input->get('inputName', '', 'default');

		foreach ($result as $user) {
			$theme = ES::themes();
			$theme->set('user', $user);
			$theme->set('inputName', $inputName);

			$items[] = $theme->output('site/friends/suggest/item');
		}

		return $this->ajax->resolve($items);
	}

	/**
	 * Output friend suggestion list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function suggestList()
	{
		$limit = $this->input->get('limit', 5);
		$showMore = $this->input->get('showMore', true);

		$output = ES::themes()->html('user.suggest', $limit, true, $showMore);

		return $this->ajax->resolve($output);
	}
}
