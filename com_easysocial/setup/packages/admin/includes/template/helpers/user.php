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

class ThemesHelperUser extends ThemesHelperAbstract
{
	/**
	 * Generates a button with actions for user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function actions(SocialUser $user)
	{
		$canEdit = $user->isViewer();
		$editLink = ESR::profile(array('layout' => 'edit'));

		$arguments = array(&$user, &$canEdit, &$editLink);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onBeforeRenderUserActions', $arguments);

		$theme = ES::themes();
		$theme->set('editLink', $editLink);
		$theme->set('canEdit', $canEdit);
		$theme->set('user', $user);
		$output = $theme->output('site/helpers/user/actions');

		return $output;
	}

	/**
	 * Generates a button with cluster actions for user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function clusterActions(SocialUser $user, $cluster)
	{
		// Don't show the dropdown option if the logged-in user is not cluster admin and site admin
		if (!$cluster->isAdmin() && !$this->my->isSiteAdmin()) {
			return;
		}

		// Don't show the dropdown option if the member is cluster owner
		if ($cluster->isOwner($user->id)) {
			return;
		}

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('cluster', $cluster);
		$output = $theme->output('site/helpers/user/cluster.actions');

		return $output;
	}

	/**
	 * Generates a report link for user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function report(SocialUser $target, $wrapper = 'list')
	{
		static $output = array();

		$index = $target->id . $wrapper;

		if (!isset($output[$index])) {
			// Ensure that the user is allowed to report objects on the site
			if (!$this->access->allowed('reports.submit')) {
				return;
			}

			$reports = ES::reports();

			// Reporting options
			$options = [
				'dialogTitle' => 'COM_EASYSOCIAL_PROFILE_REPORT_USER',
				'dialogContent' => 'COM_EASYSOCIAL_PROFILE_REPORT_USER_DESC',
				'title' => $target->getName(),
				'permalink' => $target->getPermalink(true, true),
				'type' => 'dropdown',
				'actorid' => $target->id
			];

			$output[$index] = $reports->form(SOCIAL_TYPE_USER, $target->id, $options);
		}

		return $output[$index];
	}

	/**
	 * Renders the bookmark button for users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bookmark(SocialUser $user)
	{
		$options = array();
		$options['url'] = $user->getPermalink(false, true);
		$options['display'] = 'dialog';

		$sharing = ES::sharing($options);

		$output = $sharing->button();

		return $output;
	}

	/**
	 * Renders the private messaging button for users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function conversation(SocialUser $user, $buttonSize = 'sm', $hasActivateLinkableCK = false)
	{
		// Ensure that the feature is enabled
		if (!$this->config->get('conversations.enabled')) {
			return;
		}

		// Ensure that they have the access to start conversations
		if (!$this->access->allowed('conversations.create')) {
			return;
		}

		// We should not allow them to send message to themselves
		if ($this->my->id == $user->id) {
			return;
		}

		$privacy = $user->getPrivacy();

		if (!$privacy->validate('profiles.post.message', $user->id)) {
			return;
		}

		// This only work for the view but unfortunately that is not possible to get the current view if that ia ajax call
		$view = $this->input->get('view', '', 'string');

		$useConverseKit = ES::conversekit()->exists($view);

		if ($hasActivateLinkableCK) {
			$useConverseKit = true;
		}

		$theme = ES::themes();
		$theme->set('useConverseKit', $useConverseKit);
		$theme->set('user', $user);
		$theme->set('buttonSize', $buttonSize);

		$output = $theme->output('site/helpers/user/user.conversation');

		return $output;
	}

	/**
	 * Renders the subscribe button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function subscribe(SocialUser $user, $buttonSize = 'sm')
	{
		// If the current viewer is the same as the target, we should never allow them to subscribe to themselves
		if ($user->isViewer()) {
			return;
		}

		// Currently only registered user able to subscribe to people.
		if (!$this->my->id) {
			return;
		}

		// If the user blocked the current viewer, then we shouldn't let them see this button as well
		if ($user->isBlockedBy($this->my->id)) {
			return;
		}

		// If followers is not enabled, we should not render anything
		if (!$this->config->get('followers.enabled')) {
			return;
		}

		$subscriptions = ES::subscriptions();
		$isFollowing = $subscriptions->isSubscribed($user->id, SOCIAL_TYPE_USER);

		$theme = ES::themes();
		$theme->set('isFollowing', $isFollowing);
		$theme->set('user', $user);

		$output = $theme->output('site/helpers/user/user.subscribe');

		return $output;
	}

	/**
	 * Renders the friends button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function friends(SocialUser $user, $buttonSize = 'sm')
	{
		// if friends disabled, we should not show the friends buttons.
		if (! $this->config->get('friends.enabled')) {
			return;
		}

		// If the current viewer is the same as the target, we should never allow them to subscribe to themselves
		if ($user->isViewer()) {
			return;
		}

		// If the user blocked the current viewer, then we shouldn't let them see this button as well
		if ($user->isBlockedBy($this->my->id)) {
			return;
		}

		// Ensure that the current viewer is really allowed to add the target as friends
		$privacy = $this->my->getPrivacy();

		if (!$privacy->validate('friends.request', $user->id)) {
			return;
		}

		$friend = $user->getFriend($this->my->id);

		// Determines if they are already friends
		$isFriends = $friend->state == SOCIAL_FRIENDS_STATE_FRIENDS;

		// Determines if the friend request has already been sent
		$isPending = $friend->state == SOCIAL_FRIENDS_STATE_PENDING;

		// These states are only used when the connection is pending
		$isRequester = false;
		$isResponder = false;

		if ($isPending) {

			if ($friend->actor_id == $this->my->id) {
				$isRequester = true;
			} else {
				$isResponder = true;
			}
		}

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('isFriends', $isFriends);
		$theme->set('isPending', $isPending);
		$theme->set('isRequester', $isRequester);
		$theme->set('isResponder', $isResponder);
		$theme->set('size', $buttonSize);

		$output = $theme->output('site/helpers/user/user.friends');

		return $output;
	}

	public function suggest($limit, $refresh, $showMore)
	{
		// if friends disabled, we should not show the friends buttons.
		if (! $this->config->get('friends.enabled')) {
			return;
		}

		// Get friends model
		$model = ES::model('Friends');

		// Get list of friends by the current user.
		$result = $model->getSuggestedFriends($this->my->id, $limit);
		$suggestions = array();

		foreach ($result as $item) {
			$item->user = $item->friend;
			$item->mutual = '';

			if ($item->count) {
				$pluralize = ES::get('language')->pluralize($item->count, true)->getString();
				$item->mutual = JText::sprintf('COM_EASYSOCIAL_FRIEND_SUGGESTIONS_FRIENDS_MUTUAL' . $pluralize, $item->count);
			}

			$suggestions[] = $item;
		}

		$theme = ES::themes();
		$theme->set('suggestions', $suggestions);
		$theme->set('limit', $limit);
		$theme->set('refresh', $refresh);
		$theme->set('showMore', $showMore);

		$output = $theme->output('site/helpers/user/user.suggest');

		return $output;
	}

	/**
	 * Renders the delete button for conversation participant users
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function deleteParticipant(SocialUser $user, $buttonSize = 'sm', SocialConversation $conversation = null)
	{
		// We should not allow owner delete themselves
		if ($conversation->created_by == $user->id) {
			return;
		}

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('conversationId', $conversation->id);
		$theme->set('buttonSize', $buttonSize);

		$output = $theme->output('site/helpers/user/user.delete.participant');

		return $output;
	}
}
