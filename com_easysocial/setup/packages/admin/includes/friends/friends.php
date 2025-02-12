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

class SocialFriends extends EasySocial
{
	public $requester = null;
	public $target = null;
	public $table = null;

	public function __construct($targetId, $requester = null, $table = null)
	{
		parent::__construct();

		$this->target = ES::user($targetId);
		$this->requester = ES::user($requester);

		// Get the friend object
		if (!is_null($table)) {
			$this->table = $table;
		} else {
			$this->table = $this->target->getFriend($this->requester->id);
		}
	}

	public static function factory($targetId, $requester = null, $table = null)
	{
		$obj = new self($targetId, $requester, $table);

		return $obj;
	}

	/**
	 * Allows caller to make a friend request to the target
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function request()
	{
		// Check if the target really exists on the system
		if (!$this->target->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_UNABLE_TO_LOCATE_USER');
			return false;
		}

		// Determine that the user did not exceed their friend usage.
		if ($this->requester->exceededFriendLimit()) {
			return $this->setError('COM_EASYSOCIAL_FRIENDS_EXCEEDED_LIMIT');
		}

		// Load up the model to check if they are already friends.
		$model = ES::model('Friends');
		$table = $model->request($this->requester->id, $this->target->id);

		if ($table === false) {
			$this->setError($model->getError());
			return false;
		}

		$this->table = $table;

		// @badge: friends.create
		// Assign badge for the person that initiated the friend request.
		ES::badges()->log('com_easysocial', 'friends.create', $this->requester->id, JText::_('COM_EASYSOCIAL_FRIENDS_BADGE_REQUEST_TO_BE_FRIEND'));

		// We also need to let the world know that the user cancelled the friend request
		$this->trigger('onFriendRequest');

		$preventNotify = $this->trigger('onBeforeNotifyFriendRequest');

		if (!in_array(true, $preventNotify)) {
			// Send notification to the target when a user requests to be his / her friend.
			$params = [
				'requesterId' => $this->requester->id,
				'requesterAvatar' => $this->requester->getAvatar(SOCIAL_AVATAR_LARGE),
				'requesterName' => $this->requester->getName(),
				'requesterLink' => $this->requester->getPermalink(true, true),
				'requestDate' => ES::date()->toMySQL(),
				'totalFriends' => $this->requester->getTotalFriends(),
				'totalMutualFriends'=> $this->requester->getTotalMutualFriends($this->target->id)
			];

			// Email template
			$emailOptions = [
				'actor'	=> $this->requester->getName(),
				'title' => 'COM_EASYSOCIAL_EMAILS_FRIENDS_NEW_REQUEST_SUBJECT',
				'template' => 'site/friends/request',
				'params' => $params
			];

			ES::notify('friends.request', array($this->target->id), $emailOptions, false);
		}

		return true;
	}

	/**
	 * Cancels a friend request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancel()
	{
		if (!$this->table->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_INVALID_ID_PROVIDED');
			return false;
		}

		// Check if the user is allowed to cancel the request.
		if (!$this->isRequester()) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_NOT_ALLOWED_TO_CANCEL_REQUEST');
			return false;
		}

		// Now we need to try to delete the record from the db
		$model = ES::model('Friends');
		$state = $model->cancel($this->table->id);

		if ($state === false) {
			$this->setError($model->getError());
			return false;
		}

		// We also need to let the world know that the user cancelled the friend request
		$this->trigger('onFriendCancelRequest');

		return true;
	}

	/**
	 * Triggers apps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function trigger($eventName)
	{
		// We also need to let the world know that the user cancelled the friend request
		ES::apps()->load(SOCIAL_TYPE_USER);

		$dispatcher = ES::dispatcher();
		$args = [&$this->table, &$this->requester, &$this->target];

		return $dispatcher->trigger(SOCIAL_TYPE_USER, $eventName, $args);
	}

	/**
	 * Determines if the user is a requester
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isRequester()
	{
		$state 	= $this->table->actor_id == $this->requester->id ? true : false;

		return $state;
	}

	/**
	 * Approves a friend request.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve($respectTargetId = true)
	{
		if (!$this->config->get('friends.enabled')) {
			$this->setError(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED'));
			return false;
		}

		// Ensure that we have the data
		if (!$this->table->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_ERROR_INVALID_ID');
			return false;
		}

		// Test if the target is really the current user.
		if ($respectTargetId && $this->table->target_id != $this->my->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_ERROR_NOT_YOUR_REQUEST');
			return false;
		}

		// Update the table
		$this->table->modified = ES::date()->toSql();
		$this->table->state = SOCIAL_FRIENDS_STATE_FRIENDS;

		// Save the updated record
		$state = $this->table->store();

		if (!$state) {
			$this->setError($this->table->getError());
			return false;
		}

		// Notify users
		$this->notify(__FUNCTION__);

		// Get the users involved
		$requester = $this->getRequester();
		$target = $this->getTarget();

		// Points assignment
		ES::points()->assign('friends.add', 'com_easysocial', $target->id);
		ES::points()->assign('friends.add', 'com_easysocial', $requester->id);
		ES::points()->assign('friends.approve', 'com_easysocial', $target->id);


		// Automatically follow each other when a friend request is approved
		// We also need to make sure Follower setting is enabled
		if ($this->config->get('friends.autofollow') && $this->config->get('followers.enabled')) {
			$subscriptions = ES::subscriptions();
			$subscriptions->subscribe($requester->id, SOCIAL_TYPE_USER, SOCIAL_TYPE_USER, $target->id);

			$subscriptions = ES::subscriptions();
			$subscriptions->subscribe($target->id, SOCIAL_TYPE_USER, SOCIAL_TYPE_USER, $requester->id);
		}

		// Create activity stream
		$stream = ES::stream();
		$template = $stream->getTemplate();

		$template->setActor($requester->id, SOCIAL_TYPE_USER);
		$template->setTarget($target->id);
		$template->setContext($this->table->id, SOCIAL_TYPE_FRIEND);
		$template->setVerb('add');
		$template->setAggregate(true);
		$template->setAccess('core.view');

		$stream->add($template);

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);

		// Update goals for both requester and target
		$requester->updateGoals('addfriend');
		$target->updateGoals('addfriend');

		$this->trigger('onFriendApproved');

		return true;
	}

	/**
	 * Sends notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function notify($action = '')
	{
		// Send notifications when a user approves a friend request.
		if ($action == 'approve') {

			// Get the target object.
			$target = $this->getTarget();
			$requester = $this->getRequester();

			// We want to send a notification to the user who initiated the friend request.
			$recipient = array($requester->id);

			// Add notification to the requester that the user accepted his friend request.
			$systemOptions = array(
									'uid' => $this->table->id,

									// The actor is always the target because the actor is receiving this notification item.
									'actor_id'	=> $this->table->target_id,
									'type' => SOCIAL_TYPE_FRIEND,
									'permalink' => $target->getPermalink(),
									'image' => $target->getAvatar(SOCIAL_AVATAR_LARGE),
									'url' => $target->getPermalink(false, false, false)
								);

			// Send notification to the original requested when a user approves to be his / her friend.
			$params = array(
							'actor' => $target->getName(),
							'friendId' => $target->id,
							'friendAvatar' => $target->getAvatar( SOCIAL_AVATAR_LARGE ),
							'friendName' => $target->getName(),
							'friendLink' => $target->getPermalink(true, true),
							'friendDate' => ES::date()->toMySQL(),
							'totalFriends' => $target->getTotalFriends(),
							'totalMutualFriends' => $target->getTotalMutualFriends($target->id)
						);

			// Email template
			$emailOptions  = array(
									'title' => 'COM_EASYSOCIAL_EMAILS_FRIENDS_REQUEST_APPROVED_SUBJECT',
									'template' => 'site/friends/accepted',
									'params' => $params
							);

			// Add the option to the notification.
			ES::notify('friends.approve', $recipient, $emailOptions, $systemOptions);
		}
	}

	/**
	 * Rejects a friend request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function reject()
	{
		if (! $this->config->get('friends.enabled')) {
			$this->setError(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED'));
			return false;
		}

		if (!$this->table->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_ERROR_INVALID_ID');
			return false;
		}

		// Test if the target is really the current user.
		if ($this->table->target_id != $this->my->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_ERROR_NOT_YOUR_REQUEST');
			return false;
		}

		// Try to delete the friend records now
		$this->table->delete();

		// Trigger event so apps could hook to it
		ES::apps()->load(SOCIAL_TYPE_USER);
		$dispatcher = ES::dispatcher();

		$args = array(&$this->table);

		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onFriendReject', $args);

		return true;
	}

	/**
	 * Unfriends a target
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unfriend()
	{
		if (! $this->config->get('friends.enabled')) {
			$this->setError(JText::_('COM_EASYSOCIAL_FRIENDS_ERROR_FRIENDS_DISABLED'));
			return false;
		}

		if (!$this->table->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_INVALID_ID_PROVIDED');
			return false;
		}

		// Need to ensure that the target or source of the friend belongs to the current user.
		if ($this->table->actor_id != $this->requester->id && $this->table->target_id != $this->requester->id) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_ERROR_NOT_YOUR_FRIEND');
			return false;
		}

		// Throw errors when there's a problem removing the friends
		if (!$this->table->delete()) {
			$this->setError($this->table->getError());
			return false;
		}

		// Remove user's from the respective lists.
		$this->removeFromFriendLists();

		// Remove any stream associations
		$this->removeFromStream();

		// The actor is always the current logged in user because they are the one requesting the cancellation
		$actor = $this->my;

		// Deduct points from the actor
		ES::points()->assign('friends.remove', 'com_easysocial', $actor->id);

		// Point from the target user also need to be deducted #1740
		ES::points()->assign('friends.remove', 'com_easysocial', $this->table->target_id);

		// Assign badge for actor.
		ES::badges()->log('com_easysocial', 'friends.remove', $actor->id, JText::_('COM_EASYSOCIAL_FRIENDS_BADGE_REMOVED_FRIEND'));

		$this->trigger('onFriendRemoved');

		return true;
	}

	/**
	 * Remove any friend associations from the stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeFromStream()
	{
		$stream = ES::stream();

		return $stream->delete($this->table->id, SOCIAL_TYPE_FRIEND);
	}

	/**
	 * Removes the actor and target from each other's friend list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeFromFriendLists()
	{
		$model = ES::model('Friends');
		$model->removeFromFriendLists($this->table->actor_id, $this->table->target_id);

		return true;
	}

	/**
	 * Get the requester's user object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRequester()
	{
		return $this->requester;
	}

	/**
	 * Get the target user
	 *
	 * @since	2.0
	 * @access	public
	 * @return	SocialUser
	 */
	public function getTarget()
	{
		return $this->target;
	}
}
